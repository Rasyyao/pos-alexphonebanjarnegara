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

    public function monthlyExpensesExcludingOwner(int $months = 6): Collection
    {
        $startDate = now()->subMonths($months - 1)->startOfMonth()->toDateString();
        $expenses = Expense::where('category', '!=', 'tarik_owner')
            ->where('expense_date', '>=', $startDate)
            ->get();

        $hpExpenses = \App\Models\Unit::where('purchase_date', '>=', $startDate)
            ->get();

        $result = collect();
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $yearMonth = $date->format('Y-m');

            $monthlyExp = $expenses->filter(function ($exp) use ($yearMonth) {
                return $exp->expense_date->format('Y-m') === $yearMonth;
            });

            $monthlyHp = $hpExpenses->filter(function ($unit) use ($yearMonth) {
                return $unit->purchase_date->format('Y-m') === $yearMonth;
            });

            $result->push((object)[
                'year_month' => $yearMonth,
                'label'      => $date->isoFormat('MMMM Y'),
                'total'      => (float) ($monthlyExp->sum('amount') + $monthlyHp->sum('purchase_price')),
            ]);
        }

        return $result;
    }
}
