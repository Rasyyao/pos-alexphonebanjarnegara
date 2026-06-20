<?php

namespace App\Services;

use App\Models\ProductBrand;
use App\Models\ProductModel;
use App\Models\Unit;
use App\Models\User;
use App\Repositories\Contracts\UnitRepositoryInterface;

class UnitService
{
    public function __construct(private readonly UnitRepositoryInterface $units) {}

    public function store(array $validated, User $actor): Unit
    {
        $validated['model_id'] = $this->resolveModelId($validated['brand_name'], $validated['model_name']);
        unset($validated['brand_name'], $validated['model_name']);

        $validated['created_by'] = $actor->id;
        $validated['status'] = $actor->role === \App\Enums\UserRole::Superadmin
            ? \App\Enums\UnitStatus::Ready
            : \App\Enums\UnitStatus::Pending;

        return $this->units->create($validated);
    }

    public function approve(Unit $unit, User $actor): Unit
    {
        if ($unit->status !== \App\Enums\UnitStatus::Pending) {
            throw new \LogicException('Hanya unit pending yang bisa di-approve.');
        }

        \Illuminate\Support\Facades\Log::info('Unit approved', [
            'unit_id'     => $unit->id,
            'approved_by' => $actor->id,
        ]);

        return $this->units->update($unit, ['status' => \App\Enums\UnitStatus::Ready]);
    }

    public function update(Unit $unit, array $validated): Unit
    {
        $validated['model_id'] = $this->resolveModelId($validated['brand_name'], $validated['model_name']);
        unset($validated['brand_name'], $validated['model_name']);

        return $this->units->update($unit, $validated);
    }

    private function resolveModelId(string $brandName, string $modelName): int
    {
        $brand = ProductBrand::firstOrCreate(['name' => trim($brandName)]);
        $model = ProductModel::firstOrCreate(['brand_id' => $brand->id, 'name' => trim($modelName)]);
        return $model->id;
    }

    public function destroy(Unit $unit): void
    {
        if ($unit->status->value === 'sold') {
            throw new \LogicException('Unit yang sudah terjual tidak dapat dihapus.');
        }
        $this->units->delete($unit);
    }
}
