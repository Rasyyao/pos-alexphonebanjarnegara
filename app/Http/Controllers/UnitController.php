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
        private readonly ProductBrandRepositoryInterface $brands
    ) {}

    public function index()
    {
        $brands = $this->brands->allWithModels();
        return view('units.index', compact('brands'));
    }

    public function create()
    {
        $brands = $this->brands->allWithModels();
        return view('units.create', compact('brands'));
    }

    public function store(StoreUnitRequest $request)
    {
        $kasLiquid     = FinanceService::kasLiquidNow();
        $purchasePrice = (float) $request->validated()['purchase_price'];

        if ($purchasePrice > $kasLiquid) {
            return back()->withInput()->withErrors([
                'purchase_price' => 'Kas liquid tidak cukup (Rp ' . number_format($kasLiquid, 0, ',', '.') . '). Tambah modal terlebih dahulu.',
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
        return view('units.edit', compact('unit','brands'));
    }

    public function update(UpdateUnitRequest $request, Unit $unit)
    {
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
