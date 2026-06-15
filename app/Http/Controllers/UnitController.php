<?php
namespace App\Http\Controllers;
use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Models\Unit;
use App\Services\FinanceService;
use App\Services\UnitService;
use App\Repositories\Contracts\ProductBrandRepositoryInterface;

class UnitController extends Controller
{
    public function __construct(
        private readonly UnitService $service,
        private readonly ProductBrandRepositoryInterface $brands,
        private readonly FinanceService $finance,
    ) {}

    public function index()
    {
        $brands = $this->brands->allWithModels();
        return view('units.index', compact('brands'));
    }

    public function create()
    {
        $brands = $this->brands->allWithModels();
        $summary = $this->finance->reportSummary();
        $saldoKas = (float) $summary['saldoKas'];
        $saldoAtm = (float) $summary['saldoAtmLifetime'];
        return view('units.create', compact('brands', 'saldoKas', 'saldoAtm'));
    }

    public function store(StoreUnitRequest $request)
    {
        $data  = $request->validated();
        $price = (float) $data['purchase_price'];

        [$purchaseCash, $purchaseTransfer, $data] = $this->resolveSplit($data, $price);

        $summary  = $this->finance->reportSummary();
        $saldoKas = (float) $summary['saldoKas'];
        $saldoAtm = (float) $summary['saldoAtmLifetime'];

        if (abs($purchaseCash + $purchaseTransfer - $price) > 0.01) {
            $msg = 'Jumlah cash dan transfer harus sama dengan harga beli.';
            return back()->withInput()->with('error', $msg)->withErrors(['purchase_price' => $msg]);
        }
        if ($purchaseCash > $saldoKas) {
            $msg = 'Saldo Kas Tunai tidak cukup. Butuh Rp ' . number_format($purchaseCash, 0, ',', '.') . ', tersedia Rp ' . number_format($saldoKas, 0, ',', '.') . '.';
            return back()->withInput()->with('error', $msg)->withErrors(['purchase_cash' => $msg]);
        }
        if ($purchaseTransfer > $saldoAtm) {
            $msg = 'Saldo ATM / Rekening tidak cukup. Butuh Rp ' . number_format($purchaseTransfer, 0, ',', '.') . ', tersedia Rp ' . number_format($saldoAtm, 0, ',', '.') . '.';
            return back()->withInput()->with('error', $msg)->withErrors(['purchase_transfer' => $msg]);
        }

        $this->service->store($data, $request->user());
        return redirect()->route('units.index')->with('success', 'Unit berhasil ditambahkan.');
    }

    public function show(Unit $unit)
    {
        $unit->load('model.brand','creator','saleItem.sale');
        return view('units.show', compact('unit'));
    }

    public function edit(Unit $unit)
    {
        $brands = $this->brands->allWithModels();
        $summary = $this->finance->reportSummary();
        $saldoKas = (float) $summary['saldoKas'];
        $saldoAtm = (float) $summary['saldoAtmLifetime'];
        return view('units.edit', compact('unit','brands', 'saldoKas', 'saldoAtm'));
    }

    public function update(UpdateUnitRequest $request, Unit $unit)
    {
        $data  = $request->validated();
        $price = (float) $data['purchase_price'];

        [$purchaseCash, $purchaseTransfer, $data] = $this->resolveSplit($data, $price);

        $summary  = $this->finance->reportSummary();
        $saldoKas = (float) $summary['saldoKas'] + (float) $unit->purchase_cash;
        $saldoAtm = (float) $summary['saldoAtmLifetime'] + (float) $unit->purchase_transfer;

        if (abs($purchaseCash + $purchaseTransfer - $price) > 0.01) {
            return back()->withInput()->withErrors(['purchase_price' => 'Jumlah cash dan transfer harus sama dengan harga beli.']);
        }
        if ($purchaseCash > $saldoKas) {
            return back()->withInput()->withErrors(['purchase_cash' => 'Saldo Kas Tunai tidak cukup (Rp ' . number_format($saldoKas, 0, ',', '.') . ').']);
        }
        if ($purchaseTransfer > $saldoAtm) {
            return back()->withInput()->withErrors(['purchase_transfer' => 'Saldo ATM / Rekening tidak cukup (Rp ' . number_format($saldoAtm, 0, ',', '.') . ').']);
        }

        $this->service->update($unit, $data);
        return redirect()->route('units.index')->with('success', 'Unit berhasil diperbarui.');
    }

    public function destroy(Unit $unit)
    {
        try {
            $this->service->destroy($unit);
            return redirect()->route('units.index')->with('success', 'Unit berhasil dihapus.');
        } catch (\LogicException $e) {
            return back()->with('error', $e->getMessage());
        }
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
