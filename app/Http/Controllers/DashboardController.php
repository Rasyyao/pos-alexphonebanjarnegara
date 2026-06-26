<?php
namespace App\Http\Controllers;

use App\Repositories\Contracts\DebtRepositoryInterface;
use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Repositories\Contracts\UnitRepositoryInterface;
use App\Repositories\Contracts\AccessoryRepositoryInterface;
use App\Repositories\Contracts\ExpenseRepositoryInterface;

class DashboardController extends Controller
{
    public function __construct(
        private readonly SaleRepositoryInterface $sales,
        private readonly UnitRepositoryInterface $units,
        private readonly DebtRepositoryInterface $debts,
        private readonly AccessoryRepositoryInterface $accessories,
        private readonly ExpenseRepositoryInterface $expenses,
    ) {}

    public function index()
    {
        $stockCounts      = $this->units->countByStatus();
        $todayStats       = $this->sales->todayStats();
        $weekStats        = $this->sales->weekStats();
        $monthStats       = $this->sales->monthStats();
        $weeklyRevenue    = $this->sales->weeklyRevenue();
        $paymentBreakdown = $this->sales->paymentBreakdownToday();
        $latestUnits      = $this->units->latestReady(5);
        $readyUnits       = $this->units->latestReady(5);
        $recentSales      = $this->sales->latestSales(5);
        $pendingDebts     = $this->debts->unpaidSum();
        $assetValue       = $this->units->assetValue();
        $totalRevenue     = $this->sales->totalRevenue();
        $totalProfit      = $this->sales->totalProfit();
        $totalAccessories = $this->accessories->totalStockQty();
        
        $todayExpensesQuery = \App\Models\Expense::whereDate('expense_date', today());
        if (auth()->user()?->isSuperAdmin()) {
            $todayExpensesQuery->where('category', '!=', 'tarik_owner');
        } else {
            $todayExpensesQuery->whereNotIn('category', ['tarik_owner', 'gaji']);
        }
        $todayExpenses = (float) $todayExpensesQuery->sum('amount');
        $todayNetProfit = ($todayStats['profit'] ?? 0) - $todayExpenses;
        
        $brandDist        = $this->units->brandDistribution();
        $typeDist         = $this->units->typeDistribution();
        $statusDist       = $this->units->statusDistribution();

        // Monthly trends for last 6 months
        $monthlyProfit    = $this->sales->monthlyProfit(6);
        $monthlyExpenses  = $this->expenses->monthlyExpensesExcludingOwner(6);

        $monthlyLabels    = $monthlyProfit->pluck('label')->toArray();
        $monthlyProfits   = $monthlyProfit->pluck('profit')->toArray();
        $monthlyExpData   = $monthlyExpenses->pluck('total')->toArray();

        $monthlyNetProfits = [];
        foreach ($monthlyProfits as $index => $profitVal) {
            $expVal = $monthlyExpData[$index] ?? 0;
            $monthlyNetProfits[] = $profitVal - $expVal;
        }

        return view('dashboard.index', compact(
            'stockCounts', 'todayStats', 'weekStats', 'monthStats', 'weeklyRevenue',
            'paymentBreakdown', 'latestUnits', 'readyUnits', 'recentSales', 'pendingDebts', 'assetValue',
            'totalRevenue', 'totalProfit', 'todayNetProfit', 'totalAccessories', 'brandDist', 'typeDist', 'statusDist',
            'monthlyLabels', 'monthlyNetProfits', 'monthlyExpData'
        ));
    }
}
