<?php

namespace App\Repositories\Contracts;

use App\Models\FundTransfer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface FundTransferRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function create(array $data): FundTransfer;
    public function delete(FundTransfer $transfer): void;
    public function sumCashToAtm(): float;
    public function sumAtmToCash(): float;
}
