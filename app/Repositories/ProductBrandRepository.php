<?php

namespace App\Repositories;

use App\Models\ProductBrand;
use App\Repositories\Contracts\ProductBrandRepositoryInterface;
use Illuminate\Support\Collection;

class ProductBrandRepository implements ProductBrandRepositoryInterface
{
    public function allWithModels(): Collection
    {
        return ProductBrand::with('models')->get();
    }
}
