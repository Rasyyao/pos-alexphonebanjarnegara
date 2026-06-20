<?php

namespace App\Services;

use App\Models\FundTransfer;
use App\Models\User;
use App\Repositories\Contracts\FundTransferRepositoryInterface;
use Illuminate\Support\Facades\Log;

class FundTransferService
{
    public function __construct(
        private readonly FundTransferRepositoryInterface $transfers,
    ) {}

    public function store(array $validated, User $actor): FundTransfer
    {
        $validated['created_by'] = $actor->id;

        $transfer = $this->transfers->create($validated);

        Log::info('Fund transfer recorded', [
            'id'          => $transfer->id,
            'direction'   => $transfer->direction,
            'amount'      => $transfer->amount,
            'created_by'  => $actor->id,
        ]);

        return $transfer;
    }

    public function destroy(FundTransfer $transfer): void
    {
        $finance = app(\App\Services\FinanceService::class);
        $saldo = $finance->saldoSplit();

        if ($transfer->direction === 'cash_to_atm') {
            if ($saldo['saldoAtm'] < $transfer->amount) {
                throw new \LogicException('Tidak dapat menghapus mutasi ini karena akan menyebabkan Saldo ATM menjadi negatif. Saldo ATM saat ini: Rp ' . number_format($saldo['saldoAtm'], 0, ',', '.'));
            }
        } elseif ($transfer->direction === 'atm_to_cash') {
            if ($saldo['saldoKas'] < $transfer->amount) {
                throw new \LogicException('Tidak dapat menghapus mutasi ini karena akan menyebabkan Saldo Kas menjadi negatif. Saldo Kas saat ini: Rp ' . number_format($saldo['saldoKas'], 0, ',', '.'));
            }
        }

        Log::info('Fund transfer deleted', [
            'id'         => $transfer->id,
            'direction'  => $transfer->direction,
            'amount'     => $transfer->amount,
            'deleted_by' => auth()->id(),
        ]);

        $this->transfers->delete($transfer);
    }
}
