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
        $validated['amount']         = (float) str_replace(['.', ','], ['', '.'], $validated['amount']);
        $validated['payment_method'] = $validated['payment_method'] ?? 'cash';
        $validated['created_by']     = $actor->id;
        return $this->expenses->create($validated);
    }

    public function update(Expense $expense, array $validated): Expense
    {
        $validated['amount']         = (float) str_replace(['.', ','], ['', '.'], $validated['amount']);
        $validated['payment_method'] = $validated['payment_method'] ?? 'cash';
        $expense->update($validated);
        return $expense->fresh();
    }

    public function destroy(Expense $expense): void
    {
        $this->expenses->delete($expense);
    }
}

