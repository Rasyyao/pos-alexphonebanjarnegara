<?php
namespace App\Services;

use App\Models\Expense;
use App\Models\User;
use App\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Services\FinanceService;
use Illuminate\Validation\ValidationException;

class ExpenseService
{
    public function __construct(
        private readonly ExpenseRepositoryInterface $expenses,
    ) {}

    public function store(array $validated, User $actor): Expense
    {
        \App\Services\DailyClosingService::assertDateNotLocked($validated['expense_date']);
        // Normalize the amount: strip thousand-separator dots, replace comma decimal
        $validated['amount']         = (float) str_replace(['.', ','], ['', '.'], $validated['amount']);
        $validated['payment_method'] = $validated['payment_method'] ?? 'cash';
        $validated['created_by']     = $actor->id;

        $balances  = app(FinanceService::class)->saldoSplit();
        $available = $validated['payment_method'] === 'cash' ? $balances['saldoKas'] : $balances['saldoAtm'];

        if ($validated['amount'] > $available) {
            throw ValidationException::withMessages([
                'amount' => 'Saldo tidak mencukupi. Saldo ' . ($validated['payment_method'] === 'cash' ? 'Kas' : 'ATM') . ' saat ini: Rp ' . number_format($available, 0, ',', '.'),
            ]);
        }

        return $this->expenses->create($validated);
    }

    public function update(Expense $expense, array $validated): Expense
    {
        \App\Services\DailyClosingService::assertDateNotLocked($expense->expense_date->toDateString());
        \App\Services\DailyClosingService::assertDateNotLocked($validated['expense_date']);
        $validated['amount']         = (float) str_replace(['.', ','], ['', '.'], $validated['amount']);
        $validated['payment_method'] = $validated['payment_method'] ?? 'cash';

        $balances  = app(FinanceService::class)->saldoSplit();
        $currentAvailable = $validated['payment_method'] === 'cash' ? $balances['saldoKas'] : $balances['saldoAtm'];
        
        // If updating using the same payment method, the old amount must be added back to available balance check
        if ($expense->payment_method === $validated['payment_method']) {
            $available = $currentAvailable + $expense->amount;
        } else {
            $available = $currentAvailable;
        }

        if ($validated['amount'] > $available) {
            throw ValidationException::withMessages([
                'amount' => 'Saldo tidak mencukupi. Saldo ' . ($validated['payment_method'] === 'cash' ? 'Kas' : 'ATM') . ' saat ini: Rp ' . number_format($available, 0, ',', '.'),
            ]);
        }

        $expense->update($validated);
        return $expense->fresh();
    }

    public function destroy(Expense $expense): void
    {
        \App\Services\DailyClosingService::assertDateNotLocked($expense->expense_date->toDateString());
        $this->expenses->delete($expense);
    }
}

