<?php

namespace App\Repositories;

use App\Models\FundTransfer;
use App\Repositories\Contracts\FundTransferRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FundTransferRepository implements FundTransferRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return FundTransfer::with('creator')
            ->latest('transfer_date')
            ->latest('id')
            ->paginate($perPage);
    }

    public function create(array $data): FundTransfer
    {
        return FundTransfer::create($data);
    }

    public function delete(FundTransfer $transfer): void
    {
        $transfer->delete();
    }

    public function sumCashToAtm(): float
    {
        return (float) FundTransfer::where('direction', 'cash_to_atm')->sum('amount');
    }

    public function sumAtmToCash(): float
    {
        return (float) FundTransfer::where('direction', 'atm_to_cash')->sum('amount');
    }
}
