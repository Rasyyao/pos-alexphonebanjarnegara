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
        return view('reports.finance', $this->finance->reportSummary($request->start_date, $request->end_date));
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

        $total_cash = 0;
        $total_transfer = 0;
        $total_debt = 0;

        foreach ($sales as $sale) {
            foreach ($sale->payments as $payment) {
                $method = $payment->method->value ?? $payment->method;
                if ($method === 'cash') {
                    $total_cash += $payment->amount;
                } elseif ($method === 'transfer') {
                    $total_transfer += $payment->amount;
                } elseif ($method === 'utang') {
                    $total_debt += $payment->amount;
                }
            }
        }

        return view('reports.daily', [
            'date'          => $date,
            'sales'         => $sales,
            'total_revenue' => $report['total_revenue'],
            'total_profit'  => $report['total_profit'],
            'total_cash'    => $total_cash,
            'total_transfer'=> $total_transfer,
            'total_debt'    => $total_debt,
        ]);
    }

    public function stock(Request $request)
    {
        $units       = $this->units->paginate(['status' => $request->status], 10, 'page_unit');
        $accessories = $this->accessories->paginate([], 10, 'page_accessory');
        $assetValue  = $this->units->assetValue();
        $totalStockQty = $this->accessories->totalStockQty();
        
        // Distribution stats for advanced charts
        $brandDist  = $this->units->brandDistribution();
        $typeDist   = $this->units->typeDistribution();
        $statusDist = $this->units->statusDistribution();
        
        return view('reports.stock', compact('units', 'accessories', 'assetValue', 'totalStockQty', 'brandDist', 'typeDist', 'statusDist'));
    }

    public function stockOpname()
    {
        $units       = \App\Models\Unit::with('model.brand')->orderBy('status')->orderBy('created_at')->get();
        $accessories = \App\Models\Accessory::orderBy('category')->orderBy('name')->get();

        $readyUnits = $units->filter(fn($u) => $u->status->value === 'ready');
        $assetModal = (float) $readyUnits->sum('purchase_price');
        $accModal   = (float) $accessories->sum(fn($a) => (float)$a->purchase_price * $a->stock_qty);
        $accQty     = (int) $accessories->sum('stock_qty');

        return view('reports.opname-stock', [
            'units'       => $units,
            'accessories' => $accessories,
            'readyCount'  => $readyUnits->count(),
            'soldCount'   => $units->filter(fn($u) => $u->status->value === 'sold')->count(),
            'assetModal'  => $assetModal,
            'accModal'    => $accModal,
            'accQty'      => $accQty,
            'printedAt'   => now()->isoFormat('D MMMM YYYY, HH:mm') . ' WIB',
        ]);
    }

    public function pdf(Request $request, string $type)
    {
        $pdf = match ($type) {
            'stock'   => $this->buildStockPdf(),
            'finance' => $this->buildFinancePdf($request->start_date, $request->end_date),
            'sales'   => $this->buildSalesDailyPdf($request->date ?? today()->toDateString()),
            default   => abort(404),
        };

        $filename = "laporan-{$type}-" . now()->format('Ymd-His') . ".pdf";
        return $pdf->download($filename);
    }

    private function buildStockPdf(): \Barryvdh\DomPDF\PDF
    {
        $units       = \App\Models\Unit::with('model.brand')->orderBy('status')->get();
        $accessories = \App\Models\Accessory::orderBy('category')->orderBy('name')->get();

        $readyUnits  = $units->filter(fn($u) => $u->status->value === 'ready');
        $assetModal  = (float) $readyUnits->sum('purchase_price');
        $assetJual   = 0.0;
        $accModal    = (float) $accessories->sum(fn($a) => (float)$a->purchase_price * $a->stock_qty);

        $data = [
            'units'        => $units,
            'accessories'  => $accessories,
            'readyCount'   => $readyUnits->count(),
            'soldCount'    => $units->filter(fn($u) => $u->status->value === 'sold')->count(),
            'assetModal'   => $assetModal,
            'assetJual'    => $assetJual,
            'accModal'     => $accModal,
            'accQty'       => (int) $accessories->sum('stock_qty'),
            'printedAt'    => now()->isoFormat('D MMMM YYYY, HH:mm') . ' WIB',
        ];

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf-stock', $data)
            ->setPaper('a4', 'landscape');
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

        $data = compact('summary', 'sales', 'periodStr', 'startDate', 'endDate');
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

        $totalCash = $totalTransfer = $totalDebt = 0.0;
        foreach ($sales as $s) {
            foreach ($s->payments as $p) {
                $m = $p->method->value ?? $p->method;
                if ($m === 'cash')         $totalCash     += $p->amount;
                elseif ($m === 'transfer') $totalTransfer += $p->amount;
                elseif ($m === 'utang')    $totalDebt     += $p->amount;
            }
        }

        $data = [
            'sales'         => $sales,
            'date'          => \Carbon\Carbon::parse($date)->isoFormat('D MMMM Y'),
            'totalRev'      => $totalRev,
            'totalProfit'   => $totalProfit,
            'totalCash'     => $totalCash,
            'totalTransfer' => $totalTransfer,
            'totalDebt'     => $totalDebt,
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

        $units       = \App\Models\Unit::with('model.brand')->get();
        $accessories = \App\Models\Accessory::all();

        $readyUnits  = $units->filter(fn($u) => $u->status->value === 'ready');
        $soldUnits   = $units->filter(fn($u) => $u->status->value === 'sold');
        $returUnits  = $units->reject(fn($u) => in_array($u->status->value, ['ready', 'sold']));
        $baruUnits   = $units->filter(fn($u) => $u->unit_type->value === 'baru');
        $secondUnits = $units->filter(fn($u) => $u->unit_type->value === 'second');

        $totalReady  = $readyUnits->count();
        $totalSold   = $soldUnits->count();
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
            ['Unit HP Terjual (Sold)',           $fmtN($totalSold)  . ' unit',   '475569', 'F1F5F9'],
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

        $units = \App\Models\Unit::with(['model.brand', 'creator'])->orderBy('status')->orderBy('model_id')->get();

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
        $sheet->mergeCells('A1:Q3');
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
        $sheet->mergeCells('A4:Q4');
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
        $sheet->mergeCells('F5:K5');
        $sheet->setCellValue('F5', '  ■  TERJUAL — Sudah Laku');
        $sheet->getStyle('F5')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9, 'color' => ['rgb' => '334155']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->mergeCells('L5:Q5');
        $sheet->setCellValue('L5', '  ■  RETUR / LAINNYA');
        $sheet->getStyle('L5')->applyFromArray([
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
            $sheet->mergeCells("A{$row}:Q{$row}");
            $sheet->setCellValue("A{$row}", "  ◆  BRAND: " . strtoupper($brandName) . "  (" . $brandUnits->count() . " unit)");
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font'      => ['name' => 'Segoe UI', 'bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $cBrandBg]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getRowDimension($row)->setRowHeight(26);
            $row++;

            // COLUMN HEADERS
            $headers = ['No', 'Model', 'RAM', 'ROM', 'Warna', 'Tipe', 'Grade', 'IMEI', 'No. Seri', 'Harga Modal', 'Harga Jual', 'Est. Laba', 'Margin %', 'Status', 'Tgl. Beli', 'Hari di Stok', 'Entri Oleh'];
            $sheet->fromArray($headers, null, "A{$row}");
            $sheet->getStyle("A{$row}:Q{$row}")->applyFromArray([
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
                $sheet->setCellValue("K{$row}", $jual > 0 ? $jual : null);
                $sheet->setCellValue("L{$row}", $jual > 0 ? $laba : null);
                $sheet->setCellValue("M{$row}", $jual > 0 ? $margin : null);
                $sheet->setCellValue("N{$row}", $statusLabel);
                $sheet->setCellValue("O{$row}", $u->purchase_date ? $u->purchase_date->format('d/m/Y') : '—');
                $sheet->setCellValue("P{$row}", $days . ' hari');
                $sheet->setCellValue("Q{$row}", $u->creator->name ?? '—');

                // Standard zebra row (matches finance readability)
                $sheet->getStyle("A{$row}:Q{$row}")->applyFromArray([
                    'font'    => ['name' => 'Segoe UI', 'size' => 9, 'color' => ['rgb' => '1E293B']],
                    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $rowBg]],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $cBorder]]],
                ]);
                // Status badge — only the status cell gets color, keeps rows clean
                $sheet->getStyle("N{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => $statusFg]],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $statusBadgeBg]],
                ]);
                $sheet->getStyle("J{$row}:L{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
                $sheet->getStyle("M{$row}")->getNumberFormat()->setFormatCode('0.0"%"');
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C{$row}:D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("F{$row}:G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("M{$row}:N{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("O{$row}:P{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
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
            $sheet->setCellValue("K{$row}", $brandJual > 0 ? $brandJual : null);
            $sheet->setCellValue("L{$row}", $brandJual > 0 ? $brandLaba : null);
            $sheet->getStyle("A{$row}:Q{$row}")->applyFromArray([
                'font'      => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $cSubtot]],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '0D9488']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getStyle("J{$row}:L{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
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
        $sheet->setCellValue("K{$row}", $grandTotalJual > 0 ? $grandTotalJual : null);
        $sheet->setCellValue("L{$row}", $grandTotalJual > 0 ? $grandTotalLaba : null);
        $sheet->setCellValue("N{$row}", $grandCount . ' unit');
        $sheet->getStyle("A{$row}:Q{$row}")->applyFromArray([
            'font'      => ['name' => 'Segoe UI', 'bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $cGrandT]],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '0D9488']]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("A{$row}")->getAlignment()->setIndent(1);
        $sheet->getStyle("J{$row}:L{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
        $sheet->getRowDimension($row)->setRowHeight(24);

        // ─── COLUMN WIDTHS ────────────────────────────────────────
        foreach (range('A', 'Q') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
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
        $sheet->mergeCells('A1:L3');
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
        $sheet->mergeCells('A4:L4');
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
        $sheet->mergeCells('A5:F5');
        $sheet->setCellValue('A5', '  ■  AMAN — Stok cukup (> 5 pcs)');
        $sheet->getStyle('A5')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9, 'color' => ['rgb' => '14532D']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
        ]);
        $sheet->mergeCells('G5:L5');
        $sheet->setCellValue('G5', '  ■  MENIPIS (≤ 5 pcs) — Status kolom E ditandai merah, perlu restock');
        $sheet->getStyle('G5')->applyFromArray([
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
            $sheet->mergeCells("A{$row}:L{$row}");
            $sheet->setCellValue("A{$row}", "  ◆  KATEGORI: " . strtoupper($catName) . "  (" . $catItems->count() . " jenis)");
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font'      => ['name' => 'Segoe UI', 'bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $cCatBg]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getRowDimension($row)->setRowHeight(26);
            $row++;

            // COLUMN HEADERS (12 cols: A:L)
            $headers = ['No', 'Nama Aksesoris', 'Kategori', 'Stok Qty', 'Status Stok', 'Harga Modal', 'Harga Jual', 'Laba / Pcs', 'Margin %', 'Total Modal', 'Total Jual', 'Total Laba'];
            $sheet->fromArray($headers, null, "A{$row}");
            $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
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
                $sheet->setCellValue("G{$row}", $jual);
                $sheet->setCellValue("H{$row}", $laba);
                $sheet->setCellValue("I{$row}", $margin);
                $sheet->setCellValue("J{$row}", $totModal);
                $sheet->setCellValue("K{$row}", $totJual);
                $sheet->setCellValue("L{$row}", $totLaba);

                // Standard zebra rows — clean, readable (same as finance)
                $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
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
                $sheet->getStyle("F{$row}:H{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
                $sheet->getStyle("I{$row}")->getNumberFormat()->setFormatCode('0.0"%"');
                $sheet->getStyle("J{$row}:L{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C{$row}:E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
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
            $sheet->setCellValue("J{$row}", $catModal);
            $sheet->setCellValue("K{$row}", $catJual);
            $sheet->setCellValue("L{$row}", $catLaba);
            $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
                'font'      => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $cSubtot]],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '4338CA']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getStyle("J{$row}:L{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getRowDimension($row)->setRowHeight(22);
            $row++;
            $row++; // gap
        }

        // ─── GRAND TOTAL ROW ──────────────────────────────────────
        $row++;
        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->setCellValue("A{$row}", 'GRAND TOTAL SEMUA AKSESORIS');
        $sheet->setCellValue("D{$row}", $grandQty . ' pcs');
        $sheet->setCellValue("J{$row}", $grandModal);
        $sheet->setCellValue("K{$row}", $grandJual);
        $sheet->setCellValue("L{$row}", $grandLaba);
        $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
            'font'      => ['name' => 'Segoe UI', 'bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $cGrand]],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '6D28D9']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle("A{$row}")->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'indent' => 1],
        ]);
        $sheet->getStyle("J{$row}:L{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
        $sheet->getRowDimension($row)->setRowHeight(24);

        // ─── COLUMN WIDTHS ────────────────────────────────────────
        foreach (range('A', 'L') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
        $sheet->getColumnDimension('A')->setWidth(5);
    }

    private function buildSalesSheet($sheet, string $date): void
    {
        $sheet->setTitle('Penjualan Harian');

        $report     = $this->finance->dailyReport($date);
        $sales      = $report['sales'];
        $totalRev   = (float) $report['total_revenue'];
        $totalProfit= (float) $report['total_profit'];
        $totalCash = $totalTransfer = $totalDebt = 0.0;
        foreach ($sales as $s) {
            foreach ($s->payments as $p) {
                $m = $p->method->value ?? $p->method;
                if ($m === 'cash')     $totalCash     += $p->amount;
                elseif ($m === 'transfer') $totalTransfer += $p->amount;
                elseif ($m === 'utang')    $totalDebt     += $p->amount;
            }
        }
        $txCount   = count($sales);
        $avgPerTx  = $txCount > 0 ? $totalRev / $txCount : 0;
        $dateStr   = now()->format('d F Y H:i');
        $dateLabel = \Carbon\Carbon::parse($date)->translatedFormat('d F Y');

        // 1. HEADER BANNER
        $sheet->mergeCells('A1:G3');
        $sheet->setCellValue('A1', "LAPORAN PENJUALAN HARIAN\nALEX PHONE BANJARNEGARA");
        $sheet->getStyle('A1')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
        ]);

        // 2. SUBTITLE / DATE BAR
        $sheet->mergeCells('A4:G4');
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
            ['Total Laba Kotor', $totalProfit],
            ['Penerimaan Cash', $totalCash],
            ['Penerimaan Transfer', $totalTransfer],
            ['Piutang (Hutang)', $totalDebt],
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

            // Format numbers (skip count row idx=5)
            if ($idx !== 5) {
                $sheet->getStyle("B{$mRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            }

            // Highlight total omzet row
            if ($mRow === 8) {
                $sheet->getStyle("A{$mRow}:B{$mRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '1E3A5F']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']],
                ]);
            }
            // Highlight laba kotor row
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

        // 6. TRANSACTION DETAIL TABLE HEADER (row 23)
        $sheet->mergeCells('A23:G23');
        $sheet->setCellValue('A23', 'DETAIL TRANSAKSI PENJUALAN');
        $sheet->getStyle('A23')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D4ED8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->fromArray(['Invoice', 'Tanggal', 'Kasir', 'Total Belanja', 'Laba Kotor', 'Metode Bayar', 'Status'], null, 'A24');
        $sheet->getStyle('A24:G24')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BFDBFE']]],
        ]);

        // 7. TRANSACTION ROWS
        $row = 25;
        foreach ($sales as $s) {
            $methods = $s->payments->map(fn($p) => ($p->method->value ?? $p->method) . ': ' . number_format($p->amount, 0, ',', '.'))->join(' | ');
            $sheet->setCellValue("A{$row}", $s->invoice_number);
            $sheet->setCellValue("B{$row}", $s->sale_date->format('d/m/Y'));
            $sheet->setCellValue("C{$row}", $s->creator->name ?? '—');
            $sheet->setCellValue("D{$row}", $s->total_price);
            $sheet->setCellValue("E{$row}", $s->profit);
            $sheet->setCellValue("F{$row}", $methods);
            $sheet->setCellValue("G{$row}", ucfirst($s->status->value ?? $s->status));

            $fill = ($row % 2 === 0) ? 'EFF6FF' : 'FFFFFF';
            $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
                'font' => ['name' => 'Segoe UI', 'size' => 9],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fill]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DBEAFE']]],
            ]);
            $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("D{$row}:E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
        }

        // 8. TOTALS ROW
        if ($txCount > 0) {
            $sheet->setCellValue("C{$row}", 'TOTAL');
            $sheet->setCellValue("D{$row}", $totalRev);
            $sheet->setCellValue("E{$row}", $totalProfit);
            $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
                'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9, 'color' => ['rgb' => '1E3A5F']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '93C5FD']]],
            ]);
            $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("D{$row}:E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        // 9. COLUMN WIDTHS & ROW HEIGHTS
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getRowDimension('1')->setRowHeight(20);
        $sheet->getRowDimension('2')->setRowHeight(20);
        $sheet->getRowDimension('3')->setRowHeight(20);
        $sheet->getRowDimension('4')->setRowHeight(24);
        $sheet->getRowDimension('6')->setRowHeight(28);
    }

    private function buildSalesTransactionsSheet($sheet, ?string $startDate = null, ?string $endDate = null): void
    {
        $sheet->setTitle('Transaksi Penjualan');

        $salesQuery = \App\Models\Sale::with(['creator', 'payments'])->where('status', 'approved');
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
        $sheet->mergeCells('A1:G3');
        $sheet->setCellValue('A1', "DETAIL TRANSAKSI PENJUALAN\nALEX PHONE BANJARNEGARA");
        $sheet->getStyle('A1')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
        ]);

        // 2. SUBTITLE BAR
        $sheet->mergeCells('A4:G4');
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
            ['Total Laba Kotor', $totalProfit, true],
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
            // Highlight laba kotor green
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
        $sheet->mergeCells('A23:H23');
        $sheet->setCellValue('A23', 'RINCIAN SEMUA TRANSAKSI PENJUALAN');
        $sheet->getStyle('A23')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D4ED8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->fromArray(['Invoice', 'Tanggal', 'Kasir', 'Total Belanja', 'Laba Kotor', 'Cash', 'Transfer', 'Piutang'], null, 'A24');
        $sheet->getStyle('A24:H24')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BFDBFE']]],
        ]);

        // 7. TRANSACTION ROWS
        $row = 25;
        foreach ($sales as $s) {
            $cash = $transfer = $debt = 0.0;
            foreach ($s->payments as $p) {
                $m = $p->method->value ?? $p->method;
                if ($m === 'cash')         $cash     += $p->amount;
                elseif ($m === 'transfer') $transfer += $p->amount;
                elseif ($m === 'utang')    $debt     += $p->amount;
            }
            $sheet->setCellValue("A{$row}", $s->invoice_number);
            $sheet->setCellValue("B{$row}", $s->sale_date->format('d/m/Y'));
            $sheet->setCellValue("C{$row}", $s->creator->name ?? '—');
            $sheet->setCellValue("D{$row}", $s->total_price);
            $sheet->setCellValue("E{$row}", $s->profit);
            $sheet->setCellValue("F{$row}", $cash);
            $sheet->setCellValue("G{$row}", $transfer);
            $sheet->setCellValue("H{$row}", $debt);

            $fill = ($row % 2 === 0) ? 'EFF6FF' : 'FFFFFF';
            $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
                'font' => ['name' => 'Segoe UI', 'size' => 9],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fill]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DBEAFE']]],
            ]);
            foreach (['D', 'E', 'F', 'G', 'H'] as $col) {
                $sheet->getStyle("{$col}{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
                $sheet->getStyle("{$col}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
        }

        // 8. TOTALS ROW
        if ($txCount > 0) {
            $sheet->setCellValue("C{$row}", 'TOTAL');
            $sheet->setCellValue("D{$row}", $totalRev);
            $sheet->setCellValue("E{$row}", $totalProfit);
            $sheet->setCellValue("F{$row}", $totalCash);
            $sheet->setCellValue("G{$row}", $totalTransfer);
            $sheet->setCellValue("H{$row}", $totalDebt);
            $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
                'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9, 'color' => ['rgb' => '1E3A5F']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '93C5FD']]],
            ]);
            foreach (['D', 'E', 'F', 'G', 'H'] as $col) {
                $sheet->getStyle("{$col}{$row}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
                $sheet->getStyle("{$col}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
        }

        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
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
        if ($startDate) {
            $expensesQuery->whereDate('expense_date', '>=', $startDate);
        }
        if ($endDate) {
            $expensesQuery->whereDate('expense_date', '<=', $endDate);
        }
        $expensesList = $expensesQuery->latest('expense_date')->get();

        $capitalsQuery = \App\Models\Capital::with('creator')->whereIn('type', ['initial', 'addition']);
        if ($startDate) {
            $capitalsQuery->whereDate('entry_date', '>=', $startDate);
        }
        if ($endDate) {
            $capitalsQuery->whereDate('entry_date', '<=', $endDate);
        }
        $capitalsList = $capitalsQuery->latest('entry_date')->get();

        // 1. HEADER TITLE BANNER
        $sheet->mergeCells('A1:G3');
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
        $sheet->mergeCells('A4:G4');
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

        // 2. RINGKASAN FINANSIAL HEADER
        $sheet->mergeCells('A6:B6');
        $sheet->setCellValue('A6', 'METRIK KEUANGAN');
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
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ]
        ]);
        
        $sheet->setCellValue('A7', 'Keterangan Metrik');
        $sheet->setCellValue('B7', 'Nilai / Jumlah');
        $sheet->getStyle('A7:B7')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]]
        ]);

        // 3. RINGKASAN FINANSIAL DATA
        $metrics = [
            ['Total Omzet (Pemasukan)', $data['revenue']],
            ['Total Laba Kotor', $data['profit']],
            ['Total Pengeluaran Operasional', $data['expenses']],
            ['Laba Bersih (Periode)', $data['net']],
            ['Total Setoran Modal (Periode)', $data['capital']],
            ['Utang Belum Lunas', $data['unpaidDebts']],
            ['Nilai Aset Stok HP Ready', $data['assetValue']],
            ['Modal Awal Disetor (Lifetime)', $data['modalAwal']],
            ['Modal Sekarang (Liquid Kas)', $data['modalSekarang']],
        ];

        $mRow = 8;
        foreach ($metrics as $idx => $m) {
            $sheet->setCellValue("A{$mRow}", $m[0]);
            $sheet->setCellValue("B{$mRow}", $m[1]);
            
            // Standard formatting & zebra
            $fillColor = ($mRow % 2 === 0) ? 'F8FAFC' : 'FFFFFF';
            $sheet->getStyle("A{$mRow}:B{$mRow}")->applyFromArray([
                'font' => ['name' => 'Segoe UI', 'size' => 9],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fillColor]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]]
            ]);
            
            $sheet->getStyle("B{$mRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("B{$mRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            
            // Color Net Profit beautifully (row 11 is Laba Bersih)
            if ($mRow === 11) {
                $netVal = $m[1];
                $netBg = ($netVal >= 0) ? 'D1FAE5' : 'FEE2E2'; // Light Green vs Light Red
                $netFg = ($netVal >= 0) ? '065F46' : '991B1B';
                $sheet->getStyle("A{$mRow}:B{$mRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => $netFg]],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $netBg]]
                ]);
            }
            
            $mRow++;
        }

        // 4. NATIVE EXCEL COLUMN CHART COMPARISON
        // Categories from A8 to A11 (Omzet, Laba Kotor, Pengeluaran, Laba Bersih)
        $categories = [
            new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues(
                \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues::DATASERIES_TYPE_STRING, 
                '\'Laporan Keuangan\'!$A$8:$A$11', 
                null, 
                4
            )
        ];
        // Values from B8 to B11
        $values = [
            new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues(
                \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues::DATASERIES_TYPE_NUMBER, 
                '\'Laporan Keuangan\'!$B$8:$B$11', 
                null, 
                4
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
        $legend = new \PhpOffice\PhpSpreadsheet\Chart\Legend(\PhpOffice\PhpSpreadsheet\Chart\Legend::POSITION_RIGHT, null, false);
        $chartTitle = new \PhpOffice\PhpSpreadsheet\Chart\Title('Kinerja Finansial Toko (Rp)');

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

        // Chart positioning
        $chart->setTopLeftPosition('D6');
        $chart->setBottomRightPosition('K22');
        $sheet->addChart($chart);

        // 5. DETAIL TABLES HEADERS (Row 25)
        // Expenses Header A25:F25
        $sheet->mergeCells('A25:F25');
        $sheet->setCellValue('A25', 'RINCIAN CATATAN PENGELUARAN');
        $sheet->getStyle('A25')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'BE123C']], // Soft Red
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
        
        $sheet->fromArray(['Tanggal', 'Keterangan', 'Kategori', 'Jumlah', 'Catatan', 'Dicatat Oleh'], null, 'A26');
        $sheet->getStyle('A26:F26')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFE4E6']], // Rose 100
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FDA4AF']]]
        ]);
        
        // Capital Header I25:M25
        $sheet->mergeCells('I25:M25');
        $sheet->setCellValue('I25', 'RINCIAN CATATAN SETORAN MODAL');
        $sheet->getStyle('I25')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '047857']], // Dark Emerald
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
        
        $sheet->fromArray(['Tanggal', 'Keterangan', 'Tipe', 'Jumlah', 'Dicatat Oleh'], null, 'I26');
        $sheet->getStyle('I26:M26')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'bold' => true, 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']], // Emerald 100
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'A7F3D0']]]
        ]);

        // Populate Expenses
        $expRow = 27;
        foreach ($expensesList as $e) {
            $sheet->setCellValue("A{$expRow}", $e->expense_date->format('d/m/Y'));
            $sheet->setCellValue("B{$expRow}", $e->description);
            $sheet->setCellValue("C{$expRow}", ucfirst($e->category));
            $sheet->setCellValue("D{$expRow}", $e->amount);
            $sheet->setCellValue("E{$expRow}", $e->notes ?: '—');
            $sheet->setCellValue("F{$expRow}", $e->creator->name ?? '—');
            
            $fill = ($expRow % 2 === 0) ? 'FFF1F2' : 'FFFFFF'; // Rose stripe
            $sheet->getStyle("A{$expRow}:F{$expRow}")->applyFromArray([
                'font' => ['name' => 'Segoe UI', 'size' => 9],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fill]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFE4E6']]]
            ]);
            $sheet->getStyle("D{$expRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("A{$expRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$expRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            $expRow++;
        }

        // Populate Capitals
        $capRow = 27;
        foreach ($capitalsList as $c) {
            $sheet->setCellValue("I{$capRow}", $c->entry_date->format('d/m/Y'));
            $sheet->setCellValue("J{$capRow}", $c->description);
            $sheet->setCellValue("K{$capRow}", $c->type === 'initial' ? 'Modal Awal' : 'Modal Tambahan');
            $sheet->setCellValue("L{$capRow}", $c->amount);
            $sheet->setCellValue("M{$capRow}", $c->creator->name ?? '—');
            
            $fill = ($capRow % 2 === 0) ? 'ECFDF5' : 'FFFFFF'; // Emerald stripe
            $sheet->getStyle("I{$capRow}:M{$capRow}")->applyFromArray([
                'font' => ['name' => 'Segoe UI', 'size' => 9],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fill]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1FAE5']]]
            ]);
            $sheet->getStyle("L{$capRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("I{$capRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("K{$capRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            $capRow++;
        }

        // 6. AUTO-FIT COLUMNS AND FORMAT ROWS
        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $sheet->getRowDimension('1')->setRowHeight(20);
        $sheet->getRowDimension('2')->setRowHeight(20);
        $sheet->getRowDimension('3')->setRowHeight(20);
        $sheet->getRowDimension('4')->setRowHeight(24);
        $sheet->getRowDimension('6')->setRowHeight(28);
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
