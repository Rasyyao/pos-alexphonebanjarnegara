<?php
namespace App\Services;

use App\Repositories\Contracts\CapitalRepositoryInterface;
use App\Repositories\Contracts\DebtRepositoryInterface;
use App\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Repositories\Contracts\FundTransferRepositoryInterface;
use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Repositories\Contracts\UnitRepositoryInterface;
use Illuminate\Support\Collection;

class FinanceService
{
    public function __construct(
        private readonly SaleRepositoryInterface        $sales,
        private readonly UnitRepositoryInterface        $units,
        private readonly CapitalRepositoryInterface     $capitals,
        private readonly DebtRepositoryInterface        $debts,
        private readonly ExpenseRepositoryInterface     $expenses,
        private readonly FundTransferRepositoryInterface $fundTransfers,
    ) {}

    /** Current liquid cash — used for purchase validation in controllers. */
    public static function kasLiquidNow(): float
    {
        $modalAwal  = (float)\App\Models\Capital::whereIn('type', ['initial', 'addition'])->whereNull('sale_id')->sum('amount');
        $withdrawal = (float)\App\Models\Capital::where('type', 'withdrawal')->sum('amount');
        $revenue    = (float)\App\Models\Sale::where('status', 'approved')->sum('total_price');
        $hpCost     = (float)\App\Models\Unit::sum('purchase_price');
        $accStock   = (float)\App\Models\Accessory::selectRaw('COALESCE(SUM(purchase_price * stock_qty),0) as v')->value('v');
        $accSold    = (float)\App\Models\SaleItem::whereNotNull('accessory_id')
                         ->selectRaw('COALESCE(SUM(purchase_price * quantity),0) as v')->value('v');
        $expenses   = (float)\App\Models\Expense::sum('amount');

        return $modalAwal - $withdrawal + $revenue - $hpCost - $accStock - $accSold - $expenses;
    }

    /** Saldo Kas & ATM split — lightweight version for the Mutasi Dana page. */
    public function saldoSplit(): array
    {
        $modalCash     = (float)\App\Models\Capital::whereIn('type', ['initial','addition'])->where('payment_method','cash')->whereNull('sale_id')->sum('amount');
        $modalTransfer = (float)\App\Models\Capital::whereIn('type', ['initial','addition'])->where('payment_method','transfer')->whereNull('sale_id')->sum('amount');
        $withdrawal    = (float)\App\Models\Capital::where('type', 'withdrawal')->sum('amount');

        $revCash     = (float)\App\Models\SalePayment::where('method','cash')->whereHas('sale', fn($q) => $q->where('status','approved'))->sum('amount');
        $revTransfer = (float)\App\Models\SalePayment::where('method','transfer')->whereHas('sale', fn($q) => $q->where('status','approved'))->sum('amount');

        $hpCash     = (float)\App\Models\Unit::sum('purchase_cash');
        $hpTransfer = (float)\App\Models\Unit::sum('purchase_transfer');

        $accAssetCash     = (float)\App\Models\Accessory::selectRaw('COALESCE(SUM(purchase_cash * stock_qty),0) as v')->value('v');
        $accAssetTransfer = (float)\App\Models\Accessory::selectRaw('COALESCE(SUM(purchase_transfer * stock_qty),0) as v')->value('v');
        $accSoldCash      = (float)\App\Models\SaleItem::whereNotNull('accessory_id')
                               ->join('accessories','sale_items.accessory_id','=','accessories.id')
                               ->selectRaw('COALESCE(SUM(accessories.purchase_cash * sale_items.quantity),0) as total')->value('total');
        $accSoldTransfer  = (float)\App\Models\SaleItem::whereNotNull('accessory_id')
                               ->join('accessories','sale_items.accessory_id','=','accessories.id')
                               ->selectRaw('COALESCE(SUM(accessories.purchase_transfer * sale_items.quantity),0) as total')->value('total');

        $expCash     = (float)\App\Models\Expense::where('payment_method','cash')->sum('amount');
        $expTransfer = (float)\App\Models\Expense::where('payment_method','transfer')->sum('amount');

        $cashToAtm = $this->fundTransfers->sumCashToAtm();
        $atmToCash = $this->fundTransfers->sumAtmToCash();

        return [
            'saldoKas' => $modalCash - $withdrawal + $revCash - $hpCash - $accAssetCash - $accSoldCash - $expCash
                        + $atmToCash - $cashToAtm,
            'saldoAtm' => $modalTransfer + $revTransfer - $hpTransfer - $accAssetTransfer - $accSoldTransfer - $expTransfer
                        + $cashToAtm - $atmToCash,
        ];
    }

