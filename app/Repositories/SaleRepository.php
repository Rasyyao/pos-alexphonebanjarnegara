<?php

namespace App\Repositories;

use App\Models\Sale;
use App\Models\SalePayment;
use App\Repositories\Contracts\SaleRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SaleRepository implements SaleRepositoryInterface
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $query = Sale::with(['creator', 'approver', 'items.unit.model.brand', 'items.accessory'])
            ->when($filters['status'] ?? null, fn($q, $v) => $q->where('status', $v));

        if (!empty($filters['search'])) {
            $words = array_filter(explode(' ', $filters['search']));
            foreach ($words as $word) {
                $query->where(function ($q) use ($word) {
                    $q->where('invoice_number', 'like', "%{$word}%")
                      ->orWhere('customer_name', 'like', "%{$word}%")
                      ->orWhereHas('items.unit.model', function ($sub) use ($word) {
                          $sub->where('name', 'like', "%{$word}%")
                              ->orWhereHas('brand', function ($brandQ) use ($word) {
                                  $brandQ->where('name', 'like', "%{$word}%");
                              });
                      })
                      ->orWhereHas('items.accessory', function ($sub) use ($word) {
                          $sub->where('name', 'like', "%{$word}%");
                      });
                });
            }
        }

        if (!empty($filters['period'])) {
            switch ($filters['period']) {
                case 'today':
                    $query->whereDate('sale_date', today());
                    break;
                case 'week':
                    $query->whereBetween('sale_date', [
                        now()->startOfWeek()->toDateString(),
                        now()->endOfWeek()->toDateString()
                    ]);
                    break;
                case 'month':
                    $query->whereMonth('sale_date', now()->month)
                          ->whereYear('sale_date', now()->year);
                    break;
                case 'date':
                    if (!empty($filters['date'])) {
                        $query->whereDate('sale_date', $filters['date']);
                    }
                    break;
                case 'range':
                    if (!empty($filters['start_date'])) {
                        $query->whereDate('sale_date', '>=', $filters['start_date']);
                    }
                    if (!empty($filters['end_date'])) {
                        $query->whereDate('sale_date', '<=', $filters['end_date']);
                    }
                    break;
            }
        } elseif (!empty($filters['date'])) {
            $query->whereDate('sale_date', $filters['date']);
        }

        return $query->latest()
            ->paginate(10);
    }

    public function findById(int $id): Sale
    {
        return Sale::with(['creator', 'approver', 'items.unit.model.brand', 'items.accessory', 'payments', 'debt'])->findOrFail($id);
    }

    public function create(array $data): Sale
    {
        return Sale::create($data);
    }

    public function todayStats(): array
    {
        $row = Sale::approved()->whereDate('sale_date', today())
            ->selectRaw('SUM(total_price) as revenue, SUM(profit) as profit, COUNT(*) as count')
            ->first();
        return [
            'revenue' => $row->revenue ?? 0,
            'profit'  => $row->profit ?? 0,
            'count'   => $row->count ?? 0,
        ];
    }

    public function weekStats(): array
    {
        $row = Sale::approved()
            ->whereBetween('sale_date', [now()->startOfWeek()->toDateString(), now()->toDateString()])
            ->selectRaw('SUM(total_price) as revenue, SUM(profit) as profit, COUNT(*) as count')
            ->first();
        return [
            'revenue' => $row->revenue ?? 0,
            'profit'  => $row->profit ?? 0,
            'count'   => $row->count ?? 0,
        ];
    }

    public function monthStats(): array
    {
        $row = Sale::approved()
            ->whereMonth('sale_date', now()->month)
            ->whereYear('sale_date', now()->year)
            ->selectRaw('SUM(total_price) as revenue, SUM(profit) as profit, COUNT(*) as count')
            ->first();
        return [
            'revenue' => $row->revenue ?? 0,
            'profit'  => $row->profit ?? 0,
            'count'   => $row->count ?? 0,
        ];
    }

    public function weeklyRevenue(): Collection
    {
        return Sale::approved()
            ->whereBetween('sale_date', [now()->subDays(6)->toDateString(), now()->toDateString()])
            ->selectRaw('DATE(sale_date) as date, SUM(total_price) as total, SUM(profit) as profit, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    public function paymentBreakdownToday(): Collection
    {
        return SalePayment::whereHas('sale', fn($q) =>
            $q->approved()->whereDate('sale_date', today())
        )->selectRaw('method, SUM(amount) as total')
         ->groupBy('method')
         ->get();
    }
    public function pendingList(): Collection
    {
        return Sale::with(['creator', 'items.unit.model.brand', 'items.accessory', 'payments'])
            ->where('status', 'pending')
            ->latest()
            ->get();
    }

    public function pendingPaginate(int $perPage = 10, string $pageName = 'page'): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Sale::with(['creator', 'items.unit.model.brand', 'items.accessory', 'payments'])
            ->where('status', 'pending')
            ->latest()
            ->paginate($perPage, ['*'], $pageName);
    }

    public function approvedForDate(string $date): Collection
    {
        return Sale::with(['items.unit.model.brand', 'items.accessory', 'payments', 'creator'])
            ->approved()
            ->whereDate('sale_date', $date)
            ->get();
    }

    public function totalRevenue(): float
    {
        return (float) Sale::approved()->sum('total_price');
    }

    public function totalProfit(): float
    {
        return (float) Sale::approved()->sum('profit');
    }

    public function approvedForExport(string $date): Collection
    {
        return Sale::with('payments')
            ->approved()
            ->whereDate('sale_date', $date)
            ->get();
    }

    public function latestSales(int $limit = 5): Collection
    {
        return Sale::with(['creator'])
            ->latest()
            ->take($limit)
            ->get();
    }

    public function monthlyProfit(int $months = 6): Collection
    {
        $startDate = now()->subMonths($months - 1)->startOfMonth()->toDateString();
        $sales = Sale::approved()
            ->where('sale_date', '>=', $startDate)
            ->get();

        $result = collect();
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $yearMonth = $date->format('Y-m');

            $monthlySales = $sales->filter(function ($sale) use ($yearMonth) {
                return $sale->sale_date->format('Y-m') === $yearMonth;
            });

            $result->push((object)[
                'year_month' => $yearMonth,
                'label'      => $date->isoFormat('MMMM Y'),
                'total'      => (float) $monthlySales->sum('total_price'),
                'profit'     => (float) $monthlySales->sum('profit'),
                'count'      => $monthlySales->count(),
            ]);
        }

        return $result;
    }
}
