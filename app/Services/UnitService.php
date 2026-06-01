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
        return $this->units->create($validated);
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
