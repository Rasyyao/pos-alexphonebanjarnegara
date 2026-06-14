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
    ) {}

    public function index()
    {
        return view('accessories.index');
    }

    public function create()
    {
        $categories = $this->accessories->categories();
        return view('accessories.create', compact('categories'));
    }

    public function store(StoreAccessoryRequest $request)
    {
        $data          = $request->validated();
        $kasLiquid     = FinanceService::kasLiquidNow();
        $totalCost     = (float)$data['purchase_price'] * (int)($data['stock_qty'] ?? 1);

        if ($totalCost > $kasLiquid) {
            return back()->withInput()->withErrors([
                'purchase_price' => 'Kas liquid tidak cukup (Rp ' . number_format($kasLiquid, 0, ',', '.') . '). Total beli Rp ' . number_format($totalCost, 0, ',', '.') . ' melebihi kas tersedia.',
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
        return view('accessories.edit', compact('accessory', 'categories'));
    }

    public function update(UpdateAccessoryRequest $request, Accessory $accessory)
    {
        $this->service->update($accessory, $request->validated());
        return redirect()->route('accessories.show', $accessory)->with('success', 'Aksesoris berhasil diperbarui.');
    }

    public function destroy(Accessory $accessory)
    {
        $this->service->destroy($accessory);
        return redirect()->route('accessories.index')->with('success', 'Aksesoris berhasil dihapus.');
    }
}
