<?php

namespace App\Repositories;

use App\Models\Accessory;
use App\Repositories\Contracts\AccessoryRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AccessoryRepository implements AccessoryRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 10, string $pageName = 'page'): LengthAwarePaginator
    {
        return Accessory::when($filters['search'] ?? null, fn($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->when($filters['category'] ?? null, fn($q, $v) => $q->where('category', $v))
            ->when($filters['stock_status'] ?? null, function($q, $v) {
                if ($v === 'ready') {
                    return $q->where('stock_qty', '>', 0);
                } elseif ($v === 'empty') {
                    return $q->where('stock_qty', 0);
                }
                return $q;
            })
            ->latest()
            ->paginate($perPage, ['*'], $pageName);
    }

    public function findById(int $id): Accessory
    {
        return Accessory::findOrFail($id);
    }

    public function create(array $data): Accessory
    {
        return Accessory::create($data);
    }

    public function update(Accessory $accessory, array $data): Accessory
    {
        $accessory->update($data);
        return $accessory->fresh();
    }

    public function delete(Accessory $accessory): void
    {
        $accessory->delete();
    }

    public function available(): Collection
    {
        return Accessory::where('stock_qty', '>', 0)->get();
    }

    public function totalStockQty(): int
    {
        return (int) Accessory::sum('stock_qty');
    }

    public function categories(): Collection
    {
        return Accessory::select('category')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');
    }
}
