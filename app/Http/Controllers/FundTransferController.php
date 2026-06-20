<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFundTransferRequest;
use App\Models\FundTransfer;
use App\Repositories\Contracts\FundTransferRepositoryInterface;
use App\Services\FinanceService;
use App\Services\FundTransferService;

class FundTransferController extends Controller
{
    public function __construct(
        private readonly FundTransferService $service,
        private readonly FundTransferRepositoryInterface $transfers,
        private readonly FinanceService $finance,
    ) {}

    public function index()
    {
        $cashToAtm = $this->transfers->sumCashToAtm();
        $atmToCash = $this->transfers->sumAtmToCash();
        $saldo     = $this->finance->saldoSplit();

        return view('fund-transfers.index', [
            'fundTransfers'  => $this->transfers->paginate(20),
            'totalCashToAtm' => $cashToAtm,
            'totalAtmToCash' => $atmToCash,
            'netEffect'      => $cashToAtm - $atmToCash,
            'saldoKas'       => $saldo['saldoKas'],
            'saldoAtm'       => $saldo['saldoAtm'],
        ]);
    }

    public function store(StoreFundTransferRequest $request)
    {
        $this->service->store($request->validated(), $request->user());
        return back()->with('success', 'Mutasi dana berhasil dicatat.');
    }

    public function destroy(FundTransfer $fundTransfer)
    {
        try {
            $this->service->destroy($fundTransfer);
            return back()->with('success', 'Catatan mutasi berhasil dihapus.');
        } catch (\LogicException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
