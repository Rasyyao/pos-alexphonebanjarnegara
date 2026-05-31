<?php

namespace App\Services;

use App\Models\ProductBrand;
use App\Models\ProductModel;
use App\Models\Unit;
use App\Models\User;
use App\Repositories\Contracts\UnitRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UnitService
{
    public function __construct(private readonly UnitRepositoryInterface $units) {}

    public function store(array $validated, User $actor, ?UploadedFile $photo = null, ?UploadedFile $photo2 = null, ?UploadedFile $photo3 = null): Unit
    {
        $validated['model_id'] = $this->resolveModelId($validated['brand_name'], $validated['model_name']);
        unset($validated['brand_name'], $validated['model_name']);

        if ($photo)  $validated['photo_path']   = $photo->store('units', 'public');
        if ($photo2) $validated['photo_path_2']  = $photo2->store('units', 'public');
        if ($photo3) $validated['photo_path_3']  = $photo3->store('units', 'public');

        $validated['created_by'] = $actor->id;
        return $this->units->create($validated);
    }

    public function update(Unit $unit, array $validated, ?UploadedFile $photo = null, ?UploadedFile $photo2 = null, ?UploadedFile $photo3 = null): Unit
    {
        $validated['model_id'] = $this->resolveModelId($validated['brand_name'], $validated['model_name']);
        unset($validated['brand_name'], $validated['model_name']);

        if ($photo) {
            if ($unit->photo_path) Storage::disk('public')->delete($unit->photo_path);
            $validated['photo_path'] = $photo->store('units', 'public');
        }
        if ($photo2) {
            if ($unit->photo_path_2) Storage::disk('public')->delete($unit->photo_path_2);
            $validated['photo_path_2'] = $photo2->store('units', 'public');
        }
        if ($photo3) {
            if ($unit->photo_path_3) Storage::disk('public')->delete($unit->photo_path_3);
            $validated['photo_path_3'] = $photo3->store('units', 'public');
        }
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
        if ($unit->photo_path) Storage::disk('public')->delete($unit->photo_path);
        if ($unit->photo_path_2) Storage::disk('public')->delete($unit->photo_path_2);
        if ($unit->photo_path_3) Storage::disk('public')->delete($unit->photo_path_3);
        $this->units->delete($unit);
    }
}
