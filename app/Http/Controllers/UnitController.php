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
        $summary = $this->finance->reportSummary();
        $saldoKas = (float) $summary['saldoKas'];
        $saldoAtm = (float) $summary['saldoAtmLifetime'];

        $purchaseCash = (float) ($request->purchase_cash ?? 0);
        $purchaseTransfer = (float) ($request->purchase_transfer ?? 0);
        $purchasePrice = (float) $request->purchase_price;

        if (abs($purchaseCash + $purchaseTransfer - $purchasePrice) > 0.01) {
            return back()->withInput()->withErrors([
                'purchase_price' => 'Jumlah cash dan transfer harus sama dengan harga beli.',
            ]);
        }

        if ($purchaseCash > $saldoKas) {
            return back()->withInput()->withErrors([
                'purchase_cash' => 'Saldo Kas Tunai tidak cukup (Maksimal Rp ' . number_format($saldoKas, 0, ',', '.') . ').',
            ]);
        }

        if ($purchaseTransfer > $saldoAtm) {
            return back()->withInput()->withErrors([
                'purchase_transfer' => 'Saldo ATM / Rekening tidak cukup (Maksimal Rp ' . number_format($saldoAtm, 0, ',', '.') . ').',
            ]);
        }

        $this->service->store($request->validated(), $request->user());
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
        $summary = $this->finance->reportSummary();
        // Add back this unit's existing purchase cash and transfer to available balances before checking
        $saldoKas = (float) $summary['saldoKas'] + (float) $unit->purchase_cash;
        $saldoAtm = (float) $summary['saldoAtmLifetime'] + (float) $unit->purchase_transfer;

        $purchaseCash = (float) ($request->purchase_cash ?? 0);
        $purchaseTransfer = (float) ($request->purchase_transfer ?? 0);
        $purchasePrice = (float) $request->purchase_price;

        if (abs($purchaseCash + $purchaseTransfer - $purchasePrice) > 0.01) {
            return back()->withInput()->withErrors([
                'purchase_price' => 'Jumlah cash dan transfer harus sama dengan harga beli.',
            ]);
        }

        if ($purchaseCash > $saldoKas) {
            return back()->withInput()->withErrors([
                'purchase_cash' => 'Saldo Kas Tunai tidak cukup (Maksimal Rp ' . number_format($saldoKas, 0, ',', '.') . ').',
            ]);
        }

        if ($purchaseTransfer > $saldoAtm) {
            return back()->withInput()->withErrors([
                'purchase_transfer' => 'Saldo ATM / Rekening tidak cukup (Maksimal Rp ' . number_format($saldoAtm, 0, ',', '.') . ').',
            ]);
        }

        $this->service->update($unit, $request->validated());
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
}
