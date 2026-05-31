<?php

namespace App\Repositories\Contracts;

use App\Models\Accessory;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface AccessoryRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 10, string $pageName = 'page'): LengthAwarePaginator;
    public function findById(int $id): Accessory;
    public function create(array $data): Accessory;
    public function update(Accessory $accessory, array $data): Accessory;
    public function delete(Accessory $accessory): void;
    public function available(): Collection;
    /** Sum of stock_qty of all accessories. */
    public function totalStockQty(): int;
    /** Get all distinct accessory categories. */
    public function categories(): Collection;
}
