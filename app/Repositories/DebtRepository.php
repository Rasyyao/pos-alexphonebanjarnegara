<?php
namespace App\Repositories;

use App\Models\Debt;
use App\Repositories\Contracts\DebtRepositoryInterface;

class DebtRepository implements DebtRepositoryInterface
{
    public function unpaidSum(): float
    {
        return (float) Debt::where('status', '!=', 'paid')->get()->sum(fn($d) => $d->amount - $d->paid_amount);
    }

    public function findById(int $id): Debt
    {
        return Debt::findOrFail($id);
    }

    public function markPaid(Debt $debt): Debt
    {
        $debt->update(['paid_amount' => $debt->amount, 'status' => 'paid']);
        return $debt->fresh();
    }

    public function update(Debt $debt, array $data): Debt
    {
        $debt->update($data);
        return $debt->fresh();
    }

    public function paginate(array $filters = [], int $perPage = 10): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Debt::with('sale.creator')
            ->when($filters['status'] ?? null, function ($q, $status) {
                if ($status === 'active') {
                    $q->whereIn('status', ['unpaid', 'partial']);
                } elseif ($status !== 'all') {
                    $q->where('status', $status);
                }
            })
            ->latest()
            ->paginate($perPage);
    }
}