    /** Aggregate data for the Finance index page. */
    public function summary(): array
    {
        $allExpenses = $this->expenses->allOrdered();
        $totalExp    = $allExpenses->sum('amount');
        $totalRev    = $this->sales->totalRevenue();
        $totalProfit = $this->sales->totalProfit();

        // Saldo split calculations (lifetime)
        $modalCash     = (float)\App\Models\Capital::whereIn('type', ['initial','addition'])->where('payment_method','cash')->whereNull('sale_id')->sum('amount');
        $modalTransfer = (float)\App\Models\Capital::whereIn('type', ['initial','addition'])->where('payment_method','transfer')->whereNull('sale_id')->sum('amount');
        $totalWithdrawal = (float)\App\Models\Capital::where('type', 'withdrawal')->sum('amount');
        $revenueCash     = (float)\App\Models\SalePayment::where('method','cash')->whereHas('sale', fn($q) => $q->where('status','approved'))->sum('amount');
        $revenueTransfer = (float)\App\Models\SalePayment::where('method','transfer')->whereHas('sale', fn($q) => $q->where('status','approved'))->sum('amount');
        $hpCash     = (float)\App\Models\Unit::sum('purchase_cash');
        $hpTransfer = (float)\App\Models\Unit::sum('purchase_transfer');
        $accAssetCash = (float)\App\Models\Accessory::selectRaw('COALESCE(SUM(purchase_cash * stock_qty),0) as v')->value('v');
        $accSoldCash  = (float)\App\Models\SaleItem::whereNotNull('accessory_id')
                            ->join('accessories', 'sale_items.accessory_id', '=', 'accessories.id')
                            ->selectRaw('COALESCE(SUM(accessories.purchase_cash * sale_items.quantity),0) as total')
                            ->value('total');
        $accAssetTransfer = (float)\App\Models\Accessory::selectRaw('COALESCE(SUM(purchase_transfer * stock_qty),0) as v')->value('v');
        $accSoldTransfer  = (float)\App\Models\SaleItem::whereNotNull('accessory_id')
                            ->join('accessories', 'sale_items.accessory_id', '=', 'accessories.id')
                            ->selectRaw('COALESCE(SUM(accessories.purchase_transfer * sale_items.quantity),0) as total')
                            ->value('total');
        $expCash     = (float)\App\Models\Expense::where('payment_method','cash')->sum('amount');
        $expTransfer = (float)\App\Models\Expense::where('payment_method','transfer')->sum('amount');

        $transferCashToAtm = $this->fundTransfers->sumCashToAtm();
        $transferAtmToCash = $this->fundTransfers->sumAtmToCash();

        $saldoKas         = $modalCash - $totalWithdrawal + $revenueCash - $hpCash - $accAssetCash - $accSoldCash - $expCash
                          + $transferAtmToCash - $transferCashToAtm;
        $saldoAtmLifetime = $modalTransfer + $revenueTransfer - $hpTransfer - $accAssetTransfer - $accSoldTransfer - $expTransfer
                          + $transferCashToAtm - $transferAtmToCash;

        return [
            'totalRevenue'        => $totalRev,
            'totalProfit'         => $totalProfit,
            'totalCapital'        => $this->capitals->sumTotal(),
            'pendingDebts'        => $this->debts->unpaidSum(),
            'assetValue'          => $this->units->assetValue(),
            'capitals'            => $this->capitals->paginate(),
            'expenses'            => $this->expenses->paginate(),
            'totalExpenses'       => $totalExp,
            'expensesByCategory'  => $allExpenses->groupBy('category')->map->sum('amount'),
            'netCashFlow'         => $totalRev - $totalExp,
            'fundTransfers'       => $this->fundTransfers->paginate(10),
            'saldoKas'            => $saldoKas,
            'saldoAtmLifetime'    => $saldoAtmLifetime,
            'totalCashToAtm'      => $transferCashToAtm,
            'totalAtmToCash'      => $transferAtmToCash,
        ];
    }

