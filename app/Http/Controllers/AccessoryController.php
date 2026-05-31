<?php
namespace App\Http\Controllers;
use App\Http\Requests\StoreAccessoryRequest;
use App\Http\Requests\UpdateAccessoryRequest;
use App\Models\Accessory;
use App\Repositories\Contracts\AccessoryRepositoryInterface;
use App\Services\AccessoryService;

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
        return view('accessories.create');
    }

    public function store(StoreAccessoryRequest $request)
    {
        $this->service->store($request->validated());
        return redirect()->route('accessories.index')->with('success', 'Aksesoris berhasil ditambahkan.');
    }

    public function show(Accessory $accessory)
    {
        return view('accessories.show', compact('accessory'));
    }

    public function edit(Accessory $accessory)
    {
        return view('accessories.edit', compact('accessory'));
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
