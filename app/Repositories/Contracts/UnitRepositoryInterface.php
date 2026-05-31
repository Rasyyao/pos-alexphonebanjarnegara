<?php

namespace App\Repositories\Contracts;

use App\Models\Unit;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface UnitRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 10, string $pageName = 'page'): LengthAwarePaginator;
    public function findById(int $id): Unit;
    public function create(array $data): Unit;
    public function update(Unit $unit, array $data): Unit;
    public function delete(Unit $unit): void;
    public function latestReady(int $limit = 5): Collection;
    public function countByStatus(): array;
    /** Total purchase_price of all ready units (stock asset value). */
    public function assetValue(): float;
    /** All units with brand/model for stock export. */
    public function allForExport(?string $status = null): Collection;
    /** Total purchase cost of all units purchased into stock. */
    public function totalPurchaseValue(): float;
    /** Brand distribution of ready units. */
    public function brandDistribution(): array;
    /** Condition (baru vs second) distribution of ready units. */
    public function typeDistribution(): array;
    /** Status (ready, sold, retur) distribution of all units. */
    public function statusDistribution(): array;
}