    /** Aggregate data for the daily finance report. */
    public function dailyReport(string $date): array
    {
        $sales = $this->sales->approvedForDate($date);
        return [
            'sales'          => $sales,
            'date'           => $date,
            'total_revenue'  => $sales->sum('total_price'),
            'total_profit'   => $sales->sum('profit'),
        ];
    }

    /** Summary figures for the Excel finance sheet. */
    public function financeSummaryForExport(?string $startDate = null, ?string $endDate = null): array
    {
        $salesQuery = \App\Models\Sale::where('status', 'approved');
        if ($startDate) {
            $salesQuery->whereDate('sale_date', '>=', $startDate);
        }
        if ($endDate) {
            $salesQuery->whereDate('sale_date', '<=', $endDate);
        }
        $revenue = (float) $salesQuery->sum('total_price');
        $profit  = (float) $salesQuery->sum('profit');

        // Pendapatan = cash/transfer actually received: initial payments for sales in period + debt payments received in period
        $incomeInitial = (float) \App\Models\SalePayment::whereIn('method', ['cash', 'transfer'])
            ->whereHas('sale', function ($sq) use ($startDate, $endDate) {
                $sq->where('status', 'approved');
                if ($startDate) $sq->whereDate('sale_date', '>=', $startDate);
                if ($endDate)   $sq->whereDate('sale_date', '<=', $endDate);
            })->sum('amount');
        $incomeDebtQuery = \App\Models\SalePayment::whereIn('method', ['cash', 'transfer'])
            ->whereNotNull('created_at')
            ->whereHas('sale', fn($q) => $q->where('status', 'approved'))
            ->whereRaw('DATE(sale_payments.created_at) > (SELECT sale_date FROM sales WHERE sales.id = sale_payments.sale_id)');
        if ($startDate) $incomeDebtQuery->whereDate('created_at', '>=', $startDate);
        if ($endDate)   $incomeDebtQuery->whereDate('created_at', '<=', $endDate);
        $income = $incomeInitial + (float) $incomeDebtQuery->sum('amount');

        $isSuperAdmin = auth()->user()?->isSuperAdmin() ?? false;
        $expensesQuery = \App\Models\Expense::query();
        if (!$isSuperAdmin) {
            $expensesQuery->where('category', '!=', 'tarik_owner');
        }
        if ($startDate) {
            $expensesQuery->whereDate('expense_date', '>=', $startDate);
        }
        if ($endDate) {
            $expensesQuery->whereDate('expense_date', '<=', $endDate);
        }
        $operationalExpenses = (float) $expensesQuery->sum('amount');

        $hpExpensesQuery = \App\Models\Unit::query();
        if ($startDate) {
            $hpExpensesQuery->whereDate('purchase_date', '>=', $startDate);
        }
        if ($endDate) {
            $hpExpensesQuery->whereDate('purchase_date', '<=', $endDate);
        }
        $hpPurchaseTotal = (float) $hpExpensesQuery->sum('purchase_price');
        // Total pengeluaran untuk display (kas keluar); HP cost sudah masuk COGS via profit
        $expenses = $operationalExpenses + $hpPurchaseTotal;

        $capitalsQuery = \App\Models\Capital::whereIn('type', ['initial', 'addition']);
        if ($startDate) {
            $capitalsQuery->whereDate('entry_date', '>=', $startDate);
        }
        if ($endDate) {
            $capitalsQuery->whereDate('entry_date', '<=', $endDate);
        }
        $capital = (float) $capitalsQuery->sum('amount');

        // Modal Awal and Modal Sekarang (Lifetime)
        $modalAwal = (float) \App\Models\Capital::whereIn('type', ['initial', 'addition'])->whereNull('sale_id')->sum('amount')
                  - (float) \App\Models\Capital::where('type', 'withdrawal')->sum('amount');
        $totalHPPurchases        = $this->units->totalPurchaseValue();
        $lifetimeRevenue         = (float) \App\Models\Sale::where('status', 'approved')->sum('total_price');
        $lifetimeExpenses        = (float) \App\Models\Expense::sum('amount');
        $accStock                = (float) \App\Models\Accessory::selectRaw('COALESCE(SUM(purchase_price * stock_qty),0) as v')->value('v');
        $accSold                 = (float) \App\Models\SaleItem::whereNotNull('accessory_id')->selectRaw('COALESCE(SUM(purchase_price * quantity),0) as v')->value('v');
        $totalAccessoryPurchases = $accStock + $accSold;
        $modalSekarang           = $modalAwal + $lifetimeRevenue - $totalHPPurchases - $totalAccessoryPurchases - $lifetimeExpenses;

        return [
            'revenue'      => $revenue,
            'profit'       => $profit,
            'income'       => $income,
            'capital'      => $capital,
            'expenses'     => $expenses,
            'net'          => $profit - $operationalExpenses,
            'unpaidDebts'  => $this->debts->unpaidSum(),
            'assetValue'   => $this->units->assetValue(),
            'modalAwal'    => $modalAwal,
            'modalSekarang'=> $modalSekarang,
        ];
    }

