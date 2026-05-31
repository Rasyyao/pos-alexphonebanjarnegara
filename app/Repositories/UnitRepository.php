<?php

namespace App\Repositories;

use App\Models\Unit;
use App\Repositories\Contracts\UnitRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class UnitRepository implements UnitRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 10, string $pageName = 'page'): LengthAwarePaginator
    {
        return Unit::with('model.brand')
            ->when($filters['brand_id'] ?? null, fn($q, $v) => $q->whereHas('model', fn($q) => $q->where('brand_id', $v)))
            ->when($filters['model_id'] ?? null, fn($q, $v) => $q->where('model_id', $v))
            ->when($filters['unit_type'] ?? null, fn($q, $v) => $q->where('unit_type', $v))
            ->when($filters['grade'] ?? null, fn($q, $v) => $q->where('grade', $v))
            ->when($filters['status'] ?? null, fn($q, $v) => $q->where('status', $v))
            ->when($filters['ram'] ?? null, fn($q, $v) => $q->where('ram', $v))
            ->when($filters['rom'] ?? null, fn($q, $v) => $q->where('rom', $v))
            ->when($filters['color'] ?? null, fn($q, $v) => $q->where('color', $v))
            ->latest()
            ->paginate($perPage, ['*'], $pageName);
    }

    public function findById(int $id): Unit
    {
        return Unit::with('model.brand')->findOrFail($id);
    }

    public function create(array $data): Unit
    {
        return Unit::create($data);
    }

    public function update(Unit $unit, array $data): Unit
    {
        $unit->update($data);
        return $unit->fresh();
    }

    public function delete(Unit $unit): void
    {
        $unit->delete();
    }

    public function latestReady(int $limit = 5): Collection
    {
        return Unit::with('model.brand')->where('status', 'ready')->latest()->limit($limit)->get();
    }

    public function countByStatus(): array
    {
        $total  = Unit::where('status', 'ready')->count();
        $baru   = Unit::where('status', 'ready')->where('unit_type', 'baru')->count();
        $second = Unit::where('status', 'ready')->where('unit_type', 'second')->count();
        return compact('total', 'baru', 'second');
    }

    public function assetValue(): float
    {
        return (float) Unit::where('status', 'ready')->sum('purchase_price');
    }

    public function allForExport(?string $status = null): Collection
    {
        return Unit::with('model.brand')
            ->when($status, fn($q, $v) => $q->where('status', $v))
            ->latest()
            ->get();
    }

    public function totalPurchaseValue(): float
    {
        return (float) Unit::sum('purchase_price');
    }

    public function brandDistribution(): array
    {
        return Unit::where('status', 'ready')
            ->join('product_models', 'units.model_id', '=', 'product_models.id')
            ->join('product_brands', 'product_models.brand_id', '=', 'product_brands.id')
            ->selectRaw('product_brands.name as brand_name, count(*) as count')
            ->groupBy('product_brands.name')
            ->orderByDesc('count')
            ->get()
            ->toArray();
    }

    public function typeDistribution(): array
    {
        return Unit::where('status', 'ready')
            ->selectRaw('unit_type, count(*) as count')
            ->groupBy('unit_type')
            ->get()
            ->toArray();
    }

    public function statusDistribution(): array
    {
        return Unit::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get()
            ->toArray();
    }
}
