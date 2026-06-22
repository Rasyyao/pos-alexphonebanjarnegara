<?php
namespace App\Http\Controllers;

use App\Models\Debt;
use App\Services\DebtService;

use App\Http\Requests\PayDebtRequest;
use App\Repositories\Contracts\DebtRepositoryInterface;
use Illuminate\Http\Request;

class DebtController extends Controller
{
    public function __construct(
        private readonly DebtService $service,
        private readonly DebtRepositoryInterface $debtsRepository
    ) {}

    public function index(Request $request)
    {
        $statusFilter = $request->input('status', 'active');
        $debts = $this->debtsRepository->paginate(['status' => $statusFilter], 10);
        $unpaidSum = $this->debtsRepository->unpaidSum();
        
        $paidSum = \App\Models\Debt::where('status', 'paid')->sum('amount');
        $debitorsCount = \App\Models\Debt::where('status', '!=', 'paid')->distinct('sale_id')->count();

        return view('debts.index', compact('debts', 'unpaidSum', 'paidSum', 'debitorsCount', 'statusFilter'));
    }

    public function pay(PayDebtRequest $request, Debt $debt)
    {
        try {
            $this->service->pay(
                $debt,
                $request->type,
                (float) $request->amount,
                $request->input('payment_method', 'cash'),
                $request->input('payment_date')
            );
            return back()->with('success', 'Pembayaran utang berhasil dicatat.');
        } catch (\LogicException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function markPaid(Debt $debt)
    {
        try {
            $this->service->markPaid($debt);
            return back()->with('success', 'Utang berhasil ditandai lunas.');
        } catch (\LogicException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