    /** Compile statistics for the unified reports hub page. */
    public function reportSummary(?string $startDate = null, ?string $endDate = null): array
    {
        $todayStats = $this->sales->todayStats();
        
        $user = auth()->user();
        $isSuperAdmin = $user && $user->isSuperAdmin();

        $todayExpensesQuery = \App\Models\Expense::whereDate('expense_date', today());
        if (!$isSuperAdmin) {
            $todayExpensesQuery->where('category', '!=', 'tarik_owner');
        }
        $todayExpenses = (float) $todayExpensesQuery->sum('amount');

        $todayHPStock = (float) \App\Models\Unit::whereDate('purchase_date', today())->sum('purchase_price');
        $todayExpenses += $todayHPStock;

        $todayIncome   = (float) \App\Models\SalePayment::whereIn('method', ['cash', 'transfer'])
            ->where(function ($q) {
                // Initial payments for today's sales
                $q->whereHas('sale', fn($sq) => $sq->approved()->whereDate('sale_date', today()))
                  // OR debt payments received today for older sales (identified by created_at)
                  ->orWhere(function ($sq) {
                      $sq->whereNotNull('created_at')
                         ->whereDate('created_at', today())
                         ->whereHas('sale', fn($ssq) => $ssq->approved()->whereDate('sale_date', '<', today()));
                  });
            })
            ->sum('amount');
        $todayDebt     = (float) \App\Models\Debt::whereHas('sale', fn($q) => $q->approved()->whereDate('sale_date', today()))
            ->sum('amount');

        $today = [
            'revenue'    => $todayStats['revenue'],
            'profit'     => $todayStats['profit'],
            'count'      => $todayStats['count'],
            'expenses'   => $todayExpenses,
            'income'     => $todayIncome,
            'debt'       => $todayDebt,
            'net_profit' => $todayStats['profit'] - $todayExpenses,
        ];
        $week  = $this->sales->weekStats();
        $month = $this->sales->monthStats();

        // 1. Calculate sales revenue & profit for the filtered date range
        $salesQuery = \App\Models\Sale::where('status', 'approved');
        if ($startDate) {
            $salesQuery->whereDate('sale_date', '>=', $startDate);
        }
        if ($endDate) {
            $salesQuery->whereDate('sale_date', '<=', $endDate);
        }
        
        $totalRevenue = (float) $salesQuery->sum('total_price');
        $totalProfit  = (float) $salesQuery->sum('profit');

        // 2. Calculate expenses for the filtered date range
        $expensesQuery = \App\Models\Expense::with('creator');
        if (!$isSuperAdmin) {
            $expensesQuery->where('category', '!=', 'tarik_owner');
        }
        if ($startDate) {
            $expensesQuery->whereDate('expense_date', '>=', $startDate);
        }
        if ($endDate) {
            $expensesQuery->whereDate('expense_date', '<=', $endDate);
        }
        
        $totalExpenses = (float) $expensesQuery->sum('amount');

        $hpExpensesQuery = \App\Models\Unit::query();
        if ($startDate) {
            $hpExpensesQuery->whereDate('purchase_date', '>=', $startDate);
        }
        if ($endDate) {
            $hpExpensesQuery->whereDate('purchase_date', '<=', $endDate);
        }
        $totalHPExpenses = (float) $hpExpensesQuery->sum('purchase_price');
        $totalExpenses += $totalHPExpenses;

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

        // Fetch approved sales in the filtered range
        $salesListQuery = \App\Models\Sale::with(['creator', 'items.unit.model.brand', 'items.accessory', 'payments']);
        if ($startDate) {
            $salesListQuery->whereDate('sale_date', '>=', $startDate);
        }
        if ($endDate) {
            $salesListQuery->whereDate('sale_date', '<=', $endDate);
        }
        $salesList = $salesListQuery->where('status', 'approved')->get();

        $virtualSales = $salesList->map(function ($sale) {
            $itemNames = [];
            foreach ($sale->items as $item) {
                if ($item->unit) {
                    $brand = $item->unit->model->brand->name ?? '';
                    $model = $item->unit->model->name ?? '';
                    $itemNames[] = "{$brand} {$model} ({$item->unit->ram}/{$item->unit->rom})";
                } elseif ($item->accessory) {
                    $itemNames[] = "{$item->accessory->name} (x{$item->quantity})";
                }
            }
            $itemsSummary = count($itemNames) > 0 ? implode(', ', $itemNames) : 'Produk';
            $invoice = $sale->invoice_number;
            
            $exp = new \App\Models\Expense();
            $exp->id = null;
            $exp->is_virtual = true;
            $exp->is_virtual_sale = true;
            $exp->sale_id = $sale->id;
            $exp->expense_date = $sale->sale_date;
            $exp->description = "Penjualan {$invoice}: {$itemsSummary}";
            $exp->category = "penjualan";
            
            // Map payment methods to string format
            $methods = $sale->payments->pluck('method')->map(function ($m) {
                return $m instanceof \BackedEnum ? $m->value : $m;
            })->unique();
            
            if ($methods->count() === 1) {
                $exp->payment_method = $methods->first();
            } else {
                $exp->payment_method = 'split';
            }
            
            // Detail payment methods in notes
            $paymentDetails = $sale->payments->map(function ($p) {
                $methodStr = $p->method instanceof \BackedEnum ? $p->method->value : $p->method;
                $methodLabel = $methodStr === 'cash' ? 'Tunai' : ($methodStr === 'transfer' ? 'Transfer' : 'Utang');
                return "{$methodLabel}: Rp " . number_format($p->amount, 0, ',', '.');
            })->join(', ');
            
            $exp->notes = "Metode: {$paymentDetails}" . ($sale->description ? " | " . $sale->description : "");
            $exp->amount = $sale->total_price;
            $exp->created_by = $sale->created_by;
            $exp->setRelation('creator', $sale->creator);
            return $exp;
        });

        // Debt payments received in the filtered period for sales made before that period
        $debtPaymentsQuery = \App\Models\SalePayment::with(['sale.creator'])
            ->whereIn('method', ['cash', 'transfer'])
            ->whereNotNull('created_at')
            ->whereHas('sale', fn($q) => $q->where('status', 'approved'))
            ->whereRaw('DATE(sale_payments.created_at) > (SELECT sale_date FROM sales WHERE sales.id = sale_payments.sale_id)');
        if ($startDate) $debtPaymentsQuery->whereDate('created_at', '>=', $startDate);
        if ($endDate)   $debtPaymentsQuery->whereDate('created_at', '<=', $endDate);

        $virtualDebtPayments = $debtPaymentsQuery->get()->map(function ($payment) {
            $sale      = $payment->sale;
            $invoice   = $sale->invoice_number ?? '—';
            $methodStr = $payment->method instanceof \BackedEnum ? $payment->method->value : $payment->method;
            $methodLabel = $methodStr === 'cash' ? 'Tunai' : 'Transfer';

            $exp = new \App\Models\Expense();
            $exp->id = null;
            $exp->is_virtual = true;
            $exp->is_virtual_debt_payment = true;
            $exp->sale_id = $sale->id;
            $exp->expense_date = \Carbon\Carbon::parse($payment->created_at);
            $exp->description = "Pelunasan Hutang {$invoice}";
            $exp->category = 'pelunasan_hutang';
            $exp->payment_method = $methodStr;
            $exp->notes = "Bayar {$methodLabel} — tgl. penjualan: {$sale->sale_date->format('d/m/Y')}";
            $exp->amount = $payment->amount;
            $exp->created_by = $sale->created_by;
            $exp->setRelation('creator', $sale->creator);
            return $exp;
        });

        $realExpensesList = $expensesQuery->get();
        $mergedCollection = $realExpensesList->concat($virtualExpenses)->concat($virtualSales)->concat($virtualDebtPayments)
            ->sortByDesc(function ($item) {
                $dateVal = $item->expense_date;
                if ($dateVal instanceof \Carbon\Carbon) {
                    return $dateVal->toDateString();
                }
                return $dateVal;
            });

        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage('page_expense') ?: 1;
        $perPage = 10;
        $currentItems = $mergedCollection->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $expenses = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $mergedCollection->count(),
            $perPage,
            $currentPage,
            [
                'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                'pageName' => 'page_expense',
            ]
        );
        $expenses->appends(request()->query());

