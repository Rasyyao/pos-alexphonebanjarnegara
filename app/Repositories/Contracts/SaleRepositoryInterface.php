<?php

namespace App\Repositories\Contracts;

use App\Models\Sale;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface SaleRepositoryInterface
{
    public function paginate(array $filters = []): LengthAwarePaginator;
    public function findById(int $id): Sale;
    public function create(array $data): Sale;
    public function todayStats(): array;
    public function weekStats(): array;
    public function monthStats(): array;
    public function weeklyRevenue(): Collection;
    public function paymentBreakdownToday(): Collection;
    /** All pending sales with their relationships for the verify page. */
    public function pendingList(): Collection;
    /** Paginated pending sales for the verify page. */
    public function pendingPaginate(int $perPage = 10, string $pageName = 'page'): \Illuminate\Pagination\LengthAwarePaginator;
    /** All approved sales for a given date with relationships for reports. */
    public function approvedForDate(string $date): Collection;
    /** Sum of total_price for all approved sales. */
    public function totalRevenue(): float;
    /** Sum of profit for all approved sales. */
    public function totalProfit(): float;
    /** Approved sales with payments for a given date (Excel export). */
    public function approvedForExport(string $date): Collection;
    /** Get the latest sales transactions for dashboard display. */
    public function latestSales(int $limit = 5): Collection;
    /** Get monthly sales profit for the last N months. */
    public function monthlyProfit(int $months = 6): Collection;
}
