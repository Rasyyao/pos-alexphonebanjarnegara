<?php
namespace App\Repositories\Contracts;

use App\Models\Debt;
use Illuminate\Support\Collection;

interface DebtRepositoryInterface
{
    public function unpaidSum(): float;
    public function findById(int $id): Debt;
    public function markPaid(Debt $debt): Debt;
    public function update(Debt $debt, array $data): Debt;
    public function paginate(array $filters = [], int $perPage = 10): \Illuminate\Pagination\LengthAwarePaginator;
}
