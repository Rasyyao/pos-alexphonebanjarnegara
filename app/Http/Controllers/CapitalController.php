<?php
namespace App\Http\Controllers;
use App\Http\Requests\StoreCapitalRequest;
use App\Models\Capital;
use App\Services\CapitalService;

class CapitalController extends Controller
{
    public function __construct(private readonly CapitalService $service) {}

    public function store(StoreCapitalRequest $request)
    {
        $this->service->store($request->validated(), $request->user());
        return back()->with('success', 'Modal berhasil ditambahkan.');
    }

    public function edit(Capital $capital)
    {
        return view('finance.capital-edit', compact('capital'));
    }

    public function update(StoreCapitalRequest $request, Capital $capital)
    {
        $this->service->update($capital, $request->validated());
        return redirect()->route('finance.index')->with('success', 'Modal berhasil diperbarui.');
    }

    public function destroy(Capital $capital)
    {
        $this->service->destroy($capital);
        return back()->with('success', 'Modal berhasil dihapus.');
    }
}
