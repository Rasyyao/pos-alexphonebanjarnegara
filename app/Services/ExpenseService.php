<?php
namespace App\Services;

use App\Models\Expense;
use App\Models\User;
use App\Repositories\Contracts\ExpenseRepositoryInterface;

class ExpenseService
{
    public function __construct(
        private readonly ExpenseRepositoryInterface $expenses,
    ) {}

    public function store(array $validated, User $actor): Expense
    {
        // Normalize the amount: strip thousand-separator dots, replace comma decimal
        $validated['amount']     = (float) str_replace(['.', ','], ['', '.'], $validated['amount']);
        $validated['created_by'] = $actor->id;
        return $this->expenses->create($validated);
    }

    public function destroy(Expense $expense): void
    {
        $this->expenses->delete($expense);
    }
}
