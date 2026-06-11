<?php
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\AccessoryController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\CapitalController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('welcome'));

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('units', UnitController::class);
    Route::resource('accessories', AccessoryController::class);
    Route::get('reports/finance', [ReportController::class, 'finance'])->name('reports.finance');
    Route::get('reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
    Route::get('reports/stock/opname', [ReportController::class, 'stockOpname'])->name('reports.stock.opname');

    Route::get('debts', [DebtController::class, 'index'])->name('debts.index');
    Route::post('debts/{debt}/pay', [DebtController::class, 'pay'])->name('debts.pay');

    Route::get('sales/verify', [SaleController::class, 'verify'])->middleware('role:superadmin')->name('sales.verify');
    Route::resource('sales', SaleController::class)->except(['edit','update']);
    Route::post('sales/{sale}/approve', [SaleController::class, 'approve'])->name('sales.approve');
    Route::get('sales/{sale}/print', [SaleController::class, 'printReceipt'])->name('sales.print');
    Route::get('sales/{sale}/edit', [SaleController::class, 'edit'])->middleware('role:superadmin')->name('sales.edit');
    Route::put('sales/{sale}', [SaleController::class, 'update'])->middleware('role:superadmin')->name('sales.update');

    Route::middleware('role:superadmin')->group(function () {
        Route::resource('admin-users', AdminUserController::class);
        Route::get('finance', [FinanceController::class, 'index'])->name('finance.index');
        Route::get('finance/export', [FinanceController::class, 'export'])->name('finance.export');
        Route::resource('capitals', CapitalController::class)->except(['index','show','create']);
        Route::post('expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
        Route::patch('debts/{debt}/pay-legacy', [DebtController::class, 'markPaid'])->name('debts.mark_paid_legacy');
        Route::get('reports/export/{type}', [ReportController::class, 'export'])->name('reports.export');
        Route::get('reports/pdf/{type}', [ReportController::class, 'pdf'])->name('reports.pdf');
        Route::get('reports/cashflow', [ReportController::class, 'cashflow'])->name('reports.cashflow');
    });
});

require __DIR__.'/auth.php';
