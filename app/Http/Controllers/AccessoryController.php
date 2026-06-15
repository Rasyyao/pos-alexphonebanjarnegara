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
        $data  = $request->validated();
        $price = (float) $data['purchase_price'];
        $qty   = (int)   $data['stock_qty'];

        [$purchaseCash, $purchaseTransfer, $data] = $this->resolveSplit($data, $price);

        $summary  = $this->finance->reportSummary();
        $saldoKas = (float) $summary['saldoKas'];
        $saldoAtm = (float) $summary['saldoAtmLifetime'];

        if (abs($purchaseCash + $purchaseTransfer - $price) > 0.01) {
            $msg = 'Jumlah cash dan transfer harus sama dengan harga beli.';
            return back()->withInput()->with('error', $msg)->withErrors(['purchase_price' => $msg]);
        }
        if (($purchaseCash * $qty) > $saldoKas) {
            $msg = 'Saldo Kas Tunai tidak cukup. Butuh Rp ' . number_format($purchaseCash * $qty, 0, ',', '.') . ', tersedia Rp ' . number_format($saldoKas, 0, ',', '.') . '.';
            return back()->withInput()->with('error', $msg)->withErrors(['purchase_cash' => $msg]);
        }
        if (($purchaseTransfer * $qty) > $saldoAtm) {
            $msg = 'Saldo ATM / Rekening tidak cukup. Butuh Rp ' . number_format($purchaseTransfer * $qty, 0, ',', '.') . ', tersedia Rp ' . number_format($saldoAtm, 0, ',', '.') . '.';
            return back()->withInput()->with('error', $msg)->withErrors(['purchase_transfer' => $msg]);
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
        $data  = $request->validated();
        $price = (float) $data['purchase_price'];
        $qty   = (int)   $data['stock_qty'];

        [$purchaseCash, $purchaseTransfer, $data] = $this->resolveSplit($data, $price);

        $summary  = $this->finance->reportSummary();
        $saldoKas = (float) $summary['saldoKas'] + ((float) $accessory->purchase_cash * $accessory->stock_qty);
        $saldoAtm = (float) $summary['saldoAtmLifetime'] + ((float) $accessory->purchase_transfer * $accessory->stock_qty);

        if (abs($purchaseCash + $purchaseTransfer - $price) > 0.01) {
            return back()->withInput()->withErrors(['purchase_price' => 'Jumlah cash dan transfer harus sama dengan harga beli.']);
        }
        if (($purchaseCash * $qty) > $saldoKas) {
            return back()->withInput()->withErrors(['purchase_cash' => 'Saldo Kas Tunai tidak cukup (Rp ' . number_format($saldoKas, 0, ',', '.') . ').']);
        }
        if (($purchaseTransfer * $qty) > $saldoAtm) {
            return back()->withInput()->withErrors(['purchase_transfer' => 'Saldo ATM / Rekening tidak cukup (Rp ' . number_format($saldoAtm, 0, ',', '.') . ').']);
        }

        $this->service->update($accessory, $data);
        return redirect()->route('accessories.show', $accessory)->with('success', 'Aksesoris berhasil diperbarui.');
    }

    public function destroy(Accessory $accessory)
    {
        $this->service->destroy($accessory);
        return redirect()->route('accessories.index')->with('success', 'Aksesoris berhasil dihapus.');
    }

    private function resolveSplit(array $data, float $price): array
    {
        $method = $data['purchase_payment_method'];
        if ($method === 'cash') {
            $cash     = $price;
            $transfer = 0.0;
            $data['purchase_payment_method'] = 'cash';
        } elseif ($method === 'transfer') {
            $cash     = 0.0;
            $transfer = $price;
            $data['purchase_payment_method'] = 'transfer';
        } else {
            $cash     = (float) ($data['purchase_cash'] ?? 0);
            $transfer = (float) ($data['purchase_transfer'] ?? 0);
            $data['purchase_payment_method'] = 'cash';
        }
        $data['purchase_cash']     = $cash;
        $data['purchase_transfer'] = $transfer;
        return [$cash, $transfer, $data];
    }
}