        // 3. Calculate capitals for the filtered date range
        $capitalsQuery = \App\Models\Capital::with('creator')->whereIn('type', ['initial', 'addition', 'withdrawal']);
        if ($startDate) {
            $capitalsQuery->whereDate('entry_date', '>=', $startDate);
        }
        if ($endDate) {
            $capitalsQuery->whereDate('entry_date', '<=', $endDate);
        }
        
        $totalCapital = (float) $capitalsQuery->clone()->whereIn('type', ['initial', 'addition'])->sum('amount')
                      - (float) $capitalsQuery->clone()->where('type', 'withdrawal')->sum('amount');
        $capitalsList = $capitalsQuery->clone()->whereNull('sale_id')->latest('entry_date')->paginate(10, ['*'], 'page_capital')->appends(request()->query());

        // Modal Awal and Modal Sekarang (Lifetime basis to remain mathematically accurate liquid Cash)
        $modalAwalNonSales = (float) \App\Models\Capital::whereIn('type', ['initial', 'addition'])->whereNull('sale_id')->sum('amount');
        $modalAwal         = $this->capitals->sumInitialAndAddition(); // Total including sales
        $totalWithdrawal  = (float) \App\Models\Capital::where('type', 'withdrawal')->sum('amount');
        $totalHPPurchases = $this->units->totalPurchaseValue();
        $lifetimeRevenue  = (float) \App\Models\Sale::where('status', 'approved')->sum('total_price');
        $lifetimeExpenses = (float) \App\Models\Expense::sum('amount');
        $unpaidDebts   = $this->debts->unpaidSum();
        $activeDebts   = \App\Models\Debt::with(['sale.creator'])->where('status', '!=', 'paid')->latest()->get();
        $accAssetValue = \App\Models\Accessory::all()->sum(fn($a) => (float)$a->purchase_price * $a->stock_qty);

