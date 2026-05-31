<?php
namespace App\Repositories\Contracts;

use App\Models\Capital;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CapitalRepositoryInterface
{
    public function paginate(int $perPage = 10): LengthAwarePaginator;
    public function sumTotal(): float;
    public function sumInitialAndAddition(): float;
    public function create(array $data): Capital;
    public function update(Capital $capital, array $data): Capital;
    public function delete(Capital $capital): void;
}
