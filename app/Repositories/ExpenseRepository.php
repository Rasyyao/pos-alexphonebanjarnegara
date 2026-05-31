<?php
namespace App\Repositories;

use App\Models\Expense;
use App\Repositories\Contracts\ExpenseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ExpenseRepository implements ExpenseRepositoryInterface
{
    public function allOrdered(): Collection
    {
        return Expense::with('creator')->latest('expense_date')->get();
    }

    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return Expense::with('creator')->latest('expense_date')->paginate($perPage);
    }

    public function create(array $data): Expense
    {
        return Expense::create($data);
    }

    public function delete(Expense $expense): void
    {
        $expense->delete();
    }

    public function sumTotal(): float
    {
        return (float) Expense::sum('amount');
    }

    public function sumByCategory(): Collection
    {
        return Expense::selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get()
            ->pluck('total', 'category');
    }
}
