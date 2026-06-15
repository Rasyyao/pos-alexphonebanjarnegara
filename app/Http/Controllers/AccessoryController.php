<?php
namespace App\Http\Controllers;
use App\Http\Requests\StoreAccessoryRequest;
use App\Http\Requests\UpdateAccessoryRequest;
use App\Models\Accessory;
use App\Models\SaleItem;
use App\Repositories\Contracts\AccessoryRepositoryInterface;
use App\Services\AccessoryService;
use App\Services\FinanceService;

class AccessoryController extends Controller
{
    public function __construct(
        private readonly AccessoryService $service,
        private readonly AccessoryRepositoryInterface $accessories,
        private readonly FinanceService $finance,
    ) {}

    public function index()
    {
        return view('accessories.index');
    }

    public function create()
    {
        $categories = $this->accessories->categories();
        $summary = $this->finance->reportSummary();
        $saldoKas = (float) $summary['saldoKas'];
        $saldoAtm = (float) $summary['saldoAtmLifetime'];
        return view('accessories.create', compact('categories', 'saldoKas', 'saldoAtm'));
    }

    public function store(StoreAccessoryRequest $request)
    {
        $data = $request->validated();
        $summary = $this->finance->reportSummary();
        $saldoKas = (float) $summary['saldoKas'];
        $saldoAtm = (float) $summary['saldoAtmLifetime'];

        $purchaseCash = (float) ($request->purchase_cash ?? 0);
        $purchaseTransfer = (float) ($request->purchase_transfer ?? 0);
        $purchasePrice = (float) $request->purchase_price;
        $stockQty = (int) ($request->stock_qty ?? 0);

        if (abs($purchaseCash + $purchaseTransfer - $purchasePrice) > 0.01) {
            return back()->withInput()->withErrors([
                'purchase_price' => 'Jumlah cash dan transfer harus sama dengan harga beli.',
            ]);
        }

        $totalCashCost = $purchaseCash * $stockQty;
        $totalTransferCost = $purchaseTransfer * $stockQty;

        if ($totalCashCost > $saldoKas) {
            return back()->withInput()->withErrors([
                'purchase_cash' => 'Saldo Kas Tunai tidak cukup (Maksimal Rp ' . number_format($saldoKas, 0, ',', '.') . ', Total beli Rp ' . number_format($totalCashCost, 0, ',', '.') . ').',
            ]);
        }

        if ($totalTransferCost > $saldoAtm) {
            return back()->withInput()->withErrors([
                'purchase_transfer' => 'Saldo ATM / Rekening tidak cukup (Maksimal Rp ' . number_format($saldoAtm, 0, ',', '.') . ', Total beli Rp ' . number_format($totalTransferCost, 0, ',', '.') . ').',
            ]);
        }

        $this->service->store($data);
        return redirect()->route('accessories.index')->with('success', 'Aksesoris berhasil ditambahkan.');
    }

    public function show(Accessory $accessory)
    {
        $saleHistory = SaleItem::with(['sale.payments', 'sale.creator'])
            ->where('accessory_id', $accessory->id)
            ->whereHas('sale', fn($q) => $q->whereIn('status', ['approved', 'pending']))
            ->latest('id')
            ->get();

        return view('accessories.show', compact('accessory', 'saleHistory'));
    }

    public function edit(Accessory $accessory)
    {
        $categories = $this->accessories->categories();
        $summary = $this->finance->reportSummary();
        $saldoKas = (float) $summary['saldoKas'];
        $saldoAtm = (float) $summary['saldoAtmLifetime'];
        return view('accessories.edit', compact('accessory', 'categories', 'saldoKas', 'saldoAtm'));
    }

    public function update(UpdateAccessoryRequest $request, Accessory $accessory)
    {
        $data = $request->validated();
        $summary = $this->finance->reportSummary();
        // Add back this accessory's existing purchase cash and transfer costs to available balances
        $saldoKas = (float) $summary['saldoKas'] + ((float) $accessory->purchase_cash * $accessory->stock_qty);
        $saldoAtm = (float) $summary['saldoAtmLifetime'] + ((float) $accessory->purchase_transfer * $accessory->stock_qty);

        $purchaseCash = (float) ($request->purchase_cash ?? 0);
        $purchaseTransfer = (float) ($request->purchase_transfer ?? 0);
        $purchasePrice = (float) $request->purchase_price;
        $stockQty = (int) ($request->stock_qty ?? 0);

        if (abs($purchaseCash + $purchaseTransfer - $purchasePrice) > 0.01) {
            return back()->withInput()->withErrors([
                'purchase_price' => 'Jumlah cash dan transfer harus sama dengan harga beli.',
            ]);
        }

        $totalCashCost = $purchaseCash * $stockQty;
        $totalTransferCost = $purchaseTransfer * $stockQty;

        if ($totalCashCost > $saldoKas) {
            return back()->withInput()->withErrors([
                'purchase_cash' => 'Saldo Kas Tunai tidak cukup (Maksimal Rp ' . number_format($saldoKas, 0, ',', '.') . ', Total beli Rp ' . number_format($totalCashCost, 0, ',', '.') . ').',
            ]);
        }

        if ($totalTransferCost > $saldoAtm) {
            return back()->withInput()->withErrors([
                'purchase_transfer' => 'Saldo ATM / Rekening tidak cukup (Maksimal Rp ' . number_format($saldoAtm, 0, ',', '.') . ', Total beli Rp ' . number_format($totalTransferCost, 0, ',', '.') . ').',
            ]);
        }

        $this->service->update($accessory, $data);
        return redirect()->route('accessories.show', $accessory)->with('success', 'Aksesoris berhasil diperbarui.');
    }

    public function destroy(Accessory $accessory)
    {
        $this->service->destroy($accessory);
        return redirect()->route('accessories.index')->with('success', 'Aksesoris berhasil dihapus.');
    }
}
