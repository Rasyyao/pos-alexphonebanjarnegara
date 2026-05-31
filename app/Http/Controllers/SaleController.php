<?php
namespace App\Http\Controllers;

use App\Models\Sale;
use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Services\SaleService;

class SaleController extends Controller
{
    public function __construct(
        private readonly SaleService             $service,
        private readonly SaleRepositoryInterface $sales,
    ) {}

    public function index()
    {
        return view('sales.index');
    }

    public function create()
    {
        return view('sales.create');
    }

    public function verify()
    {
        abort_unless(auth()->user()->role->value === 'superadmin', 403);
        $pending = $this->sales->pendingPaginate(10);
        return view('sales.verify', compact('pending'));
    }

    public function store(\App\Http\Requests\StoreSaleRequest $request)
    {
        try {
            $sale = $this->service->create($request->validated(), $request->user());
            return redirect()->route('sales.show', $sale)->with('success', 'Transaksi berhasil disimpan.');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['general' => $e->getMessage()])->withInput();
        }
    }

    public function show(Sale $sale)
    {
        $sale->load(['creator', 'approver', 'items.unit.model.brand', 'items.accessory', 'payments', 'debt']);
        return view('sales.show', compact('sale'));
    }

    public function approve(Sale $sale)
    {
        abort_unless(auth()->user()->role->value === 'superadmin', 403);
        try {
            $this->service->approve($sale, auth()->user());
            return back()->with('success', 'Transaksi berhasil di-approve.');
        } catch (\LogicException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Sale $sale)
    {
        abort_unless(auth()->user()->role->value === 'superadmin', 403);
        if ($sale->status->value !== 'pending') {
            return back()->with('error', 'Hanya transaksi pending yang dapat dihapus.');
        }
        $sale->items()->delete();
        $sale->payments()->delete();
        $sale->delete();
        return back()->with('success', 'Transaksi ' . $sale->invoice_number . ' berhasil dihapus.');
    }

    public function printReceipt(Sale $sale)
    {
        $sale->load(['creator', 'approver', 'items.unit.model.brand', 'items.accessory', 'payments']);
        return view('sales.print', compact('sale'));
    }
}
