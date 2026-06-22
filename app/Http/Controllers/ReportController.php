<?php
namespace App\Http\Controllers;

use App\Repositories\Contracts\AccessoryRepositoryInterface;
use App\Repositories\Contracts\UnitRepositoryInterface;
use App\Services\FinanceService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ReportController extends Controller
{
    public function __construct(
        private readonly FinanceService              $finance,
        private readonly UnitRepositoryInterface     $units,
        private readonly AccessoryRepositoryInterface $accessories,
    ) {}

    public function finance(Request $request)
    {
        return view('reports.finance', $this->finance->reportSummary($request->start_date, $request->end_date, $request->type_filter));
    }

    public function cashflow(Request $request)
    {
        return view('reports.cashflow', $this->finance->reportSummary($request->start_date, $request->end_date));
    }

    public function dailyFinance(Request $request)
    {
        $date = $request->date ?? today()->toDateString();
        $report = $this->finance->dailyReport($date);
        $sales  = $report['sales'];
        $dailyExpenses = $this->dailyExpenseSummary($date);
        $dailyPayments = $this->dailyPaymentSummary($date);

        // Stock purchased on this date (superadmin only)
        $unitsToday        = \App\Models\Unit::with('model.brand')
                                ->whereDate('purchase_date', $date)
                                ->orderBy('created_at')
                                ->get();
        $accToday          = \App\Models\Accessory::whereDate('created_at', $date)
                                ->orderBy('name')
                                ->get();
        $stockCashOut      = (float) $unitsToday->sum('purchase_cash')
                           + (float) $accToday->sum(fn($a) => (float)$a->purchase_cash * $a->stock_qty);
        $stockTransferOut  = (float) $unitsToday->sum('purchase_transfer')
                           + (float) $accToday->sum(fn($a) => (float)$a->purchase_transfer * $a->stock_qty);

        return view('reports.daily', [
            'date'            => $date,
            'sales'           => $sales,
            'total_revenue'   => $report['total_revenue'],
            'total_profit'    => $report['total_profit'],
            'total_cash'      => $dailyPayments['cash'],
            'total_transfer'  => $dailyPayments['transfer'],
            'total_debt'      => $dailyPayments['debt'],
            'operationalExpenses' => $dailyExpenses['expenses'],
            'operationalExpenseTotal' => $dailyExpenses['total'],
            'operationalExpenseCash' => $dailyExpenses['cash'],
            'operationalExpenseTransfer' => $dailyExpenses['transfer'],
            'unitsToday'      => $unitsToday,
            'accToday'        => $accToday,
            'stockCashOut'    => $stockCashOut,
            'stockTransferOut'=> $stockTransferOut,
        ]);
    }

    private function dailyPaymentSummary(string $date): array
    {
        $receivedByMethod = \App\Models\SalePayment::whereIn('method', ['cash', 'transfer'])
            ->where(function ($q) use ($date) {
                $q->where(function ($initial) use ($date) {
                    $initial
                        ->where('source', 'sale')
                        ->whereHas('sale', fn($sq) => $sq->where('status', 'approved')->whereDate('sale_date', $date));
                })->orWhere(function ($repayment) use ($date) {
                    $repayment
                        ->where('source', 'debt_payment')
                        ->whereDate('created_at', $date)
                        ->whereHas('sale', fn($sq) => $sq->where('status', 'approved'));
                });
            })
            ->selectRaw('method, SUM(amount) as total')
            ->groupBy('method')
            ->pluck('total', 'method');

        $initialDebt = (float) \App\Models\SalePayment::where('method', 'utang')
            ->where('source', 'sale')
            ->whereHas('sale', fn($q) => $q->where('status', 'approved')->whereDate('sale_date', $date))
            ->sum('amount');

        $repayments = (float) \App\Models\SalePayment::whereIn('method', ['cash', 'transfer'])
            ->whereDate('created_at', '<=', $date)
            ->where(function ($q) {
                $q->where('source', 'debt_payment')
                  ->orWhereRaw('DATE(sale_payments.created_at) > (SELECT DATE(sales.created_at) FROM sales WHERE sales.id = sale_payments.sale_id)');
            })
            ->whereHas('sale', fn($q) => $q->where('status', 'approved')->whereDate('sale_date', $date))
            ->sum('amount');

        $debt = max(0.0, $initialDebt - $repayments);

        return [
            'cash'     => (float) ($receivedByMethod['cash'] ?? 0),
            'transfer' => (float) ($receivedByMethod['transfer'] ?? 0),
            'debt'     => $debt,
        ];
    }

    private function dailyExpenseSummary(string $date): array
    {
        $query = \App\Models\Expense::with('creator')->whereDate('expense_date', $date);

        if (!(auth()->user()?->isSuperAdmin() ?? false)) {
            $query->whereNotIn('category', ['tarik_owner', 'gaji']);
        }

        $expenses = $query->latest('expense_date')->latest('created_at')->get();

        return [
            'expenses' => $expenses,
            'total'    => (float) $expenses->sum('amount'),
            'cash'     => (float) $expenses->where('payment_method', 'cash')->sum('amount'),
            'transfer' => (float) $expenses->where('payment_method', 'transfer')->sum('amount'),
        ];
    }

    public function stock(Request $request)
    {
        $statusFilter = $request->status;
        $filters = [];
        if ($statusFilter && $statusFilter !== 'sold') {
            $filters['status'] = $statusFilter;
        } else {
            $filters['exclude_status'] = 'sold';
        }

        $units       = $this->units->paginate($filters, 10, 'page_unit');
        $accessories = $this->accessories->paginate([], 10, 'page_accessory');
        $assetValue  = $this->units->assetValue();
        $totalStockQty = $this->accessories->totalStockQty();
        
        // Distribution stats for advanced charts
        $brandDist  = $this->units->brandDistribution();
        $typeDist   = $this->units->typeDistribution();
        
        // Exclude sold from status distribution
        $statusDist = collect($this->units->statusDistribution())
            ->reject(fn($item) => $item['status'] === 'sold' || (is_string($item['status']) && $item['status'] === 'sold') || ($item['status'] instanceof \App\Enums\UnitStatus && $item['status']->value === 'sold'))
            ->values()
            ->toArray();
        
        return view('reports.stock', compact('units', 'accessories', 'assetValue', 'totalStockQty', 'brandDist', 'typeDist', 'statusDist'));
    }

    public function stockOpname()
    {
        $units       = \App\Models\Unit::with('model.brand')->where('status', '!=', 'sold')->orderBy('status')->orderBy('created_at')->get();
        $accessories = \App\Models\Accessory::orderBy('category')->orderBy('name')->get();

        $readyUnits = $units->filter(fn($u) => $u->status->value === 'ready');
        $assetModal = (float) $readyUnits->sum('purchase_price');
        $accModal   = (float) $accessories->sum(fn($a) => (float)$a->purchase_price * $a->stock_qty);
        $accQty     = (int) $accessories->sum('stock_qty');

        return view('reports.opname-stock', [
            'units'       => $units,
            'accessories' => $accessories,
            'readyCount'  => $readyUnits->count(),
            'soldCount'   => 0,
            'assetModal'  => $assetModal,
            'accModal'    => $accModal,
            'accQty'      => $accQty,
            'printedAt'   => now()->isoFormat('D MMMM YYYY, HH:mm') . ' WIB',
        ]);
    }

    public function pdf(Request $request, string $type)
    {
        $pdf = match ($type) {
            'stock'              => $this->buildStockPdf(),
            'stock-hp'          => $this->buildStockHpPdf(),
            'stock-accessories' => $this->buildStockAccessoriesPdf(),
            'finance'           => $this->buildFinancePdf($request->start_date, $request->end_date),
            'sales'             => $this->buildSalesDailyPdf($request->date ?? today()->toDateString()),
            default             => abort(404),
        };

        $filename = "laporan-{$type}-" . now()->format('Ymd-His') . ".pdf";
        return $pdf->download($filename);
    }

    private function buildStockPdf(): \Barryvdh\DomPDF\PDF
    {
        $units       = \App\Models\Unit::with('model.brand')->where('status', '!=', 'sold')->orderBy('status')->get();
        $accessories = \App\Models\Accessory::orderBy('category')->orderBy('name')->get();

        $readyUnits  = $units->filter(fn($u) => $u->status->value === 'ready');
        $assetModal  = (float) $readyUnits->sum('purchase_price');
        $assetJual   = 0.0;
        $accModal    = (float) $accessories->sum(fn($a) => (float)$a->purchase_price * $a->stock_qty);

        $data = [
            'units'        => $units,
            'accessories'  => $accessories,
            'readyCount'   => $readyUnits->count(),
            'soldCount'    => 0,
            'assetModal'   => $assetModal,
            'assetJual'    => $assetJual,
            'accModal'     => $accModal,
            'accQty'       => (int) $accessories->sum('stock_qty'),
            'printedAt'    => now()->isoFormat('D MMMM YYYY, HH:mm') . ' WIB',
        ];

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf-stock', $data)
            ->setPaper('a4', 'portrait');
    }

    private function buildStockHpPdf(): \Barryvdh\DomPDF\PDF
    {
        $units      = \App\Models\Unit::with('model.brand')->where('status', '!=', 'sold')->orderBy('status')->get();
        $readyUnits = $units->filter(fn($u) => $u->status->value === 'ready');

        $data = [
            'units'       => $units,
            'accessories' => collect(),
            'readyCount'  => $readyUnits->count(),
            'soldCount'   => 0,
            'assetModal'  => (float) $readyUnits->sum('purchase_price'),
            'assetJual'   => 0.0,
            'accModal'    => 0.0,
            'accQty'      => 0,
            'printedAt'   => now()->isoFormat('D MMMM YYYY, HH:mm') . ' WIB',
        ];

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf-stock', $data)
            ->setPaper('a4', 'portrait');
    }

    private function buildStockAccessoriesPdf(): \Barryvdh\DomPDF\PDF
    {
        $accessories = \App\Models\Accessory::orderBy('category')->orderBy('name')->get();
        $accQty      = (int) $accessories->sum('stock_qty');
        $accModal    = (float) $accessories->sum(fn($a) => (float)$a->purchase_price * $a->stock_qty);

        $data = [
            'units'       => collect(),
            'accessories' => $accessories,
            'readyCount'  => 0,
            'soldCount'   => 0,
            'assetModal'  => 0.0,
            'assetJual'   => 0.0,
            'accModal'    => $accModal,
            'accQty'      => $accQty,
            'printedAt'   => now()->isoFormat('D MMMM YYYY, HH:mm') . ' WIB',
        ];

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf-stock', $data)
            ->setPaper('a4', 'portrait');
    }

    private function buildFinancePdf(?string $startDate, ?string $endDate): \Barryvdh\DomPDF\PDF
    {
        $summary = $this->finance->financeSummaryForExport($startDate, $endDate);

        $salesQuery = \App\Models\Sale::with(['items.unit.model.brand', 'items.accessory', 'creator'])
            ->where('status', 'approved');
        if ($startDate) $salesQuery->whereDate('sale_date', '>=', $startDate);
        if ($endDate)   $salesQuery->whereDate('sale_date', '<=', $endDate);
        $sales = $salesQuery->latest('sale_date')->get();

        $periodStr = ($startDate || $endDate)
            ? (($startDate ? \Carbon\Carbon::parse($startDate)->isoFormat('D MMM Y') : 'Awal')
               . ' – '
               . ($endDate ? \Carbon\Carbon::parse($endDate)->isoFormat('D MMM Y') : 'Sekarang'))
            : 'Semua Periode';

        $debtPaymentsQuery = \App\Models\SalePayment::with(['sale.creator'])
            ->whereIn('method', ['cash', 'transfer'])
            ->whereNotNull('created_at')
            ->whereHas('sale', fn($q) => $q->where('status', 'approved'))
            ->where(function ($q) {
                $q->where('source', 'debt_payment')
                  ->orWhereRaw('DATE(sale_payments.created_at) > (SELECT DATE(sales.created_at) FROM sales WHERE sales.id = sale_payments.sale_id)');
            });
        if ($startDate) $debtPaymentsQuery->whereDate('created_at', '>=', $startDate);
        if ($endDate)   $debtPaymentsQuery->whereDate('created_at', '<=', $endDate);
        $debtPayments = $debtPaymentsQuery->get();

        $hpPurchasesQuery = \App\Models\Unit::with(['model.brand', 'creator']);
        if ($startDate) $hpPurchasesQuery->whereDate('purchase_date', '>=', $startDate);
        if ($endDate)   $hpPurchasesQuery->whereDate('purchase_date', '<=', $endDate);
        $hpPurchases = $hpPurchasesQuery->latest('purchase_date')->get();

        // Operational expenses (excluding HP stock purchases)
        $isSuperAdmin = auth()->user()?->isSuperAdmin() ?? false;
        $operationalExpensesQuery = \App\Models\Expense::with('creator');
        if (!$isSuperAdmin) {
            $operationalExpensesQuery->whereNotIn('category', ['tarik_owner', 'gaji']);
        }
        if ($startDate) $operationalExpensesQuery->whereDate('expense_date', '>=', $startDate);
        if ($endDate)   $operationalExpensesQuery->whereDate('expense_date', '<=', $endDate);
        $operationalExpenses = $operationalExpensesQuery->latest('expense_date')->get();

        $data = compact('summary', 'sales', 'debtPayments', 'hpPurchases', 'operationalExpenses', 'periodStr', 'startDate', 'endDate');
        $data['printedAt'] = now()->isoFormat('D MMMM YYYY, HH:mm') . ' WIB';

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf-finance', $data)
            ->setPaper('a4', 'landscape');
    }

    private function buildSalesDailyPdf(string $date): \Barryvdh\DomPDF\PDF
    {
        $report      = $this->finance->dailyReport($date);
        $sales       = $report['sales'];
        $totalRev    = (float) $report['total_revenue'];
        $totalProfit = (float) $report['total_profit'];
        $dailyExpenses = $this->dailyExpenseSummary($date);
        $dailyPayments = $this->dailyPaymentSummary($date);

        $data = [
            'sales'         => $sales,
            'date'          => \Carbon\Carbon::parse($date)->isoFormat('D MMMM Y'),
            'totalRev'      => $totalRev,
            'totalProfit'   => $totalProfit,
            'totalCash'     => $dailyPayments['cash'],
            'totalTransfer' => $dailyPayments['transfer'],
            'totalDebt'     => $dailyPayments['debt'],
            'operationalExpenses' => $dailyExpenses['expenses'],
            'operationalExpenseTotal' => $dailyExpenses['total'],
            'operationalExpenseCash' => $dailyExpenses['cash'],
            'operationalExpenseTransfer' => $dailyExpenses['transfer'],
            'txCount'       => count($sales),
            'printedAt'     => now()->isoFormat('D MMMM YYYY, HH:mm') . ' WIB',
        ];

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf-sales', $data)
            ->setPaper('a4', 'landscape');
    }

    public function export(Request $request, string $type)
    {
        $spreadsheet = new Spreadsheet();

        if ($type === 'stock') {
            $sheet1 = $spreadsheet->getActiveSheet();
            $this->buildStockSummarySheet($sheet1);
            $sheet2 = $spreadsheet->createSheet();
            $this->buildStockDetailSheet($sheet2);
            $sheet3 = $spreadsheet->createSheet();
            $this->buildAccessoriesStockSheet($sheet3);
            $spreadsheet->setActiveSheetIndex(0);
        } elseif ($type === 'stock-hp') {
            $sheet1 = $spreadsheet->getActiveSheet();
            $this->buildStockSummarySheet($sheet1);
            $sheet2 = $spreadsheet->createSheet();
            $this->buildStockDetailSheet($sheet2);
            $spreadsheet->setActiveSheetIndex(0);
        } elseif ($type === 'stock-accessories') {
            $sheet1 = $spreadsheet->getActiveSheet();
            $this->buildAccessoriesStockSheet($sheet1);
            $spreadsheet->setActiveSheetIndex(0);
        } elseif ($type === 'finance') {
            $sheet1 = $spreadsheet->getActiveSheet();
            $this->buildFinanceSheet($sheet1, $request->start_date, $request->end_date);
            $sheet2 = $spreadsheet->createSheet();
            $this->buildSalesTransactionsSheet($sheet2, $request->start_date, $request->end_date);
        } else {
            $sheet = $spreadsheet->getActiveSheet();
            match ($type) {
                'sales'   => $this->buildSalesSheet($sheet, $request->date ?? today()->toDateString()),
                'expenses'=> $this->buildExpensesSheet($sheet),
                default   => abort(404),
            };
        }

        $writer   = new Xlsx($spreadsheet);
        $writer->setIncludeCharts(true); // Enable Excel-native chart rendering!
        $filename = "export-{$type}-" . now()->format('Ymd-His') . ".xlsx";

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    private function buildStockSummarySheet($sheet): void
    {
        $sheet->setTitle('Ringkasan');

        $units       = \App\Models\Unit::with('model.brand')->where('status', '!=', 'sold')->get();
        $accessories = \App\Models\Accessory::all();

        $readyUnits  = $units->filter(fn($u) => $u->status->value === 'ready');
        $returUnits  = $units->filter(fn($u) => $u->status->value === 'returned');
        $baruUnits   = $units->filter(fn($u) => $u->unit_type->value === 'baru');
        $secondUnits = $units->filter(fn($u) => $u->unit_type->value === 'second');

        $totalReady  = $readyUnits->count();
        $totalSold   = 0;
        $totalRetur  = $returUnits->count();
        $totalBaru   = $baruUnits->count();
        $totalSecond = $secondUnits->count();
        $totalUnits  = $units->count();

        $brandGroups = $readyUnits->groupBy(fn($u) => $u->model->brand->name ?? 'Lain-lain');
        $numBrands   = $brandGroups->count();
        $topBrand    = $brandGroups->map(fn($g) => $g->count())->sortDesc()->keys()->first() ?? '-';
        $brandDist   = $brandGroups->map(fn($g) => $g->count())->sortDesc();

        $assetModal  = (float) $readyUnits->sum(fn($u) => (float) $u->purchase_price);
        $assetJual   = 0.0;
        $estLaba     = 0.0;
        $avgModal    = $totalReady > 0 ? $assetModal / $totalReady : 0;

        $accCount = $accessories->count();
        $accQty   = (int) $accessories->sum('stock_qty');
        $accModal = (float) $accessories->sum(fn($a) => (float) $a->purchase_price * $a->stock_qty);
        $accJual  = (float) $accessories->sum(fn($a) => (float) $a->selling_price * $a->stock_qty);
        $accLaba  = $accJual - $accModal;
        $numCats  = $accessories->groupBy(fn($a) => $a->category ?: 'Lain-lain')->count();
        $lowStock = $accessories->filter(fn($a) => $a->stock_qty <= 5)->count();

        $fmt  = fn($n) => 'Rp ' . number_format((float)$n, 0, ',', '.');
        $fmtN = fn($n) => number_format((int)$n, 0, ',', '.');

        // ─── COLORS ─────────────────────────────────────────────
        $cHdrBg   = '014737'; $cSecHpBg  = '065F46'; $cSecAccBg = '1E3A8A';
        $cColBg   = '1E293B'; $cGreenLt  = 'ECFDF5'; $cBlueLt   = 'EFF6FF';
        $cSlate   = 'CBD5E1'; $cGreenBdr = 'A7F3D0'; $cBlueBdr  = 'BFDBFE';
        $cBrandBg = '0369A1';

        // ─── TITLE HEADER (A1:N4) ────────────────────────────────
        $sheet->mergeCells('A1:N4');
        $sheet->setCellValue('A1', "LAPORAN STOCK OPNAME\nALEX PHONE BANJARNEGARA");
        $sheet->getStyle('A1')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['name' => 'Segoe UI', 'bold' => true, 'size' => 20, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $cHdrBg]],
        ]);
        foreach ([1, 2, 3] as $r) $sheet->getRowDimension($r)->setRowHeight(18);
        $sheet->getRowDimension(2)->setRowHeight(28);
        $sheet->getRowDimension(4)->setRowHeight(18);

        // ─── DATE BAR (A5:N5) ────────────────────────────────────
        $sheet->mergeCells('A5:N5');
        $sheet->setCellValue('A5', 'Tanggal Cetak: ' . now()->format('d F Y') . '  |  Pukul: ' . now()->format('H:i') . ' WIB  |  Dokumen: Laporan Stock Opname');
        $sheet->getStyle('A5')->applyFromArray([
            'font'      => ['name' => 'Segoe UI', 'size' => 9, 'color' => ['rgb' => $cSecHpBg]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $cGreenLt]],
            'borders'   => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '6EE7B7']]],
        ]);
        $sheet->getRowDimension(5)->setRowHeight(20);

        // ─── SECTION HEADERS ROW 7 ───────────────────────────────
        $sheet->mergeCells('A7:F7');
        $sheet->setCellValue('A7', '  RINGKASAN STOK HANDPHONE (HP)');
        $sheet->getStyle('A7')->applyFromArray([
            'font'      => ['name' => 'Segoe UI', 'bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $cSecHpBg]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->mergeCells('H7:N7');
        $sheet->setCellValue('H7', '  RINGKASAN STOK AKSESORIS');
        $sheet->getStyle('H7')->applyFromArray([
            'font'      => ['name' => 'Segoe UI', 'bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $cSecAccBg]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(7)->setRowHeight(26);

        // ─── KPI COLUMN HEADERS ROW 8 ────────────────────────────
        foreach (['A8:C8' => 'Keterangan', 'D8:F8' => 'Nilai'] as $range => $label) {
            [$s] = explode(':', $range);
            $sheet->mergeCells($range);
            $sheet->setCellValue($s, $label);
        }
        $sheet->getStyle('A8:F8')->applyFromArray([
            'font'      => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $cColBg]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '475569']]],
        ]);
        foreach (['H8:J8' => 'Keterangan', 'K8:N8' => 'Nilai'] as $range => $label) {
            [$s] = explode(':', $range);
            $sheet->mergeCells($range);
            $sheet->setCellValue($s, $label);
        }
        $sheet->getStyle('H8:N8')->applyFromArray([
            'font'      => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $cColBg]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '475569']]],
        ]);
        $sheet->getRowDimension(8)->setRowHeight(22);

        // ─── HP KPI DATA (rows 9+) ────────────────────────────────
        $hpKpis = [
            ['Total Unit HP Terdaftar',         $fmtN($totalUnits) . ' unit',   null,     null],
            ['Unit HP Siap Jual (Ready)',        $fmtN($totalReady) . ' unit',   '14532D', 'D1FAE5'],
            ['Unit HP Retur / Lainnya',          $fmtN($totalRetur) . ' unit',   'B91C1C', 'FEF2F2'],
            ['Unit Kondisi Baru',                $fmtN($totalBaru)  . ' unit',   '1D4ED8', 'EFF6FF'],
            ['Unit Kondisi Second / Bekas',      $fmtN($totalSecond). ' unit',   'B45309', 'FFFBEB'],
            ['Jumlah Brand (Ready)',             $fmtN($numBrands)  . ' brand',  null,     null],
            ['Brand Terbanyak (Ready)',          $topBrand,                       null,     null],
            ['Nilai Modal Stok HP Ready',        $fmt($assetModal),               '064E3B', 'ECFDF5'],
            ['Est. Nilai Jual Stok Ready',       $fmt($assetJual),                '1E3A8A', 'EFF6FF'],
            ['Est. Laba Potensial Stok Ready',   $fmt($estLaba),                  '065F46', 'D1FAE5'],
            ['Rata-rata Harga Modal / Unit',     $fmt($avgModal),                 null,     null],
        ];

        $row = 9;
        foreach ($hpKpis as [$label, $value, $fg, $bg]) {
            $sheet->mergeCells("A{$row}:C{$row}");
            $sheet->mergeCells("D{$row}:F{$row}");
            $sheet->setCellValue("A{$row}", $label);
            $sheet->setCellValue("D{$row}", $value);
            $bgColor = $bg ?: ($row % 2 === 0 ? 'F8FAFC' : 'FFFFFF');
            $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                'font'    => ['name' => 'Segoe UI', 'size' => 9, 'color' => ['rgb' => '1E293B']],
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $cSlate]]],
            ]);
            $sheet->getStyle("A{$row}")->getAlignment()->setIndent(1);
            $sheet->getStyle("D{$row}")->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => $fg ?: '1E293B']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $sheet->getRowDimension($row)->setRowHeight(20);
            $row++;
        }

        // ─── ACCESSORIES KPI DATA (rows 9+, columns H:N) ─────────
        // No padding rows — accessories section is shorter than HP, that's fine
        $accKpis = [
            ['Total Jenis Aksesoris',         $fmtN($accCount) . ' jenis',   null,     null],
            ['Total Qty Stok Keseluruhan',    $fmtN($accQty)  . ' pcs',      null,     null],
            ['Jumlah Kategori',               $fmtN($numCats) . ' kategori',  null,     null],
            ['Stok Menipis (≤ 5 pcs)',        $fmtN($lowStock) . ' jenis',   'B91C1C', 'FEF2F2'],
            ['Total Nilai Modal Aksesoris',   $fmt($accModal),                '064E3B', 'ECFDF5'],
            ['Total Est. Nilai Jual',         $fmt($accJual),                 '1E3A8A', 'EFF6FF'],
            ['Est. Laba Potensial Aksesoris', $fmt($accLaba),                 '065F46', 'D1FAE5'],
        ];

        $accRow = 9;
        foreach ($accKpis as [$label, $value, $fg, $bg]) {
            $sheet->mergeCells("H{$accRow}:J{$accRow}");
            $sheet->mergeCells("K{$accRow}:N{$accRow}");
            $sheet->setCellValue("H{$accRow}", $label);
            $sheet->setCellValue("K{$accRow}", $value);
            $bgColor = $bg ?: ($accRow % 2 === 0 ? 'F8FAFC' : 'FFFFFF');
            $sheet->getStyle("H{$accRow}:N{$accRow}")->applyFromArray([
                'font'    => ['name' => 'Segoe UI', 'size' => 9, 'color' => ['rgb' => '1E293B']],
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $cSlate]]],
            ]);
            $sheet->getStyle("H{$accRow}")->getAlignment()->setIndent(1);
            $sheet->getStyle("K{$accRow}")->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => $fg ?: '1E293B']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $sheet->getRowDimension($accRow)->setRowHeight(20);
            $accRow++;
        }

        // ─── BRAND DISTRIBUTION TABLE ─────────────────────────────
        $row += 2;
        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->setCellValue("A{$row}", 'DISTRIBUSI BRAND HP (STOK READY)');
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font'      => ['name' => 'Segoe UI', 'bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $cBrandBg]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(24);
        $row++;

        $sheet->fromArray(['No', 'Brand', 'Jumlah Unit', '', '% Stok', 'Ket.'], null, "A{$row}");
        $sheet->mergeCells("C{$row}:D{$row}");
        $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
            'font'      => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $cColBg]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '475569']]],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(22);
        $row++;

        $bNo = 1;
        foreach ($brandDist as $brand => $count) {
            $pct = $totalReady > 0 ? round(($count / $totalReady) * 100, 1) : 0;
            $ket = $count >= 5 ? 'Cukup' : ($count >= 2 ? 'Sedikit' : 'Kritis');
            $ketColor = $count >= 5 ? '14532D' : ($count >= 2 ? 'B45309' : 'B91C1C');
            $sheet->setCellValue("A{$row}", $bNo);
            $sheet->setCellValue("B{$row}", $brand);
            $sheet->mergeCells("C{$row}:D{$row}");
            $sheet->setCellValue("C{$row}", $count . ' unit');
            $sheet->setCellValue("E{$row}", $pct . '%');
            $sheet->setCellValue("F{$row}", $ket);
            $bg = ($row % 2 === 0) ? 'EFF6FF' : 'FFFFFF';
            $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                'font'    => ['name' => 'Segoe UI', 'size' => 9],
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $cBlueBdr]]],
            ]);
            $sheet->getStyle("A{$row}:F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$row}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'indent' => 1],
            ]);
            $sheet->getStyle("F{$row}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => $ketColor]],
            ]);
            $row++;
            $bNo++;
        }

        if ($brandDist->isEmpty()) {
            $sheet->mergeCells("A{$row}:F{$row}");
            $sheet->setCellValue("A{$row}", 'Tidak ada unit HP ready saat ini');
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
        }

        // Brand total row
        $sheet->mergeCells("C{$row}:D{$row}");
        $sheet->setCellValue("B{$row}", 'TOTAL READY');
        $sheet->setCellValue("C{$row}", $totalReady . ' unit');
        $sheet->setCellValue("E{$row}", '100%');
        $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
            'font'      => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $cBrandBg]],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $cBrandBg]]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $row++;

        // ─── SIGNATURE / AUTHORIZATION SECTION ───────────────────
        $row += 2;
        $sheet->mergeCells("A{$row}:N{$row}");
        $sheet->setCellValue("A{$row}", 'LEMBAR PERSETUJUAN & TANDA TANGAN');
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font'      => ['name' => 'Segoe UI', 'bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $cHdrBg]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(24);
        $row++;

        $sigBlocks = [
            ['range' => "A{$row}:D{$row}", 'label' => 'Dibuat Oleh'],
            ['range' => "F{$row}:J{$row}", 'label' => 'Diperiksa Oleh'],
            ['range' => "L{$row}:N{$row}", 'label' => 'Disetujui Oleh'],
        ];
        foreach ($sigBlocks as $sig) {
            [$s] = explode(':', $sig['range']);
            $sheet->mergeCells($sig['range']);
            $sheet->setCellValue($s, $sig['label']);
            $sheet->getStyle($sig['range'])->applyFromArray([
                'font'      => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $cSlate]]],
            ]);
        }
        $sheet->getRowDimension($row)->setRowHeight(20);

        for ($i = 0; $i < 4; $i++) {
            $row++;
            $sigBlocks2 = [["A{$row}:D{$row}"], ["F{$row}:J{$row}"], ["L{$row}:N{$row}"]];
            foreach ($sigBlocks2 as [$range]) {
                [$s] = explode(':', $range);
                $sheet->mergeCells($range);
                $sheet->getStyle($range)->applyFromArray([
                    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
                    'borders' => ['leftBorder' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $cSlate]],
                                  'rightBorder' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $cSlate]]],
                ]);
            }
            $sheet->getRowDimension($row)->setRowHeight(20);
        }

        $row++;
        $nameBlocks = [["A{$row}:D{$row}"], ["F{$row}:J{$row}"], ["L{$row}:N{$row}"]];
        foreach ($nameBlocks as [$range]) {
            [$s] = explode(':', $range);
            $sheet->mergeCells($range);
            $sheet->setCellValue($s, '(________________________)');
            $sheet->getStyle($range)->applyFromArray([
                'font'      => ['name' => 'Segoe UI', 'size' => 9],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $cSlate]]],
            ]);
        }
        $sheet->getRowDimension($row)->setRowHeight(22);

        // ─── COLUMN WIDTHS ────────────────────────────────────────
        foreach (range('A', 'N') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
        $sheet->getColumnDimension('G')->setWidth(2.5);
        $sheet->getColumnDimension('E')->setWidth(3);
    }

    private function buildStockDetailSheet($sheet): void
    {
        $sheet->setTitle('Detail HP');

        $units = \App\Models\Unit::with(['model.brand', 'creator'])->where('status', '!=', 'sold')->orderBy('status')->orderBy('model_id')->get();

        // Compute grand totals
        $grandTotalModal = 0.0;
        $grandTotalJual  = 0.0;
        $grandTotalLaba  = 0.0;
        $grandCount      = $units->count();

        // ─── COLORS (Teal theme — different from old Blue) ────────
        $cHdr     = '134E4A'; // dark teal
        $cBrandBg = '0F766E'; // teal section
        $cColBg   = '1E293B'; // dark slate column header
        $cSubtot  = '115E59'; // teal subtotal
        $cGrandT  = '0F766E'; // teal grand total
        $cBorder  = 'E2E8F0'; // light gray border (matches finance)

        // ─── TITLE HEADER ─────────────────────────────────────────
        $sheet->mergeCells('A1:N3');
        $sheet->setCellValue('A1', "DATA INVENTARIS UNIT HANDPHONE (HP)\nALEX PHONE BANJARNEGARA");
        $sheet->getStyle('A1')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['name' => 'Segoe UI', 'bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $cHdr]],
        ]);
        foreach ([1, 2, 3] as $r) $sheet->getRowDimension($r)->setRowHeight(20);
        $sheet->getRowDimension(2)->setRowHeight(26);

        // ─── SUBTITLE BAR ─────────────────────────────────────────
        $sheet->mergeCells('A4:N4');
        $dateStr = now()->format('d F Y H:i');
        $sheet->setCellValue('A4', "Dicetak: {$dateStr}  |  Total Unit: {$grandCount}  |  Status unit ditampilkan di kolom Status (diberi warna)");
        $sheet->getStyle('A4')->applyFromArray([
            'font'      => ['name' => 'Segoe UI', 'size' => 9, 'color' => ['rgb' => $cHdr]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'CCFBF1']],
            'borders'   => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '5EEAD4']]],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(20);

        // ─── STATUS LEGEND (A5:Q5) ────────────────────────────────
        $sheet->mergeCells('A5:E5');
        $sheet->setCellValue('A5', '  ■  READY — Siap Jual');
        $sheet->getStyle('A5')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9, 'color' => ['rgb' => '14532D']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->mergeCells('F5:J5');
        $sheet->setCellValue('F5', '  ■  TERJUAL — Sudah Laku');
        $sheet->getStyle('F5')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9, 'color' => ['rgb' => '334155']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->mergeCells('K5:N5');
        $sheet->setCellValue('K5', '  ■  RETUR / LAINNYA');
        $sheet->getStyle('K5')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9, 'color' => ['rgb' => 'B91C1C']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEE2E2']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(5)->setRowHeight(20);

        // ─── GROUP BY BRAND ───────────────────────────────────────
        $grouped = $units->groupBy(fn($u) => $u->model->brand->name ?? 'Lain-lain');
        $row = 7;

        foreach ($grouped as $brandName => $brandUnits) {
            $brandModal = 0.0;
            $brandJual  = 0.0;
            $brandReady = 0;

            // BRAND SECTION HEADER
            $sheet->mergeCells("A{$row}:N{$row}");
            $sheet->setCellValue("A{$row}", "  ◆  BRAND: " . strtoupper($brandName) . "  (" . $brandUnits->count() . " unit)");
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font'      => ['name' => 'Segoe UI', 'bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $cBrandBg]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getRowDimension($row)->setRowHeight(26);
            $row++;

            // COLUMN HEADERS
            $headers = ['No', 'Model', 'RAM', 'ROM', 'Warna', 'Tipe', 'Grade', 'IMEI', 'No. Seri', 'Harga Modal', 'Status', 'Tgl. Beli', 'Hari di Stok', 'Entri Oleh'];
            $sheet->fromArray($headers, null, "A{$row}");
            $sheet->getStyle("A{$row}:N{$row}")->applyFromArray([
                'font'      => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $cColBg]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '475569']]],
            ]);
            $sheet->getRowDimension($row)->setRowHeight(30);
            $row++;

            // DATA ROWS
            $no = 1;
            foreach ($brandUnits as $u) {
                $modal  = (float) $u->purchase_price;
                $jual   = 0.0;
                $laba   = 0.0;
                $margin = 0.0;
                $days   = $u->purchase_date ? (int) now()->diffInDays($u->purchase_date) : 0;
                $status = $u->status->value;

                $statusLabel = match ($status) {
                    'ready' => 'READY',
                    'sold'  => 'TERJUAL',
                    default => strtoupper($status),
                };
                $statusBadgeBg = match ($status) {
                    'ready' => 'D1FAE5',
                    'sold'  => 'E2E8F0',
                    default => 'FEE2E2',
                };
                $statusFg = match ($status) {
                    'ready' => '14532D',
                    'sold'  => '334155',
                    default => 'B91C1C',
                };

                $rowBg = ($no % 2 === 0) ? 'F8FAFC' : 'FFFFFF';

                $sheet->setCellValue("A{$row}", $no);
                $sheet->setCellValue("B{$row}", $u->model->name ?? '—');
                $sheet->setCellValue("C{$row}", $u->ram);
                $sheet->setCellValue("D{$row}", $u->rom);
                $sheet->setCellValue("E{$row}", $u->color);
                $sheet->setCellValue("F{$row}", ucfirst($u->unit_type->value));
                $sheet->setCellValue("G{$row}", $u->grade ?: ($u->unit_type->value === 'baru' ? 'A' : '-'));
                $sheet->getCell("H{$row}")->setValueExplicit(' ' . ($u->imei ?: '—'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->getCell("I{$row}")->setValueExplicit(' ' . ($u->serial_number ?: '—'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue("J{$row}", $modal);
                $sheet->setCellValue("K{$row}", $statusLabel);
                $sheet->setCellValue("L{$row}", $u->purchase_date ? $u->purchase_date->format('d/m/Y') : '—');
                $sheet->setCellValue("M{$row}", $days . ' hari');
                $sheet->setCellValue("N{$row}", $u->creator->name ?? '—');

                // Standard zebra row (matches finance readability)
                $sheet->getStyle("A{$row}:N{$row}")->applyFromArray([
                    'font'    => ['name' => 'Segoe UI', 'size' => 9, 'color' => ['rgb' => '1E293B']],
                    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $rowBg]],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $cBorder]]],
                ]);
                // Status badge — only the status cell gets color, keeps rows clean
                $sheet->getStyle("K{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => $statusFg]],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $statusBadgeBg]],
                ]);
                $sheet->getStyle("J{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C{$row}:D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("F{$row}:G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("K{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("L{$row}:M{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension($row)->setRowHeight(20);

                $brandModal += $modal;
                $brandJual  += $jual;
                if ($status === 'ready') $brandReady++;
                $row++;
                $no++;
            }

            // BRAND SUBTOTAL ROW
            $brandLaba = $brandJual - $brandModal;
            $sheet->setCellValue("B{$row}", "Subtotal: " . strtoupper($brandName));
            $sheet->setCellValue("C{$row}", $brandUnits->count() . ' unit');
            $sheet->setCellValue("D{$row}", "Ready: {$brandReady}");
            $sheet->setCellValue("J{$row}", $brandModal);
            $sheet->getStyle("A{$row}:N{$row}")->applyFromArray([
                'font'      => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $cSubtot]],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '0D9488']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getStyle("J{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getRowDimension($row)->setRowHeight(22);
            $row++;
            $row++; // gap

            $grandTotalModal += $brandModal;
            $grandTotalJual  += $brandJual;
            $grandTotalLaba  += ($brandJual - $brandModal);
        }

        // ─── GRAND TOTAL ROW ──────────────────────────────────────
        $row++;
        $sheet->mergeCells("A{$row}:I{$row}");
        $sheet->setCellValue("A{$row}", 'GRAND TOTAL KESELURUHAN');
        $sheet->setCellValue("J{$row}", $grandTotalModal);
        $sheet->setCellValue("K{$row}", $grandCount . ' unit');
        $sheet->getStyle("A{$row}:N{$row}")->applyFromArray([
            'font'      => ['name' => 'Segoe UI', 'bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $cGrandT]],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '0D9488']]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("A{$row}")->getAlignment()->setIndent(1);
        $sheet->getStyle("J{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
        $sheet->getRowDimension($row)->setRowHeight(24);

        // ─── COLUMN WIDTHS ────────────────────────────────────────
        foreach (range('A', 'N') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('H')->setWidth(18);
        $sheet->getColumnDimension('I')->setWidth(16);
    }

    private function buildAccessoriesStockSheet($sheet): void
    {
        $sheet->setTitle('Aksesoris');

        $accessories = \App\Models\Accessory::orderBy('category')->orderBy('name')->get();

        $grandQty   = (int) $accessories->sum('stock_qty');
        $grandModal = (float) $accessories->sum(fn($a) => (float)$a->purchase_price * $a->stock_qty);
        $grandJual  = (float) $accessories->sum(fn($a) => (float)$a->selling_price * $a->stock_qty);
        $grandLaba  = $grandJual - $grandModal;
        $lowCount   = $accessories->filter(fn($a) => $a->stock_qty <= 5)->count();

        // ─── COLORS (Violet/Purple theme) ────────────────────────
        $cHdr    = '2E1065'; // very dark violet
        $cCatBg  = '4C1D95'; // dark violet category
        $cColBg  = '1E293B'; // slate column header
        $cSubtot = '4338CA'; // indigo subtotal
        $cGrand  = '4C1D95'; // dark violet grand total
        $cBorder = 'E2E8F0'; // light gray border (matches finance)

        // ─── TITLE HEADER ─────────────────────────────────────────
        $sheet->mergeCells('A1:G3');
        $sheet->setCellValue('A1', "LAPORAN STOCK OPNAME — AKSESORIS\nALEX PHONE BANJARNEGARA");
        $sheet->getStyle('A1')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['name' => 'Segoe UI', 'bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $cHdr]],
        ]);
        foreach ([1, 2, 3] as $r) $sheet->getRowDimension($r)->setRowHeight(20);
        $sheet->getRowDimension(2)->setRowHeight(26);

        // ─── SUBTITLE BAR ─────────────────────────────────────────
        $sheet->mergeCells('A4:G4');
        $dateStr    = now()->format('d F Y H:i');
        $grandQtyFmt = number_format($grandQty, 0, ',', '.');
        $grandMFmt  = 'Rp ' . number_format($grandModal, 0, ',', '.');
        $sheet->setCellValue('A4', "Dicetak: {$dateStr}  |  Total Qty: {$grandQtyFmt} pcs  |  Total Nilai Modal: {$grandMFmt}  |  Stok Menipis: {$lowCount} jenis");
        $sheet->getStyle('A4')->applyFromArray([
            'font'      => ['name' => 'Segoe UI', 'size' => 9, 'color' => ['rgb' => $cHdr]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EDE9FE']],
            'borders'   => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => 'A78BFA']]],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(20);

        // ─── LEGEND ROW ───────────────────────────────────────────
        $sheet->mergeCells('A5:C5');
        $sheet->setCellValue('A5', '  ■  AMAN — Stok cukup (> 5 pcs)');
        $sheet->getStyle('A5')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9, 'color' => ['rgb' => '14532D']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
        ]);
        $sheet->mergeCells('D5:G5');
        $sheet->setCellValue('D5', '  ■  MENIPIS (≤ 5 pcs) — Status kolom E ditandai merah, perlu restock');
        $sheet->getStyle('D5')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9, 'color' => ['rgb' => 'B91C1C']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEE2E2']],
        ]);
        $sheet->getRowDimension(5)->setRowHeight(20);

        // ─── GROUP BY CATEGORY ────────────────────────────────────
        $grouped = $accessories->groupBy(fn($a) => $a->category ?: 'Lain-lain');
        $row = 7;

        foreach ($grouped as $catName => $catItems) {
            $catQty   = 0; $catModal = 0.0; $catJual = 0.0;

            // CATEGORY SECTION HEADER
            $sheet->mergeCells("A{$row}:G{$row}");
            $sheet->setCellValue("A{$row}", "  ◆  KATEGORI: " . strtoupper($catName) . "  (" . $catItems->count() . " jenis)");
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font'      => ['name' => 'Segoe UI', 'bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $cCatBg]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getRowDimension($row)->setRowHeight(26);
            $row++;

            // COLUMN HEADERS (7 cols: A:G)
            $headers = ['No', 'Nama Aksesoris', 'Kategori', 'Stok Qty', 'Status Stok', 'Harga Modal', 'Total Modal'];
            $sheet->fromArray($headers, null, "A{$row}");
            $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
                'font'      => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $cColBg]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '475569']]],
            ]);
            $sheet->getRowDimension($row)->setRowHeight(28);
            $row++;

            // DATA ROWS
            $no = 1;
            foreach ($catItems as $item) {
                $modal      = (float) $item->purchase_price;
                $jual       = (float) $item->selling_price;
                $laba       = $jual - $modal;
                $margin     = $modal > 0 ? round(($laba / $modal) * 100, 1) : 0;
                $qty        = (int) $item->stock_qty;
                $totModal   = $modal * $qty;
                $totJual    = $jual  * $qty;
                $totLaba    = $laba  * $qty;
                $isLow      = $qty <= 5;
                $statusStok = $qty === 0 ? 'HABIS' : ($qty <= 5 ? 'MENIPIS' : 'AMAN');
                $statusBadgeBg = $qty === 0 ? 'FEE2E2' : ($qty <= 5 ? 'FEF3C7' : 'D1FAE5');
                $statusFg      = $qty === 0 ? 'B91C1C' : ($qty <= 5 ? 'B45309' : '14532D');

                $rowBg = ($no % 2 === 0) ? 'F8FAFC' : 'FFFFFF';

                $sheet->setCellValue("A{$row}", $no);
                $sheet->setCellValue("B{$row}", $item->name);
                $sheet->setCellValue("C{$row}", $catName);
                $sheet->setCellValue("D{$row}", $qty);
                $sheet->setCellValue("E{$row}", $statusStok);
                $sheet->setCellValue("F{$row}", $modal);
                $sheet->setCellValue("G{$row}", $totModal);

                // Standard zebra rows — clean, readable (same as finance)
                $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
                    'font'    => ['name' => 'Segoe UI', 'size' => 9, 'color' => ['rgb' => '1E293B']],
                    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $rowBg]],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $cBorder]]],
                ]);
                // Status badge on the status cell only
                $sheet->getStyle("E{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => $statusFg]],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $statusBadgeBg]],
                ]);
                // Bold red qty text for low stock (draws attention without coloring the whole row)
                if ($isLow) {
                    $sheet->getStyle("D{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => 'B91C1C']],
                    ]);
                }
                $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
                $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C{$row}:E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension($row)->setRowHeight(20);

                $catQty   += $qty;
                $catModal += $totModal;
                $catJual  += $totJual;
                $row++;
                $no++;
            }

            // CATEGORY SUBTOTAL ROW
            $catLaba = $catJual - $catModal;
            $sheet->setCellValue("B{$row}", "Subtotal: " . strtoupper($catName));
            $sheet->setCellValue("C{$row}", $catItems->count() . ' jenis');
            $sheet->setCellValue("D{$row}", $catQty . ' pcs');
            $sheet->setCellValue("G{$row}", $catModal);
            $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
                'font'      => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $cSubtot]],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '4338CA']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getRowDimension($row)->setRowHeight(22);
            $row++;
            $row++; // gap
        }

        // ─── GRAND TOTAL ROW ──────────────────────────────────────
        $row++;
        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->setCellValue("A{$row}", 'GRAND TOTAL SEMUA AKSESORIS');
        $sheet->setCellValue("D{$row}", $grandQty . ' pcs');
        $sheet->setCellValue("G{$row}", $grandModal);
        $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
            'font'      => ['name' => 'Segoe UI', 'bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $cGrand]],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '6D28D9']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle("A{$row}")->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'indent' => 1],
        ]);
        $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
        $sheet->getRowDimension($row)->setRowHeight(24);

        // ─── COLUMN WIDTHS ────────────────────────────────────────
        foreach (range('A', 'G') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
        $sheet->getColumnDimension('A')->setWidth(5);
    }

    private function buildSalesSheet($sheet, string $date): void
    {
        $sheet->setTitle('Penjualan Harian');

        $report     = $this->finance->dailyReport($date);
        $sales      = $report['sales'];
        $totalRev   = (float) $report['total_revenue'];
        $totalProfit= (float) $report['total_profit'];
        $dailyExpenses = $this->dailyExpenseSummary($date);
        $operationalExpenses = $dailyExpenses['expenses'];
        $operationalExpenseTotal = $dailyExpenses['total'];
        $operationalExpenseCash = $dailyExpenses['cash'];
        $operationalExpenseTransfer = $dailyExpenses['transfer'];
        $dailyPayments = $this->dailyPaymentSummary($date);
        $totalCash = $dailyPayments['cash'];
        $totalTransfer = $dailyPayments['transfer'];
        $totalDebt = $dailyPayments['debt'];
        $txCount   = count($sales);
        $avgPerTx  = $txCount > 0 ? $totalRev / $txCount : 0;
        $dateStr   = now()->format('d F Y H:i');
        $dateLabel = \Carbon\Carbon::parse($date)->translatedFormat('d F Y');

        // 1. HEADER BANNER
        $sheet->mergeCells('A1:I3');
        $sheet->setCellValue('A1', "LAPORAN PENJUALAN HARIAN\nALEX PHONE BANJARNEGARA");
        $sheet->getStyle('A1')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
        ]);

        // 2. SUBTITLE / DATE BAR
        $sheet->mergeCells('A4:I4');
        $sheet->setCellValue('A4', "Tanggal Laporan: {$dateLabel}   |   Dicetak Pada: {$dateStr}   |   Total Transaksi: {$txCount}");
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9, 'color' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EFF6FF']],
            'borders' => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '3B82F6']]],
        ]);

        // 3. METRICS SECTION HEADER
        $sheet->mergeCells('A6:B6');
        $sheet->setCellValue('A6', 'RINGKASAN PENJUALAN');
        $sheet->getStyle('A6')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->setCellValue('A7', 'Keterangan');
        $sheet->setCellValue('B7', 'Nilai');
        $sheet->getStyle('A7:B7')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ]);

        // 4. METRICS DATA
        $metrics = [
            ['Total Omzet (Pemasukan)', $totalRev],
            ['Total Laba Bersih', $totalProfit],
            ['Penerimaan Cash', $totalCash],
            ['Penerimaan Transfer', $totalTransfer],
            ['Piutang (Hutang)', $totalDebt],
            ['Pengeluaran Operasional', $operationalExpenseTotal],
            ['Jumlah Transaksi', $txCount],
            ['Rata-rata per Transaksi', $avgPerTx],
        ];

        $mRow = 8;
        foreach ($metrics as $idx => $m) {
            $sheet->setCellValue("A{$mRow}", $m[0]);
            $sheet->setCellValue("B{$mRow}", $m[1]);

            $fillColor = ($mRow % 2 === 0) ? 'F8FAFC' : 'FFFFFF';
            $sheet->getStyle("A{$mRow}:B{$mRow}")->applyFromArray([
                'font' => ['name' => 'Segoe UI', 'size' => 9],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fillColor]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
            ]);
            $sheet->getStyle("B{$mRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            // Format numbers (skip count row idx=6)
            if ($idx !== 6) {
                $sheet->getStyle("B{$mRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            }

            // Highlight total omzet row
            if ($mRow === 8) {
                $sheet->getStyle("A{$mRow}:B{$mRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '1E3A5F']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']],
                ]);
            }
            // Highlight laba bersih row
            if ($mRow === 9) {
                $sheet->getStyle("A{$mRow}:B{$mRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '065F46']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
                ]);
            }
            $mRow++;
        }

        // 5. NATIVE EXCEL BAR CHART — payment method breakdown (rows 10-12: Cash, Transfer, Piutang)
        $categories = [
            new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues(
                \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues::DATASERIES_TYPE_STRING,
                '\'Penjualan Harian\'!$A$10:$A$12', null, 3
            )
        ];
        $values = [
            new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues(
                \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues::DATASERIES_TYPE_NUMBER,
                '\'Penjualan Harian\'!$B$10:$B$12', null, 3
            )
        ];
        $series = new \PhpOffice\PhpSpreadsheet\Chart\DataSeries(
            \PhpOffice\PhpSpreadsheet\Chart\DataSeries::TYPE_BARCHART,
            \PhpOffice\PhpSpreadsheet\Chart\DataSeries::GROUPING_CLUSTERED,
            range(0, count($values) - 1),
            [], $categories, $values
        );
        $plotArea  = new \PhpOffice\PhpSpreadsheet\Chart\PlotArea(null, [$series]);
        $legend    = new \PhpOffice\PhpSpreadsheet\Chart\Legend(\PhpOffice\PhpSpreadsheet\Chart\Legend::POSITION_BOTTOM, null, false);
        $chartTitle = new \PhpOffice\PhpSpreadsheet\Chart\Title('Komposisi Metode Penerimaan (Rp)');
        $chart = new \PhpOffice\PhpSpreadsheet\Chart\Chart(
            'sales-bar-chart', $chartTitle, $legend, $plotArea,
            true, \PhpOffice\PhpSpreadsheet\Chart\DataSeries::EMPTY_AS_GAP, null, null
        );
        $chart->setTopLeftPosition('D6');
        $chart->setBottomRightPosition('K20');
        $sheet->addChart($chart);

        // 6. TRANSACTION DETAIL SECTION TITLE (row 23)
        $sheet->mergeCells('A23:I23');
        $sheet->setCellValue('A23', 'RINCIAN TRANSAKSI');
        $sheet->getStyle('A23')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 10, 'color' => ['rgb' => '0A2540']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension('23')->setRowHeight(24);

        // Column headers (row 24)
        $sheet->fromArray(['NO', 'INVOICE', 'ITEM TERJUAL', 'KASIR', 'TOTAL JUAL', 'LABA', 'CASH', 'TRANSFER', 'UTANG'], null, 'A24');
        $sheet->getStyle('A24:I24')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0A2540']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '0A2540']]],
        ]);
        $sheet->getRowDimension('24')->setRowHeight(24);

        // 7. TRANSACTION ROWS
        $row = 25;
        $no = 1;
        foreach ($sales as $s) {
            $sCash = $sTransfer = $sDebt = 0.0;
            foreach ($s->payments->where('source', 'sale') as $p) {
                $m = $p->method->value ?? $p->method;
                if ($m === 'cash')         $sCash     += $p->amount;
                elseif ($m === 'transfer') $sTransfer += $p->amount;
                elseif ($m === 'utang')    $sDebt     += $p->amount;
            }
            $items = $s->items->map(fn($i) => $i->unit_id
                ? (($i->unit->model->brand->name ?? '') . ' ' . ($i->unit->model->name ?? ''))
                : (($i->accessory->name ?? '—') . ' ×' . $i->quantity)
            )->join(', ');

            $sheet->setCellValue("A{$row}", $no);
            $sheet->setCellValue("B{$row}", $s->invoice_number);
            $sheet->setCellValue("C{$row}", $items);
            $sheet->setCellValue("D{$row}", $s->creator->name ?? '—');
            $sheet->setCellValue("E{$row}", $s->total_price);
            $sheet->setCellValue("F{$row}", $s->profit);
            $sheet->setCellValue("G{$row}", $sCash);
            $sheet->setCellValue("H{$row}", $sTransfer);
            $sheet->setCellValue("I{$row}", $sDebt);

            $fill = ($no % 2 === 0) ? 'F4F6FB' : 'FFFFFF';
            $sheet->getStyle("A{$row}:I{$row}")->applyFromArray([
                'font' => ['name' => 'Segoe UI', 'size' => 9],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fill]],
                'borders' => [
                    'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E4E9F2']],
                    'left' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E4E9F2']],
                    'right' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E4E9F2']],
                ],
            ]);

            // Styling specific columns
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => ['color' => ['rgb' => '7A8AA8']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
            ]);
            $sheet->getStyle("B{$row}")->applyFromArray([
                'font' => ['color' => ['rgb' => '7A8AA8']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
            ]);
            $sheet->getStyle("C{$row}")->applyFromArray([
                'font' => ['color' => ['rgb' => '0A2540']],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER]
            ]);
            $sheet->getStyle("D{$row}")->applyFromArray([
                'font' => ['color' => ['rgb' => '7A8AA8']],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER]
            ]);
            $sheet->getStyle("E{$row}")->applyFromArray([
                'font' => ['color' => ['rgb' => '0A2540']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER]
            ]);
            $sheet->getStyle("F{$row}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => '065F46']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER]
            ]);
            $sheet->getStyle("G{$row}")->applyFromArray([
                'font' => ['color' => ['rgb' => '7A8AA8']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER]
            ]);
            $sheet->getStyle("H{$row}")->applyFromArray([
                'font' => ['color' => ['rgb' => '7A8AA8']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER]
            ]);
            $sheet->getStyle("I{$row}")->applyFromArray([
                'font' => ['color' => ['rgb' => '7A8AA8']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER]
            ]);

            // Number formatting
            $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0;;""—""');
            $sheet->getStyle("H{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0;;""—""');
            $sheet->getStyle("I{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0;;""—""');

            $sheet->getRowDimension($row)->setRowHeight(22);
            $row++;
            $no++;
        }

        // 8. TOTALS ROW
        if ($txCount > 0) {
            $sheet->mergeCells("A{$row}:D{$row}");
            $sheet->setCellValue("A{$row}", 'TOTAL:');
            $sheet->setCellValue("E{$row}", $totalRev);
            $sheet->setCellValue("F{$row}", $totalProfit);
            $sheet->setCellValue("G{$row}", $totalCash);
            $sheet->setCellValue("H{$row}", $totalTransfer);
            $sheet->setCellValue("I{$row}", $totalDebt);

            $sheet->getStyle("A{$row}:I{$row}")->applyFromArray([
                'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9, 'color' => ['rgb' => '0A2540']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8EDF5']],
                'borders' => [
                    'top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '0A2540']],
                    'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '0A2540']],
                    'left' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E4E9F2']],
                    'right' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E4E9F2']],
                ],
            ]);

            $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0;;""—""');
            $sheet->getStyle("H{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0;;""—""');
            $sheet->getStyle("I{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0;;""—""');

            $sheet->getStyle("E{$row}:I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $sheet->getStyle("F{$row}")->applyFromArray([
                'font' => ['color' => ['rgb' => '065F46']]
            ]);

            $sheet->getRowDimension($row)->setRowHeight(24);
            $row++;
        }

        $row += 3;
        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->setCellValue("A{$row}", 'REKAP PENGELUARAN OPERASIONAL');
        $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B91C1C']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $row++;
        $sheet->fromArray(['NO', 'TANGGAL', 'KETERANGAN', 'KATEGORI', 'METODE', 'JUMLAH'], null, "A{$row}");
        $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9, 'color' => ['rgb' => '7F1D1D']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEE2E2']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FECACA']]],
        ]);
        $row++;

        if ($operationalExpenses->isEmpty()) {
            $sheet->mergeCells("A{$row}:F{$row}");
            $sheet->setCellValue("A{$row}", 'Tidak ada pengeluaran operasional pada tanggal ini');
            $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                'font' => ['name' => 'Segoe UI', 'size' => 9, 'italic' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $row++;
        } else {
            foreach ($operationalExpenses as $idx => $expense) {
                $categoryLabel = $expense->category === 'tarik_owner'
                    ? 'Tarik Saldo Owner'
                    : ($expense->category === 'listrik' ? 'Listrik & Gas' : ucwords($expense->category));
                $methodLabel = ($expense->payment_method ?? 'cash') === 'transfer' ? 'Transfer' : 'Tunai';

                $sheet->setCellValue("A{$row}", $idx + 1);
                $sheet->setCellValue("B{$row}", $expense->expense_date->format('d/m/Y'));
                $sheet->setCellValue("C{$row}", $expense->description);
                $sheet->setCellValue("D{$row}", $categoryLabel);
                $sheet->setCellValue("E{$row}", $methodLabel);
                $sheet->setCellValue("F{$row}", $expense->amount);
                $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                    'font' => ['name' => 'Segoe UI', 'size' => 9],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $idx % 2 === 0 ? 'FFFFFF' : 'FFF7F7']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FEE2E2']]],
                ]);
                $sheet->getStyle("A{$row}:B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
                $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $row++;
            }

            $sheet->mergeCells("A{$row}:E{$row}");
            $sheet->setCellValue("A{$row}", 'TOTAL PENGELUARAN OPERASIONAL');
            $sheet->setCellValue("F{$row}", $operationalExpenseTotal);
            $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9, 'color' => ['rgb' => '7F1D1D']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEE2E2']],
                'borders' => ['top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => 'B91C1C']]],
            ]);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        // 9. COLUMN WIDTHS & ROW HEIGHTS
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getColumnDimension('C')->setAutoSize(false)->setWidth(45);
        $sheet->getStyle('C25:C' . $row)->getAlignment()->setWrapText(true);

        $sheet->getRowDimension('1')->setRowHeight(20);
        $sheet->getRowDimension('2')->setRowHeight(20);
        $sheet->getRowDimension('3')->setRowHeight(20);
        $sheet->getRowDimension('4')->setRowHeight(24);
        $sheet->getRowDimension('6')->setRowHeight(28);
    }

    private function buildSalesTransactionsSheet($sheet, ?string $startDate = null, ?string $endDate = null): void
    {
        $sheet->setTitle('Transaksi Penjualan');

        $salesQuery = \App\Models\Sale::with(['creator', 'payments', 'items.unit.model.brand', 'items.accessory'])->where('status', 'approved');
        if ($startDate) $salesQuery->whereDate('sale_date', '>=', $startDate);
        if ($endDate)   $salesQuery->whereDate('sale_date', '<=', $endDate);
        $sales = $salesQuery->latest('sale_date')->get();

        $totalRev    = (float) $sales->sum('total_price');
        $totalProfit = (float) $sales->sum('profit');
        $txCount     = $sales->count();
        $totalCash = $totalTransfer = $totalDebt = 0.0;
        foreach ($sales as $s) {
            foreach ($s->payments as $p) {
                $m = $p->method->value ?? $p->method;
                if ($m === 'cash')         $totalCash     += $p->amount;
                elseif ($m === 'transfer') $totalTransfer += $p->amount;
                elseif ($m === 'utang')    $totalDebt     += $p->amount;
            }
        }
        $avgPerTx  = $txCount > 0 ? $totalRev / $txCount : 0;
        $dateStr   = now()->format('d F Y H:i');
        $periodStr = ($startDate || $endDate)
            ? (($startDate ? \Carbon\Carbon::parse($startDate)->format('d/m/Y') : 'Awal') . ' s.d ' . ($endDate ? \Carbon\Carbon::parse($endDate)->format('d/m/Y') : 'Akhir'))
            : 'Semua Periode';

        // 1. HEADER BANNER
        $sheet->mergeCells('A1:I3');
        $sheet->setCellValue('A1', "DETAIL TRANSAKSI PENJUALAN\nALEX PHONE BANJARNEGARA");
        $sheet->getStyle('A1')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
        ]);

        // 2. SUBTITLE BAR
        $sheet->mergeCells('A4:I4');
        $sheet->setCellValue('A4', "Periode: {$periodStr}   |   Dicetak Pada: {$dateStr}   |   Total Transaksi: {$txCount}");
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9, 'color' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EFF6FF']],
            'borders' => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '3B82F6']]],
        ]);

        // 3. METRICS SECTION HEADER
        $sheet->mergeCells('A6:B6');
        $sheet->setCellValue('A6', 'RINGKASAN STATISTIK');
        $sheet->getStyle('A6')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->setCellValue('A7', 'Keterangan');
        $sheet->setCellValue('B7', 'Nilai');
        $sheet->getStyle('A7:B7')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ]);

        // 4. METRICS DATA
        $metrics = [
            ['Total Omzet (Pemasukan)', $totalRev, true],
            ['Total Laba Bersih', $totalProfit, true],
            ['Penerimaan Cash', $totalCash, true],
            ['Penerimaan Transfer', $totalTransfer, true],
            ['Piutang (Hutang)', $totalDebt, true],
            ['Jumlah Transaksi', $txCount, false],
            ['Rata-rata per Transaksi', $avgPerTx, true],
        ];

        $mRow = 8;
        foreach ($metrics as $idx => [$label, $value, $isCurrency]) {
            $sheet->setCellValue("A{$mRow}", $label);
            $sheet->setCellValue("B{$mRow}", $value);
            $fillColor = ($mRow % 2 === 0) ? 'F8FAFC' : 'FFFFFF';
            $sheet->getStyle("A{$mRow}:B{$mRow}")->applyFromArray([
                'font' => ['name' => 'Segoe UI', 'size' => 9],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fillColor]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
            ]);
            $sheet->getStyle("B{$mRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            if ($isCurrency) $sheet->getStyle("B{$mRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            // Highlight total omzet blue
            if ($mRow === 8) {
                $sheet->getStyle("A{$mRow}:B{$mRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '1E3A5F']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']],
                ]);
            }
            // Highlight laba bersih green
            if ($mRow === 9) {
                $sheet->getStyle("A{$mRow}:B{$mRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '065F46']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
                ]);
            }
            $mRow++;
        }

        // 5. CHART — payment method bar chart (Cash/Transfer/Piutang rows 10-12)
        $categories = [
            new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues(
                \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues::DATASERIES_TYPE_STRING,
                '\'Transaksi Penjualan\'!$A$10:$A$12', null, 3
            )
        ];
        $values = [
            new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues(
                \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues::DATASERIES_TYPE_NUMBER,
                '\'Transaksi Penjualan\'!$B$10:$B$12', null, 3
            )
        ];
        $series = new \PhpOffice\PhpSpreadsheet\Chart\DataSeries(
            \PhpOffice\PhpSpreadsheet\Chart\DataSeries::TYPE_BARCHART,
            \PhpOffice\PhpSpreadsheet\Chart\DataSeries::GROUPING_CLUSTERED,
            range(0, count($values) - 1), [], $categories, $values
        );
        $plotArea  = new \PhpOffice\PhpSpreadsheet\Chart\PlotArea(null, [$series]);
        $legend    = new \PhpOffice\PhpSpreadsheet\Chart\Legend(\PhpOffice\PhpSpreadsheet\Chart\Legend::POSITION_BOTTOM, null, false);
        $chartTitle = new \PhpOffice\PhpSpreadsheet\Chart\Title('Komposisi Metode Penerimaan (Rp)');
        $chart = new \PhpOffice\PhpSpreadsheet\Chart\Chart(
            'tx-payment-chart', $chartTitle, $legend, $plotArea,
            true, \PhpOffice\PhpSpreadsheet\Chart\DataSeries::EMPTY_AS_GAP, null, null
        );
        $chart->setTopLeftPosition('D6');
        $chart->setBottomRightPosition('K20');
        $sheet->addChart($chart);

        // 6. TRANSACTION TABLE HEADER (row 23)
        $sheet->mergeCells('A23:I23');
        $sheet->setCellValue('A23', 'RINCIAN SEMUA TRANSAKSI PENJUALAN');
        $sheet->getStyle('A23')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D4ED8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->fromArray(['NO', 'INVOICE', 'ITEM TERJUAL', 'KASIR', 'TOTAL JUAL', 'LABA', 'CASH', 'TRANSFER', 'UTANG'], null, 'A24');
        $sheet->getStyle('A24:I24')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BFDBFE']]],
        ]);

        // 7. TRANSACTION ROWS
        $row = 25;
        $no = 1;
        foreach ($sales as $s) {
            $cash = $transfer = $debt = 0.0;
            foreach ($s->payments as $p) {
                $m = $p->method->value ?? $p->method;
                if ($m === 'cash')         $cash     += $p->amount;
                elseif ($m === 'transfer') $transfer += $p->amount;
                elseif ($m === 'utang')    $debt     += $p->amount;
            }
            
            $items = $s->items->map(fn($i) => $i->unit_id
                ? (($i->unit->model->brand->name ?? '') . ' ' . ($i->unit->model->name ?? ''))
                : (($i->accessory->name ?? '—') . ' ×' . $i->quantity)
            )->join(', ');

            $sheet->setCellValue("A{$row}", $no);
            $sheet->setCellValue("B{$row}", $s->invoice_number);
            $sheet->setCellValue("C{$row}", $items);
            $sheet->setCellValue("D{$row}", $s->creator->name ?? '—');
            $sheet->setCellValue("E{$row}", $s->total_price);
            $sheet->setCellValue("F{$row}", $s->profit);
            $sheet->setCellValue("G{$row}", $cash);
            $sheet->setCellValue("H{$row}", $transfer);
            $sheet->setCellValue("I{$row}", $debt);

            $fill = ($no % 2 === 0) ? 'EFF6FF' : 'FFFFFF';
            $sheet->getStyle("A{$row}:I{$row}")->applyFromArray([
                'font' => ['name' => 'Segoe UI', 'size' => 9],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fill]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DBEAFE']]],
            ]);
            
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            foreach (['E', 'F', 'G', 'H', 'I'] as $col) {
                $sheet->getStyle("{$col}{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0;;""—""');
                $sheet->getStyle("{$col}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
            $row++;
            $no++;
        }

        // 8. TOTALS ROW
        if ($txCount > 0) {
            $sheet->mergeCells("A{$row}:D{$row}");
            $sheet->setCellValue("A{$row}", 'TOTAL');
            $sheet->setCellValue("E{$row}", $totalRev);
            $sheet->setCellValue("F{$row}", $totalProfit);
            $sheet->setCellValue("G{$row}", $totalCash);
            $sheet->setCellValue("H{$row}", $totalTransfer);
            $sheet->setCellValue("I{$row}", $totalDebt);
            $sheet->getStyle("A{$row}:I{$row}")->applyFromArray([
                'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9, 'color' => ['rgb' => '1E3A5F']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '93C5FD']]],
            ]);
            foreach (['E', 'F', 'G', 'H', 'I'] as $col) {
                $sheet->getStyle("{$col}{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0;;""—""');
                $sheet->getStyle("{$col}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getColumnDimension('C')->setAutoSize(false)->setWidth(45);
        $sheet->getStyle('C25:C' . $row)->getAlignment()->setWrapText(true);

        $sheet->getRowDimension('1')->setRowHeight(20);
        $sheet->getRowDimension('2')->setRowHeight(20);
        $sheet->getRowDimension('3')->setRowHeight(20);
        $sheet->getRowDimension('4')->setRowHeight(24);
        $sheet->getRowDimension('6')->setRowHeight(28);
        $sheet->getRowDimension('23')->setRowHeight(28);
    }

    private function buildFinanceSheet($sheet, ?string $startDate = null, ?string $endDate = null): void
    {
        $sheet->setTitle('Laporan Keuangan');

        // Fetch data
        $data = $this->finance->financeSummaryForExport($startDate, $endDate);
        
        $expensesQuery = \App\Models\Expense::with('creator');
        $isSuperAdmin = auth()->user()?->isSuperAdmin() ?? false;
        if (!$isSuperAdmin) {
            $expensesQuery->whereNotIn('category', ['tarik_owner', 'gaji']);
        }
        if ($startDate) {
            $expensesQuery->whereDate('expense_date', '>=', $startDate);
        }
        if ($endDate) {
            $expensesQuery->whereDate('expense_date', '<=', $endDate);
        }
        $expensesList = $expensesQuery->latest('expense_date')->get();

        // Fetch units purchased in the filtered range and map to virtual Expense models
        $unitsListQuery = \App\Models\Unit::with('creator', 'model.brand');
        if ($startDate) {
            $unitsListQuery->whereDate('purchase_date', '>=', $startDate);
        }
        if ($endDate) {
            $unitsListQuery->whereDate('purchase_date', '<=', $endDate);
        }
        $unitsList = $unitsListQuery->latest('purchase_date')->get();

        $virtualExpenses = $unitsList->map(function ($unit) {
            $brand = $unit->model->brand->name ?? '';
            $model = $unit->model->name ?? '';
            $spec = "{$brand} {$model} ({$unit->ram}/{$unit->rom}) - {$unit->color}";
            $imei = $unit->imei ? " IMEI: {$unit->imei}" : "";
            $sn = $unit->serial_number ? " SN: {$unit->serial_number}" : "";
            $paymentMethod = $unit->purchase_payment_method ?? 'cash';
            
            $exp = new \App\Models\Expense();
            $exp->id = null;
            $exp->is_virtual = true;
            $exp->unit_id = $unit->id;
            $exp->expense_date = $unit->purchase_date;
            $exp->description = "Pembelian Stok HP: {$spec}";
            $exp->category = "stok_hp";
            $exp->payment_method = $paymentMethod;
            $exp->notes = "Kondisi: " . ucfirst($unit->unit_type->value) . ($unit->grade ? " (Grade {$unit->grade})" : "") . $imei . $sn;
            $exp->amount = $unit->purchase_price;
            $exp->created_by = $unit->created_by;
            $exp->setRelation('creator', $unit->creator);
            return $exp;
        });

        // Keep only real operational expenses (no HP virtual expenses in operational table)
        // virtualExpenses (HP purchases) are shown separately below
        $expensesList = $expensesList->sortByDesc(function ($item) {
            return $item->expense_date instanceof \Carbon\Carbon
                ? $item->expense_date->toDateString()
                : $item->expense_date;
        });

        // Fetch approved sales payments for Omzet breakdown
        $salesQuery = \App\Models\Sale::where('status', 'approved');
        if ($startDate) {
            $salesQuery->whereDate('sale_date', '>=', $startDate);
        }
        if ($endDate) {
            $salesQuery->whereDate('sale_date', '<=', $endDate);
        }
        $salesList = $salesQuery->get();
        $txCount = $salesList->count();
        
        $initialPaymentsInPeriod = \App\Models\SalePayment::whereIn('method', ['cash', 'transfer'])
            ->where('source', 'sale')
            ->whereHas('sale', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'approved');
                if ($startDate) $q->whereDate('sale_date', '>=', $startDate);
                if ($endDate)   $q->whereDate('sale_date', '<=', $endDate);
            })
            ->selectRaw('method, SUM(amount) as total')
            ->groupBy('method')
            ->pluck('total', 'method');
        $debtPaymentsInPeriodQuery = \App\Models\SalePayment::whereIn('method', ['cash', 'transfer'])
            ->whereNotNull('created_at')
            ->whereHas('sale', fn($q) => $q->where('status', 'approved'))
            ->where(function ($q) {
                $q->where('source', 'debt_payment')
                  ->orWhereRaw('DATE(sale_payments.created_at) > (SELECT DATE(sales.created_at) FROM sales WHERE sales.id = sale_payments.sale_id)');
            });
        if ($startDate) $debtPaymentsInPeriodQuery->whereDate('created_at', '>=', $startDate);
        if ($endDate)   $debtPaymentsInPeriodQuery->whereDate('created_at', '<=', $endDate);
        $debtPaymentsInPeriod = $debtPaymentsInPeriodQuery
            ->selectRaw('method, SUM(amount) as total')
            ->groupBy('method')
            ->pluck('total', 'method');

        $penerimaanCash = (float) ($initialPaymentsInPeriod['cash'] ?? 0)
            + (float) ($debtPaymentsInPeriod['cash'] ?? 0);
        $penerimaanTransfer = (float) ($initialPaymentsInPeriod['transfer'] ?? 0)
            + (float) ($debtPaymentsInPeriod['transfer'] ?? 0);
        $piutang = 0;
        foreach ($salesList as $sale) {
            foreach ($sale->payments as $payment) {
                $method = $payment->method->value ?? $payment->method;
                if ($method === 'utang') {
                    $piutang += $payment->amount;
                }
            }
        }
        $avgPerTx = $txCount > 0 ? $data['revenue'] / $txCount : 0;

        // Fetch ASET BARANG values
        $summary = $this->finance->reportSummary($startDate, $endDate);
        $unitVal = (float) $summary['assetValue'];
        $accAssetVal = (float) $summary['accAssetValue'];
        $unpaidDebts = (float) $summary['unpaidDebts'];
        $bankVal = (float) $summary['saldoAtmLifetime'];
        $cashVal = (float) $summary['saldoKas'];

        // 1. HEADER TITLE BANNER
        $sheet->mergeCells('A1:K3');
        $sheet->setCellValue('A1', "LAPORAN RINGKASAN KEUANGAN & LABA RUGI\nALEX PHONE BANJARNEGARA");
        $sheet->getStyle('A1')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'name' => 'Segoe UI',
                'bold' => true,
                'size' => 14,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0F766E'] // Deep Teal
            ]
        ]);
        
        // Subtitle card with Date range info
        $sheet->mergeCells('A4:K4');
        $dateStr = now()->format('d F Y H:i');
        $periodStr = ($startDate || $endDate) 
            ? ($startDate ? \Carbon\Carbon::parse($startDate)->format('d/m/Y') : 'Awal') . ' s.d ' . ($endDate ? \Carbon\Carbon::parse($endDate)->format('d/m/Y') : 'Akhir')
            : 'Semua Periode';
        $sheet->setCellValue('A4', "Periode Laporan: {$periodStr}   |   Dicetak Pada: {$dateStr}");
        $sheet->getStyle('A4')->applyFromArray([
            'font' => [
                'name' => 'Segoe UI',
                'bold' => true,
                'size' => 9,
                'color' => ['rgb' => '0F766E']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F0FDFA'] // Light Teal
            ],
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '14B8A6']
                ]
            ]
        ]);

        // 2. RINGKASAN STATISTIK TABLE (A6:B14)
        $sheet->mergeCells('A6:B6');
        $sheet->setCellValue('A6', 'RINGKASAN STATISTIK');
        $sheet->getStyle('A6')->applyFromArray([
            'font' => [
                'name' => 'Segoe UI',
                'bold' => true,
                'size' => 10,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E293B'] // Dark Slate
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ]
        ]);
        
        $sheet->setCellValue('A7', 'Keterangan');
        $sheet->setCellValue('B7', 'Nilai');
        $sheet->getStyle('A7:B7')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Values
        $sheet->setCellValue('A8', 'Total Omzet (Pemasukan)');
        $sheet->setCellValue('B8', $data['revenue']);
        $sheet->setCellValue('A9', 'Total Laba Bersih');
        $sheet->setCellValue('B9', $data['profit']);
        $sheet->setCellValue('A10', 'Penerimaan Cash');
        $sheet->setCellValue('B10', $penerimaanCash);
        $sheet->setCellValue('A11', 'Penerimaan Transfer');
        $sheet->setCellValue('B11', $penerimaanTransfer);
        $sheet->setCellValue('A12', 'Piutang (Hutang)');
        $sheet->setCellValue('B12', $piutang);
        $sheet->setCellValue('A13', 'Jumlah Transaksi');
        $sheet->setCellValue('B13', $txCount);
        $sheet->setCellValue('A14', 'Rata-rata per Transaksi');
        $sheet->setCellValue('B14', $avgPerTx);

        // Styling Ringkasan Statistik
        for ($r = 8; $r <= 14; $r++) {
            $fillColor = ($r % 2 === 0) ? 'F8FAFC' : 'FFFFFF';
            $sheet->getStyle("A{$r}:B{$r}")->applyFromArray([
                'font' => ['name' => 'Segoe UI', 'size' => 9],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fillColor]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]]
            ]);
            $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            if ($r != 13) {
                $sheet->getStyle("B{$r}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            } else {
                $sheet->getStyle("B{$r}")->getNumberFormat()->setFormatCode('#,##0');
            }
        }
        
        // Highlight Total Omzet & Laba
        $sheet->getStyle('A8:B8')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'color' => ['rgb' => '1E3A8A']], // Dark Blue
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EFF6FF']]
        ]);
        $sheet->getStyle('A9:B9')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'color' => ['rgb' => '065F46']], // Dark Green
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']]
        ]);

        // 3. NATIVE EXCEL COLUMN CHART COMPARISON (payment method breakdown)
        $categories = [
            new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues(
                \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues::DATASERIES_TYPE_STRING, 
                '\'Laporan Keuangan\'!$A$10:$A$12', 
                null, 
                3
            )
        ];
        $values = [
            new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues(
                \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues::DATASERIES_TYPE_NUMBER, 
                '\'Laporan Keuangan\'!$B$10:$B$12', 
                null, 
                3
            )
        ];
        $series = new \PhpOffice\PhpSpreadsheet\Chart\DataSeries(
            \PhpOffice\PhpSpreadsheet\Chart\DataSeries::TYPE_BARCHART, 
            \PhpOffice\PhpSpreadsheet\Chart\DataSeries::GROUPING_CLUSTERED, 
            range(0, count($values) - 1), 
            [], 
            $categories, 
            $values
        );
        $plotArea = new \PhpOffice\PhpSpreadsheet\Chart\PlotArea(null, [$series]);
        $legend = new \PhpOffice\PhpSpreadsheet\Chart\Legend(\PhpOffice\PhpSpreadsheet\Chart\Legend::POSITION_BOTTOM, null, false);
        $chartTitle = new \PhpOffice\PhpSpreadsheet\Chart\Title('Komposisi Metode Penerimaan (Rp)');
        $chart = new \PhpOffice\PhpSpreadsheet\Chart\Chart(
            'finance-bar-chart',
            $chartTitle,
            $legend,
            $plotArea,
            true,
            \PhpOffice\PhpSpreadsheet\Chart\DataSeries::EMPTY_AS_GAP,
            null,
            null
        );
        $chart->setTopLeftPosition('D6');
        $chart->setBottomRightPosition('K14');
        $sheet->addChart($chart);

        // 4. ASET BARANG SECTION (Row 16 onwards)
        $sheet->mergeCells('A16:B16');
        $sheet->setCellValue('A16', 'ASET BARANG');
        $sheet->getStyle('A16')->applyFromArray([
            'font' => [
                'name' => 'Segoe UI',
                'bold' => true,
                'size' => 10,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E293B'] // Dark Slate to match Ringkasan Statistik
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ]
        ]);

        $sheet->setCellValue('A17', 'ASET');
        $sheet->setCellValue('B17', 'NILAI');
        $sheet->getStyle('A17:B17')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9, 'color' => ['rgb' => '1E293B']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']], // Light Gray to match Ringkasan Statistik
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        $sheet->setCellValue('A18', 'STOK HANDPHONE (HP)');
        $sheet->setCellValue('B18', $unitVal);
        $sheet->setCellValue('A19', 'STOK AKSESORIS');
        $sheet->setCellValue('B19', $accAssetVal);
        $sheet->setCellValue('A20', 'PIUTANG AKTIF');
        $sheet->setCellValue('B20', $unpaidDebts);
        $sheet->setCellValue('A21', 'SALDO ATM');
        $sheet->setCellValue('B21', $bankVal);
        $sheet->setCellValue('A22', 'UANG CASH');
        $sheet->setCellValue('B22', $cashVal);

        // Styling for rows 18-22
        for ($r = 18; $r <= 22; $r++) {
            $fillColor = ($r % 2 === 0) ? 'F8FAFC' : 'FFFFFF';
            $sheet->getStyle("A{$r}:B{$r}")->applyFromArray([
                'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fillColor]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]]
            ]);
            $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setIndent(1);
            $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("B{$r}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
        }

        // Row 23: TOTAL ASET (SALDO RILL)
        $sheet->setCellValue('A23', 'TOTAL ASET (SALDO RILL)');
        $sheet->setCellValue('B23', '=SUM(B18:B22)');
        $sheet->getStyle('A23:B23')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 10, 'color' => ['rgb' => '047857']], // Emerald 700
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']], // Emerald 100
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'A7F3D0']]]
        ]);
        $sheet->getStyle('A23')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setIndent(1);
        $sheet->getStyle('B23')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('B23')->getNumberFormat()->setFormatCode('"Rp "#,##0');

        // Explicitly clear cells in rows 24-31 to make sure they are blank and have no legacy styles or formulas
        for ($r = 24; $r <= 31; $r++) {
            $sheet->setCellValue("A{$r}", '');
            $sheet->setCellValue("B{$r}", '');
            $sheet->getStyle("A{$r}:B{$r}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_NONE],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_NONE]
                ]
            ]);
        }

        // 5. NATIVE EXCEL COLUMN CHART FOR ASET BARANG (Row 16 to 22, columns D to K)
        $categories2 = [
            new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues(
                \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues::DATASERIES_TYPE_STRING, 
                '\'Laporan Keuangan\'!$A$18:$A$22', 
                null, 
                5
            )
        ];
        $values2 = [
            new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues(
                \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues::DATASERIES_TYPE_NUMBER, 
                '\'Laporan Keuangan\'!$B$18:$B$22', 
                null, 
                5
            )
        ];
        $series2 = new \PhpOffice\PhpSpreadsheet\Chart\DataSeries(
            \PhpOffice\PhpSpreadsheet\Chart\DataSeries::TYPE_BARCHART, 
            \PhpOffice\PhpSpreadsheet\Chart\DataSeries::GROUPING_CLUSTERED, 
            range(0, count($values2) - 1), 
            [], 
            $categories2, 
            $values2
        );
        $plotArea2 = new \PhpOffice\PhpSpreadsheet\Chart\PlotArea(null, [$series2]);
        $legend2 = new \PhpOffice\PhpSpreadsheet\Chart\Legend(\PhpOffice\PhpSpreadsheet\Chart\Legend::POSITION_BOTTOM, null, false);
        $chartTitle2 = new \PhpOffice\PhpSpreadsheet\Chart\Title('Komposisi Aset (Rp)');
        $chart2 = new \PhpOffice\PhpSpreadsheet\Chart\Chart(
            'asset-bar-chart',
            $chartTitle2,
            $legend2,
            $plotArea2,
            true,
            \PhpOffice\PhpSpreadsheet\Chart\DataSeries::EMPTY_AS_GAP,
            null,
            null
        );
        $chart2->setTopLeftPosition('D16');
        $chart2->setBottomRightPosition('K31');
        $sheet->addChart($chart2);

        // 6. DETAIL TABLES SECTION (Row 35 onwards)
        // Table 1: TOTAL PIUTANG (A35:C35)
        $activeDebtsList = \App\Models\Debt::with('sale.creator')
            ->where('status', '!=', 'paid')
            ->latest()
            ->get();
        $totalActiveDebts = $activeDebtsList->sum(fn($d) => $d->amount - $d->paid_amount);

        $sheet->mergeCells('A35:C35');
        $sheet->setCellValue('A35', 'TOTAL PIUTANG: ' . 'Rp ' . number_format($totalActiveDebts, 0, ',', '.'));
        $sheet->getStyle('A35')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '991B1B']], // Dark Red
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        
        $sheet->fromArray(['Tanggal', 'Pelanggan', 'Jumlah'], null, 'A36');
        $sheet->getStyle('A36:C36')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9, 'color' => ['rgb' => '991B1B']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEE2E2']], // Red 100
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FCA5A5']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Table 2: Operational Expenses Header E35:J35
        $sheet->mergeCells('E35:J35');
        $sheet->setCellValue('E35', 'RINCIAN PENGELUARAN OPERASIONAL');
        $sheet->getStyle('E35')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'BE123C']], // Soft Red
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);

        $sheet->fromArray(['Tanggal', 'Keterangan', 'Kategori', 'Metode', 'Catatan', 'Dicatat Oleh'], null, 'E36');
        $sheet->getStyle('E36:J36')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9, 'color' => ['rgb' => 'BE123C']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFE4E6']], // Rose 100
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FDA4AF']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);


        // Populate Piutang
        $piutangRow = 37;
        foreach ($activeDebtsList as $d) {
            $sheet->setCellValue("A{$piutangRow}", $d->sale->sale_date ? $d->sale->sale_date->format('d/m/Y') : $d->created_at->format('d/m/Y'));
            $sheet->setCellValue("B{$piutangRow}", $d->sale->customer_name ?? '—');
            $sheet->setCellValue("C{$piutangRow}", $d->amount - $d->paid_amount);
            
            $fill = ($piutangRow % 2 === 0) ? 'FEF2F2' : 'FFFFFF'; // Soft Red stripe
            $sheet->getStyle("A{$piutangRow}:C{$piutangRow}")->applyFromArray([
                'font' => ['name' => 'Segoe UI', 'size' => 9],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fill]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FEE2E2']]]
            ]);
            $sheet->getStyle("C{$piutangRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("A{$piutangRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$piutangRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            
            $piutangRow++;
        }
        if ($activeDebtsList->isEmpty()) {
            $sheet->mergeCells("A37:C37");
            $sheet->setCellValue("A37", "Tidak ada piutang aktif");
            $sheet->getStyle("A37:C37")->applyFromArray([
                'font' => ['name' => 'Segoe UI', 'size' => 9, 'italic' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);
        }

        // Populate Operational Expenses only (real Expense records, no HP)
        $expRow = 37;
        foreach ($expensesList as $e) {
            $categoryLabel = match($e->category) {
                'tarik_owner' => 'Tarik Saldo Owner',
                'listrik'     => 'Listrik & Gas',
                'stok_hp'     => 'Stok HP',
                default       => ucwords($e->category)
            };
            $methodLabel = match($e->payment_method ?? 'cash') {
                'transfer' => 'Transfer',
                'cash'     => 'Tunai',
                default    => ucfirst($e->payment_method ?? 'cash')
            };
            $dateVal = $e->expense_date instanceof \Carbon\Carbon
                ? $e->expense_date->format('d/m/Y')
                : \Carbon\Carbon::parse($e->expense_date)->format('d/m/Y');

            $sheet->setCellValue("E{$expRow}", $dateVal);
            $sheet->setCellValue("F{$expRow}", $e->description);
            $sheet->setCellValue("G{$expRow}", $categoryLabel);
            $sheet->setCellValue("H{$expRow}", $e->amount);
            $sheet->setCellValue("I{$expRow}", $e->notes ?: '—');
            $sheet->setCellValue("J{$expRow}", $e->creator->name ?? '—');

            $fill = ($expRow % 2 === 0) ? 'FFF1F2' : 'FFFFFF'; // Rose stripe
            $sheet->getStyle("E{$expRow}:J{$expRow}")->applyFromArray([
                'font'    => ['name' => 'Segoe UI', 'size' => 9],
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fill]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFE4E6']]]
            ]);
            $sheet->getStyle("H{$expRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("E{$expRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$expRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("H{$expRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $expRow++;
        }
        if ($expensesList->isEmpty()) {
            $sheet->mergeCells("E37:J37");
            $sheet->setCellValue("E37", "Tidak ada pengeluaran operasional");
            $sheet->getStyle("E37:J37")->applyFromArray([
                'font'      => ['name' => 'Segoe UI', 'size' => 9, 'italic' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);
            $expRow = 38;
        }

        // HP Purchases table — below the operational expenses
        $hpTableStartRow = max($piutangRow, $expRow) + 2;
        $sheet->mergeCells("E{$hpTableStartRow}:J{$hpTableStartRow}");
        $totalHPExcel = $virtualExpenses->sum('amount');
        $sheet->setCellValue("E{$hpTableStartRow}", 'PEMBELIAN STOK HP (Total: Rp ' . number_format($totalHPExcel, 0, ',', '.') . ')');
        $sheet->getStyle("E{$hpTableStartRow}")->applyFromArray([
            'font'      => ['name' => 'Segoe UI', 'bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D4ED8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension((string)$hpTableStartRow)->setRowHeight(28);

        $hpHeaderRow = $hpTableStartRow + 1;
        $sheet->fromArray(['Tanggal', 'Nama HP', 'IMEI / SN', 'Kondisi', 'Metode', 'Inputter'], null, "E{$hpHeaderRow}");
        $sheet->getStyle("E{$hpHeaderRow}:J{$hpHeaderRow}")->applyFromArray([
            'font'      => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9, 'color' => ['rgb' => '1D4ED8']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BFDBFE']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
        $sheet->getRowDimension((string)$hpHeaderRow)->setRowHeight(22);

        $hpRow = $hpHeaderRow + 1;
        foreach ($virtualExpenses as $ve) {
            $unit = \App\Models\Unit::with(['model.brand', 'creator'])->find($ve->unit_id);
            $imeiSn = collect([$unit?->imei ? 'IMEI: '.$unit->imei : null, $unit?->serial_number ? 'SN: '.$unit->serial_number : null])->filter()->join(' | ') ?: '—';
            $kondisi = ucfirst($unit?->unit_type?->value ?? '') . ($unit?->grade ? ' Grade '.$unit->grade : '');
            $hpDate = $ve->expense_date instanceof \Carbon\Carbon ? $ve->expense_date->format('d/m/Y') : \Carbon\Carbon::parse($ve->expense_date)->format('d/m/Y');
            $hpMethod = match($ve->payment_method ?? 'cash') {
                'transfer' => 'Transfer', 'cash' => 'Tunai', default => ucfirst($ve->payment_method ?? 'cash')
            };

            $sheet->setCellValue("E{$hpRow}", $hpDate);
            $sheet->setCellValue("F{$hpRow}", $ve->description);
            $sheet->setCellValue("G{$hpRow}", $imeiSn);
            $sheet->setCellValue("H{$hpRow}", $kondisi);
            $sheet->setCellValue("I{$hpRow}", $hpMethod);
            $sheet->setCellValue("J{$hpRow}", $ve->creator?->name ?? '—');

            $fill = ($hpRow % 2 === 0) ? 'EFF6FF' : 'FFFFFF';
            $sheet->getStyle("E{$hpRow}:J{$hpRow}")->applyFromArray([
                'font'    => ['name' => 'Segoe UI', 'size' => 9],
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fill]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BFDBFE']]]
            ]);
            $sheet->getStyle("E{$hpRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getRowDimension((string)$hpRow)->setRowHeight(20);
            $hpRow++;
        }
        if ($virtualExpenses->isEmpty()) {
            $sheet->mergeCells("E{$hpRow}:J{$hpRow}");
            $sheet->setCellValue("E{$hpRow}", 'Tidak ada pembelian stok HP');
            $sheet->getStyle("E{$hpRow}:J{$hpRow}")->applyFromArray([
                'font'      => ['name' => 'Segoe UI', 'size' => 9, 'italic' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);
            $hpRow++;
        }

        // Fetch all capital transactions
        $capitalsQuery = \App\Models\Capital::with('creator');
        if ($startDate) {
            $capitalsQuery->whereDate('entry_date', '>=', $startDate);
        }
        if ($endDate) {
            $capitalsQuery->whereDate('entry_date', '<=', $endDate);
        }
        $capitalsList = $capitalsQuery->latest('entry_date')->get();

        // Calculate starting row for Capital table (leave a 3-row gap below the longest table)
        $capitalStartRow = max($piutangRow, $hpRow) + 3;

        $sheet->mergeCells("A{$capitalStartRow}:F{$capitalStartRow}");
        $sheet->setCellValue("A{$capitalStartRow}", 'RINCIAN TRANSAKSI MODAL');
        $sheet->getStyle("A{$capitalStartRow}")->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '5B21B6']], // Dark Purple
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension((string)$capitalStartRow)->setRowHeight(28);

        $capitalHeaderRow = $capitalStartRow + 1;
        $sheet->fromArray(['Tanggal', 'Keterangan', 'Tipe', 'Metode', 'Jumlah', 'Dicatat Oleh'], null, "A{$capitalHeaderRow}");
        $sheet->getStyle("A{$capitalHeaderRow}:F{$capitalHeaderRow}")->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9, 'color' => ['rgb' => '5B21B6']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F3FF']], // Purple 100
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDD6FE']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
        $sheet->getRowDimension((string)$capitalHeaderRow)->setRowHeight(22);

        $capRow = $capitalHeaderRow + 1;
        foreach ($capitalsList as $c) {
            $sheet->setCellValue("A{$capRow}", $c->entry_date ? $c->entry_date->format('d/m/Y') : $c->created_at->format('d/m/Y'));
            $sheet->setCellValue("B{$capRow}", $c->description);
            
            $typeLabel = match($c->type->value ?? $c->type) {
                'initial' => 'Modal Awal',
                'addition' => 'Modal Tambahan',
                'withdrawal' => 'Penarikan',
                default => ucfirst($c->type->value ?? $c->type)
            };
            $sheet->setCellValue("C{$capRow}", $typeLabel);
            $sheet->setCellValue("D{$capRow}", strtoupper($c->payment_method ?? 'cash'));
            $sheet->setCellValue("E{$capRow}", $c->amount);
            $sheet->setCellValue("F{$capRow}", $c->creator->name ?? '—');

            $fill = ($capRow % 2 === 0) ? 'FDFDFD' : 'FFFFFF'; // Soft stripe
            $sheet->getStyle("A{$capRow}:F{$capRow}")->applyFromArray([
                'font' => ['name' => 'Segoe UI', 'size' => 9],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fill]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDD6FE']]]
            ]);
            $sheet->getStyle("E{$capRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("A{$capRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$capRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$capRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$capRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getRowDimension((string)$capRow)->setRowHeight(20);
            
            $capRow++;
        }
        if ($capitalsList->isEmpty()) {
            $sheet->mergeCells("A{$capRow}:F{$capRow}");
            $sheet->setCellValue("A{$capRow}", "Tidak ada rincian transaksi modal");
            $sheet->getStyle("A{$capRow}:F{$capRow}")->applyFromArray([
                'font' => ['name' => 'Segoe UI', 'size' => 9, 'italic' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);
            $sheet->getRowDimension((string)$capRow)->setRowHeight(20);
            $capRow++;
        }

        // 7. AUTO-FIT COLUMNS AND FORMAT ROWS
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getColumnDimension('D')->setAutoSize(false)->setWidth(2.5);
        
        $sheet->getRowDimension('1')->setRowHeight(20);
        $sheet->getRowDimension('2')->setRowHeight(20);
        $sheet->getRowDimension('3')->setRowHeight(20);
        $sheet->getRowDimension('4')->setRowHeight(24);
        $sheet->getRowDimension('6')->setRowHeight(28);
        
        $sheet->getRowDimension('16')->setRowHeight(24);
        $sheet->getRowDimension('17')->setRowHeight(22);
        for ($r = 18; $r <= 31; $r++) {
            $sheet->getRowDimension((string)$r)->setRowHeight(20);
        }
        $sheet->getRowDimension('35')->setRowHeight(28);
        $sheet->getRowDimension('36')->setRowHeight(22);
    }

    private function buildExpensesSheet($sheet): void
    {
        $sheet->setTitle('Pengeluaran');
        $sheet->fromArray(
            ['Tanggal', 'Keterangan', 'Kategori', 'Jumlah', 'Catatan', 'Dicatat oleh'],
            null, 'A1'
        );
        $summary  = $this->finance->summary();
        $expenses = $summary['expenses'];
        $row      = 2;
        foreach ($expenses as $e) {
            $sheet->fromArray([
                $e->expense_date->format('d/m/Y'),
                $e->description,
                $e->category,
                $e->amount,
                $e->notes,
                $e->creator->name ?? '—',
            ], null, "A{$row}");
            $row++;
        }
        $sheet->setCellValue("B{$row}", 'TOTAL');
        $sheet->setCellValue("D{$row}", $expenses->sum('amount'));
    }
}
