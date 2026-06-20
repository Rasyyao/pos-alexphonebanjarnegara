<?php
namespace App\Repositories\Contracts;

use App\Models\Expense;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ExpenseRepositoryInterface
{
    public function allOrdered(): Collection;
    public function paginate(int $perPage = 10): LengthAwarePaginator;
    public function create(array $data): Expense;
    public function delete(Expense $expense): void;
    public function sumTotal(): float;
    public function sumByCategory(): Collection;
    /** Get monthly expenses excluding owner withdrawals. */
    public function monthlyExpensesExcludingOwner(int $months = 6): Collection;
}
