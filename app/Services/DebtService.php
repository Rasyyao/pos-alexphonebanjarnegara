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

    public function markPaid(Debt $debt, string $paymentMethod = 'cash'): Debt
    {
        $status = is_string($debt->status) ? $debt->status : ($debt->status->value ?? '');
        if ($status === 'paid') {
            throw new \LogicException('Utang sudah lunas.');
        }
        $remaining = $debt->amount - $debt->paid_amount;
        return DB::transaction(function () use ($debt, $remaining, $paymentMethod) {
            $updated = $this->debts->markPaid($debt);
            $debt->sale->payments()->create([
                'method' => $paymentMethod,
                'amount' => $remaining,
                'source' => 'debt_payment',
            ]);
            return $updated;
        });
    }

    public function pay(Debt $debt, string $type, float $amount = 0, string $paymentMethod = 'cash'): Debt
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

        return DB::transaction(function () use ($debt, $payAmount, $type, $paymentMethod) {
            $newPaidAmount = $debt->paid_amount + $payAmount;
            $newStatus = $newPaidAmount >= $debt->amount ? 'paid' : 'partial';

            $updatedDebt = $this->debts->update($debt, [
                'paid_amount' => $newPaidAmount,
                'status'      => $newStatus,
            ]);

            $updatedDebt->sale->payments()->create([
                'method' => $paymentMethod,
                'amount' => $payAmount,
                'source' => 'debt_payment',
            ]);

            Log::info('Debt payment recorded', [
                'debt_id'               => $updatedDebt->id,
                'sale_id'               => $updatedDebt->sale_id,
                'invoice'               => $updatedDebt->sale->invoice_number ?? '—',
                'payment_type'          => $type,
                'payment_method'        => $paymentMethod,
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
