<?php

namespace App\Livewire;

use App\Models\Debt;
use Livewire\Component;
use Livewire\WithPagination;

class DebtList extends Component
{
    use WithPagination;
    public string $filter = 'unpaid';

    public function updatedFilter(): void { $this->resetPage(); }

    public function markPaid(int $debtId): void
    {
        $debt = Debt::findOrFail($debtId);
        if ($debt->status !== 'paid') {
            $debt->update(['paid_amount' => $debt->amount, 'status' => 'paid']);
            session()->flash('success', 'Utang berhasil ditandai lunas.');
        }
    }

    public function render()
    {
        $debts = Debt::with('sale.creator')
            ->when($this->filter !== 'all', fn($q) => $q->where('status', $this->filter))
            ->latest()
            ->paginate(10);

        return view('livewire.debt-list', compact('debts'));
    }
}
