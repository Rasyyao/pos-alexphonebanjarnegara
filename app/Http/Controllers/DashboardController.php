<?php
namespace App\Http\Controllers;

use App\Repositories\Contracts\DebtRepositoryInterface;
use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Repositories\Contracts\UnitRepositoryInterface;
use App\Repositories\Contracts\AccessoryRepositoryInterface;

class DashboardController extends Controller
{
    public function __construct(
        private readonly SaleRepositoryInterface $sales,
        private readonly UnitRepositoryInterface $units,
        private readonly DebtRepositoryInterface $debts,
        private readonly AccessoryRepositoryInterface $accessories,
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
        
        $brandDist        = $this->units->brandDistribution();
        $typeDist         = $this->units->typeDistribution();
        $statusDist       = $this->units->statusDistribution();

        return view('dashboard.index', compact(
            'stockCounts', 'todayStats', 'weekStats', 'monthStats', 'weeklyRevenue',
            'paymentBreakdown', 'latestUnits', 'readyUnits', 'recentSales', 'pendingDebts', 'assetValue',
            'totalRevenue', 'totalProfit', 'totalAccessories', 'brandDist', 'typeDist', 'statusDist'
        ));
    }
}
