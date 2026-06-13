<?php
namespace App\Services;

use App\Repositories\Contracts\CapitalRepositoryInterface;
use App\Repositories\Contracts\DebtRepositoryInterface;
use App\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Repositories\Contracts\UnitRepositoryInterface;
use Illuminate\Support\Collection;

class FinanceService
{
    public function __construct(
        private readonly SaleRepositoryInterface    $sales,
        private readonly UnitRepositoryInterface    $units,
        private readonly CapitalRepositoryInterface $capitals,
        private readonly DebtRepositoryInterface    $debts,
        private readonly ExpenseRepositoryInterface $expenses,
    ) {}

    /** Current liquid cash — used for purchase validation in controllers. */
    public static function kasLiquidNow(): float
    {
        $modalAwal  = (float)\App\Models\Capital::whereIn('type', ['initial', 'addition'])->sum('amount');
        $withdrawal = (float)\App\Models\Capital::where('type', 'withdrawal')->sum('amount');
        $revenue    = (float)\App\Models\Sale::where('status', 'approved')->sum('total_price');
        $hpCost     = (float)\App\Models\Unit::sum('purchase_price');
        $accStock   = (float)\App\Models\Accessory::selectRaw('COALESCE(SUM(purchase_price * stock_qty),0) as v')->value('v');
        $accSold    = (float)\App\Models\SaleItem::whereNotNull('accessory_id')
                         ->selectRaw('COALESCE(SUM(purchase_price * quantity),0) as v')->value('v');
        $expenses   = (float)\App\Models\Expense::sum('amount');

        return $modalAwal - $withdrawal + $revenue - $hpCost - $accStock - $accSold - $expenses;
    }

    /** Aggregate data for the Finance index page. */
    public function summary(): array
    {
        $allExpenses = $this->expenses->allOrdered();
        $totalExp    = $allExpenses->sum('amount');
        $totalRev    = $this->sales->totalRevenue();
        $totalProfit = $this->sales->totalProfit();

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

        $expensesQuery = \App\Models\Expense::query();
        if ($startDate) {
            $expensesQuery->whereDate('expense_date', '>=', $startDate);
        }
        if ($endDate) {
            $expensesQuery->whereDate('expense_date', '<=', $endDate);
        }
        $expenses = (float) $expensesQuery->sum('amount');

        $capitalsQuery = \App\Models\Capital::whereIn('type', ['initial', 'addition']);
        if ($startDate) {
            $capitalsQuery->whereDate('entry_date', '>=', $startDate);
        }
        if ($endDate) {
            $capitalsQuery->whereDate('entry_date', '<=', $endDate);
        }
        $capital = (float) $capitalsQuery->sum('amount');

        // Modal Awal and Modal Sekarang (Lifetime)
        $modalAwal = (float) \App\Models\Capital::whereIn('type', ['initial', 'addition'])->sum('amount')
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
            'capital'      => $capital,
            'expenses'     => $expenses,
            'net'          => $profit - $expenses,
            'unpaidDebts'  => $this->debts->unpaidSum(),
            'assetValue'   => $this->units->assetValue(),
            'modalAwal'    => $modalAwal,
            'modalSekarang'=> $modalSekarang,
        ];
    }

    /** Compile statistics for the unified reports hub page. */
    public function reportSummary(?string $startDate = null, ?string $endDate = null): array
    {
        $today = $this->sales->todayStats();
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
        if ($startDate) {
            $expensesQuery->whereDate('expense_date', '>=', $startDate);
        }
        if ($endDate) {
            $expensesQuery->whereDate('expense_date', '<=', $endDate);
        }
        
        $totalExpenses = (float) $expensesQuery->sum('amount');
        $expenses = $expensesQuery->latest('expense_date')->paginate(10, ['*'], 'page_expense')->appends(request()->query());

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
        $capitalsList = $capitalsQuery->latest('entry_date')->paginate(10, ['*'], 'page_capital')->appends(request()->query());

        // Modal Awal and Modal Sekarang (Lifetime basis to remain mathematically accurate liquid Cash)
        $modalAwal        = $this->capitals->sumInitialAndAddition();
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
        $modalSekarang           = $modalAwal - $totalWithdrawal + $lifetimeRevenue - $totalHPPurchases - $totalAccessoryPurchases - $lifetimeExpenses;

        $saldoAtm = (float) \App\Models\SalePayment::where('method', 'transfer')
            ->whereHas('sale', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'approved');
                if ($startDate) $q->whereDate('sale_date', '>=', $startDate);
                if ($endDate)   $q->whereDate('sale_date', '<=', $endDate);
            })->sum('amount');

        // Lifetime totals for the Aliran Modal diagram (always all-time, not period-filtered)
        $saldoKas = (float) \App\Models\SalePayment::where('method', 'cash')
            ->whereHas('sale', fn($q) => $q->where('status', 'approved'))
            ->sum('amount');
        $saldoAtmLifetime = (float) \App\Models\SalePayment::where('method', 'transfer')
            ->whereHas('sale', fn($q) => $q->where('status', 'approved'))
            ->sum('amount');

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
        ];
    }
}

