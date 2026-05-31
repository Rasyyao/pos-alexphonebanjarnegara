<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface ProductBrandRepositoryInterface
{
    /** Retrieve all brands with their models. */
    public function allWithModels(): Collection;
}
