<?php
namespace App\Http\Controllers;

use App\Models\Expense;
use App\Http\Requests\StoreExpenseRequest;
use App\Services\ExpenseService;

class ExpenseController extends Controller
{
    public function __construct(private readonly ExpenseService $service) {}

    public function store(StoreExpenseRequest $request)
    {
        $this->service->store($request->validated(), $request->user());
        return redirect()->back()->with('success', 'Pengeluaran berhasil dicatat.');
    }

    public function destroy(Expense $expense)
    {
        $this->service->destroy($expense);
        return redirect()->back()->with('success', 'Pengeluaran berhasil dihapus.');
    }
}
