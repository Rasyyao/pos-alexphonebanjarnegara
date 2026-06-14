<?php
namespace App\Http\Controllers;

use App\Models\Expense;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Services\ExpenseService;

class ExpenseController extends Controller
{
    public function __construct(private readonly ExpenseService $service) {}

    public function store(StoreExpenseRequest $request)
    {
        $this->service->store($request->validated(), $request->user());
        return redirect()->back()->with('success', 'Pengeluaran berhasil dicatat.');
    }

    public function update(UpdateExpenseRequest $request, Expense $expense)
    {
        $this->service->update($expense, $request->validated());
        return redirect()->back()->with('success', 'Pengeluaran berhasil diperbarui.');
    }

    public function destroy(Expense $expense)
    {
        $this->service->destroy($expense);
        return redirect()->back()->with('success', 'Pengeluaran berhasil dihapus.');
    }
}
