<?php
namespace App\Http\Controllers;

use App\Models\Sale;
use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Services\SaleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function __construct(
        private readonly SaleService             $service,
        private readonly SaleRepositoryInterface $sales,
        private readonly \App\Repositories\Contracts\UnitRepositoryInterface $units,
        private readonly \App\Repositories\Contracts\AccessoryRepositoryInterface $accessories,
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

        $pending            = $this->sales->pendingPaginate(10, 'page_sale')->withQueryString();
        $pendingUnits       = $this->units->paginate(['status' => \App\Enums\UnitStatus::Pending], 10, 'page_unit')->withQueryString();
        $pendingAccessories = $this->accessories->paginate(['status' => \App\Enums\AccessoryStatus::Pending], 10, 'page_accessory')->withQueryString();
        $pendingClosings    = \App\Models\DailyClosing::where('status', 'closed')
            ->orderBy('closing_date', 'desc')
            ->paginate(10, ['*'], 'page_closing')
            ->withQueryString();

        $isSuperadmin = true;

        return view('sales.verify', compact('pending', 'pendingUnits', 'pendingAccessories', 'pendingClosings', 'isSuperadmin'));
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

    public function edit(Sale $sale)
    {
        abort_unless(auth()->user()->role->value === 'superadmin', 403);
        $sale->load(['creator', 'items.unit.model.brand', 'items.accessory', 'payments']);
        return view('sales.edit', compact('sale'));
    }

    public function update(Request $request, Sale $sale)
    {
        abort_unless(auth()->user()->role->value === 'superadmin', 403);
        $request->validate([
            'sale_date'             => ['required', 'date'],
            'description'           => ['nullable', 'string', 'max:1000'],
            'items'                 => ['required', 'array'],
            'items.*.selling_price' => ['required', 'numeric', 'min:0'],
            'payments'              => ['required', 'array', 'min:1'],
            'payments.*.method'     => ['required', 'in:cash,transfer,utang'],
            'payments.*.amount'     => ['required', 'numeric', 'min:1'],
        ]);

        DB::transaction(function () use ($request, $sale) {
            \App\Services\DailyClosingService::assertDateNotLocked($sale->sale_date->toDateString());
            \App\Services\DailyClosingService::assertDateNotLocked($request->sale_date);
            foreach ($request->input('items') as $itemId => $data) {
                $item = $sale->items()->findOrFail($itemId);
                $price = (float) $data['selling_price'];
                $item->update([
                    'selling_price' => $price,
                    'subtotal'      => $price * $item->quantity,
                ]);
            }
            $sale->load('items');
            $total  = $sale->items->sum('subtotal');
            $profit = $sale->items->sum(fn($i) => ($i->selling_price - $i->purchase_price) * $i->quantity);
            $payments = collect($request->input('payments'))
                ->map(fn($payment) => [
                    'method' => $payment['method'],
                    'amount' => (float) $payment['amount'],
                ])
                ->values();
            $paid = $payments->sum('amount');

            if ($paid < $total) {
                throw ValidationException::withMessages([
                    'payments' => 'Total pembayaran tidak boleh kurang dari total penjualan.',
                ]);
            }

            $utangTotal = $payments->where('method', 'utang')->sum('amount');
            $debt = $sale->debt()->lockForUpdate()->first();
            $currentPayments = $sale->payments()
                ->get()
                ->map(fn($payment) => [
                    'method' => $payment->method->value ?? $payment->method,
                    'amount' => (float) $payment->amount,
                ])
                ->values();

            if ($debt && (float) $debt->paid_amount > 0 && $currentPayments->toArray() !== $payments->toArray()) {
                throw ValidationException::withMessages([
                    'payments' => 'Split pembayaran tidak bisa diubah karena utang transaksi ini sudah pernah dicicil.',
                ]);
            }

            if ($debt && (float) $debt->paid_amount > $utangTotal) {
                throw ValidationException::withMessages([
                    'payments' => 'Nominal utang tidak boleh lebih kecil dari utang yang sudah dibayar.',
                ]);
            }

            $sale->update([
                'sale_date'   => $request->sale_date,
                'description' => $request->description ?: null,
                'total_price' => $total,
                'total_paid'  => $paid,
                'profit'      => $profit,
            ]);

            $sale->payments()->delete();
            $payments->each(fn($payment) => $sale->payments()->create($payment));

            if ($utangTotal > 0) {
                $paidAmount = $debt ? (float) $debt->paid_amount : 0;
                $status = $paidAmount >= $utangTotal ? 'paid' : ($paidAmount > 0 ? 'partial' : 'unpaid');
                $sale->debt()->updateOrCreate(
                    ['sale_id' => $sale->id],
                    ['amount' => $utangTotal, 'paid_amount' => $paidAmount, 'status' => $status]
                );
            } elseif ($debt) {
                $debt->delete();
            }
        });

        return redirect()->route('sales.show', $sale)->with('success', 'Transaksi berhasil diperbarui.');
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
        $user = auth()->user();
        $isSuperadmin = $user->role->value === 'superadmin';

        if (!$isSuperadmin) {
            abort_unless(
                $sale->status->value === 'pending' &&
                $sale->sale_date->isToday() &&
                $sale->created_by === $user->id,
                403,
                'Transaksi hari sebelumnya sudah tutup buku dan tidak dapat dihapus.'
            );
        }

        $invoice = $sale->invoice_number;

        DB::transaction(function () use ($sale) {
            \App\Services\DailyClosingService::assertDateNotLocked($sale->sale_date->toDateString());
            if ($sale->status->value === 'approved') {
                foreach ($sale->items()->with(['unit', 'accessory'])->get() as $item) {
                    if ($item->unit_id) $item->unit->update(['status' => 'ready']);
                    if ($item->accessory_id) $item->accessory->increment('stock_qty', $item->quantity);
                }
            }
            $sale->debt?->delete();
            $sale->items()->delete();
            $sale->payments()->delete();
            $sale->delete();
        });

        return redirect()->route('sales.index')->with('success', 'Transaksi ' . $invoice . ' berhasil dihapus.');
    }

    public function printReceipt(Sale $sale)
    {
        $sale->load(['creator', 'approver', 'items.unit.model.brand', 'items.accessory', 'payments']);
        return view('sales.print', compact('sale'));
    }
}
