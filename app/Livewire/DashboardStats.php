<?php

namespace App\Livewire;

use App\Models\Debt;
use App\Models\Unit;
use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Repositories\Contracts\UnitRepositoryInterface;
use Livewire\Component;

class DashboardStats extends Component
{
    public function render(SaleRepositoryInterface $sales, UnitRepositoryInterface $units)
    {
        $stockCounts     = $units->countByStatus();
        $todayStats      = $sales->todayStats();
        $weeklyRevenue   = $sales->weeklyRevenue();
        $latestUnits     = $units->latestReady(5);
        $pendingDebts    = Debt::where('status', '!=', 'paid')->sum('amount');

        return view('livewire.dashboard-stats', compact(
            'stockCounts', 'todayStats', 'weeklyRevenue', 'latestUnits', 'pendingDebts'
        ));
    }
}