        // Total ever spent on accessories = current stock cost + cost of accessories already sold
        $accSoldCost             = (float)\App\Models\SaleItem::whereNotNull('accessory_id')
                                       ->selectRaw('COALESCE(SUM(purchase_price * quantity), 0) as total')
                                       ->value('total');
        $totalAccessoryPurchases = $accAssetValue + $accSoldCost;
        $modalSekarang           = $modalAwalNonSales - $totalWithdrawal + $lifetimeRevenue - $totalHPPurchases - $totalAccessoryPurchases - $lifetimeExpenses;

        $saldoAtm = (float) \App\Models\SalePayment::where('method', 'transfer')
            ->whereHas('sale', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'approved');
                if ($startDate) $q->whereDate('sale_date', '>=', $startDate);
                if ($endDate)   $q->whereDate('sale_date', '<=', $endDate);
            })->sum('amount');

        // ── Lifetime real balances split by payment method ──────────────────
        // Capital deposited: split by payment_method (exclude sale-based capitals to prevent double-counting with revenueCash/revenueTransfer)
        $modalCash     = (float)\App\Models\Capital::whereIn('type', ['initial','addition'])->where('payment_method','cash')->whereNull('sale_id')->sum('amount');
        $modalTransfer = (float)\App\Models\Capital::whereIn('type', ['initial','addition'])->where('payment_method','transfer')->whereNull('sale_id')->sum('amount');

        // Revenue received: split by SalePayment method
        $revenueCash     = (float)\App\Models\SalePayment::where('method','cash')->whereHas('sale', fn($q) => $q->where('status','approved'))->sum('amount');
        $revenueTransfer = (float)\App\Models\SalePayment::where('method','transfer')->whereHas('sale', fn($q) => $q->where('status','approved'))->sum('amount');

        // HP purchases: split by purchase_cash and purchase_transfer
        $hpCash     = (float)\App\Models\Unit::sum('purchase_cash');
        $hpTransfer = (float)\App\Models\Unit::sum('purchase_transfer');

        // Accessories purchases: split by purchase_cash and purchase_transfer
        $accAssetCash = (float)\App\Models\Accessory::selectRaw('COALESCE(SUM(purchase_cash * stock_qty),0) as v')->value('v');
        $accSoldCash  = (float)\App\Models\SaleItem::whereNotNull('accessory_id')
                            ->join('accessories', 'sale_items.accessory_id', '=', 'accessories.id')
                            ->selectRaw('COALESCE(SUM(accessories.purchase_cash * sale_items.quantity),0) as total')
                            ->value('total');
        $totalAccessoryCash = $accAssetCash + $accSoldCash;

        $accAssetTransfer = (float)\App\Models\Accessory::selectRaw('COALESCE(SUM(purchase_transfer * stock_qty),0) as v')->value('v');
        $accSoldTransfer  = (float)\App\Models\SaleItem::whereNotNull('accessory_id')
                            ->join('accessories', 'sale_items.accessory_id', '=', 'accessories.id')
                            ->selectRaw('COALESCE(SUM(accessories.purchase_transfer * sale_items.quantity),0) as total')
                            ->value('total');
        $totalAccessoryTransfer = $accAssetTransfer + $accSoldTransfer;

        // Saldo ATM  = modal via transfer + revenue via transfer − HP bought via transfer − accessories bought via transfer − expenses paid via transfer
        $lifetimeExpensesCash     = (float) \App\Models\Expense::where('payment_method', 'cash')->sum('amount');
        $lifetimeExpensesTransfer = (float) \App\Models\Expense::where('payment_method', 'transfer')->sum('amount');
        // ── Mutasi Dana (internal fund transfers between cash & ATM) ──────────
        $transferCashToAtm = $this->fundTransfers->sumCashToAtm();
        $transferAtmToCash = $this->fundTransfers->sumAtmToCash();

        // Saldo ATM  = modal via transfer + revenue via transfer − HP bought via transfer − accessories bought via transfer − expenses paid via transfer
        //              + cash_to_atm transfers − atm_to_cash transfers
        $saldoAtmLifetime = $modalTransfer + $revenueTransfer - $hpTransfer - $totalAccessoryTransfer - $lifetimeExpensesTransfer
                          + $transferCashToAtm - $transferAtmToCash;

        // Saldo Kas  = modal via cash − withdrawals + revenue via cash − HP bought via cash − accessories bought via cash − cash expenses
        //              + atm_to_cash transfers − cash_to_atm transfers
        $saldoKas = $modalCash - $totalWithdrawal + $revenueCash - $hpCash - $totalAccessoryCash - $lifetimeExpensesCash
                  + $transferAtmToCash - $transferCashToAtm;

        return [
            'today'            => $today,
            'week'             => $week,
            'month'            => $month,
            'total'            => [
                'revenue' => $totalRevenue,
                'profit'  => $totalProfit,
            ],
            'cashflow'         => [
                'inflow'      => $totalRevenue,
                'outflow'     => $totalExpenses,
                'hpPurchases' => $totalHPPurchases,
                'net'         => $totalRevenue - $totalExpenses,
            ],
            'expenses'         => $expenses,
            'capitals'         => $capitalsList,
            'totalCapital'     => $totalCapital,
            'modalAwal'        => $modalAwal,
            'modalAwalNonSales'=> $modalAwalNonSales,
            'totalHPPurchases' => $totalHPPurchases,
            'modalSekarang'    => $modalSekarang,
            'unpaidDebts'      => $unpaidDebts,
            'activeDebts'      => $activeDebts,
            'assetValue'        => $this->units->assetValue(),
            'accAssetValue'     => $accAssetValue,
            'saldoAtm'          => $saldoAtm,
            'saldoKas'          => $saldoKas,
            'saldoAtmLifetime'  => $saldoAtmLifetime,
            'lifetimeRevenue'   => $lifetimeRevenue,
            'lifetimeExpenses'  => $lifetimeExpenses,
            'lifetimeExpensesCash'     => $lifetimeExpensesCash,
            'lifetimeExpensesTransfer' => $lifetimeExpensesTransfer,
            'lifetimeProfit'           => $this->sales->totalProfit(),
            'totalWithdrawal'          => $totalWithdrawal,
        ];
    }
}

