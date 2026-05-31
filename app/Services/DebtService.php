<?php
namespace App\Services;

use App\Models\Debt;
use App\Repositories\Contracts\DebtRepositoryInterface;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DebtService
{
    public function __construct(
        private readonly DebtRepositoryInterface $debts,
    ) {}

    public function markPaid(Debt $debt): Debt
    {
        $status = is_string($debt->status) ? $debt->status : ($debt->status->value ?? '');
        if ($status === 'paid') {
            throw new \LogicException('Utang sudah lunas.');
        }
        return $this->debts->markPaid($debt);
    }

    public function pay(Debt $debt, string $type, float $amount = 0): Debt
    {
        $status = is_string($debt->status) ? $debt->status : ($debt->status->value ?? '');
        if ($status === 'paid') {
            throw new \LogicException('Utang ini sudah lunas.');
        }

        $outstanding = $debt->amount - $debt->paid_amount;

        if ($type === 'full') {
            $payAmount = $outstanding;
        } elseif ($type === 'partial') {
            if ($amount <= 0) {
                throw new \LogicException('Jumlah cicilan harus lebih besar dari Rp 0.');
            }
            if ($amount > $outstanding) {
                throw new \LogicException('Jumlah cicilan tidak boleh melebihi sisa utang yang harus dibayar.');
            }
            $payAmount = $amount;
        } else {
            throw new \InvalidArgumentException('Tipe pembayaran tidak valid.');
        }

        return DB::transaction(function () use ($debt, $payAmount, $type) {
            $newPaidAmount = $debt->paid_amount + $payAmount;
            $newStatus = $newPaidAmount >= $debt->amount ? 'paid' : 'partial';

            $updatedDebt = $this->debts->update($debt, [
                'paid_amount' => $newPaidAmount,
                'status'      => $newStatus,
            ]);

            Log::info('Debt payment recorded', [
                'debt_id'               => $updatedDebt->id,
                'sale_id'               => $updatedDebt->sale_id,
                'invoice'               => $updatedDebt->sale->invoice_number ?? '—',
                'payment_type'          => $type,
                'paid_amount_increment' => $payAmount,
                'total_paid_amount'     => $updatedDebt->paid_amount,
                'outstanding_remaining' => $updatedDebt->amount - $updatedDebt->paid_amount,
                'new_status'            => $updatedDebt->status,
                'recorded_by'           => auth()->id(),
            ]);

            return $updatedDebt;
        });
    }
}
