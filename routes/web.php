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
use App\Http\Controllers\BrandController;
use App\Http\Controllers\FundTransferController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DailyClosingController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('welcome'));

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('units', UnitController::class);
    Route::post('brands', [BrandController::class, 'store'])->name('brands.store');
    Route::delete('brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy');
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

    Route::middleware('role:superadmin,admin')->group(function () {
        Route::post('expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::put('expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
        Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
        Route::get('reports/export/{type}', [ReportController::class, 'export'])->name('reports.export');
        Route::get('reports/pdf/{type}', [ReportController::class, 'pdf'])->name('reports.pdf');
        Route::post('daily-closings', [DailyClosingController::class, 'store'])->name('daily-closings.store');
        Route::get('daily-closings/data', [DailyClosingController::class, 'getClosingData'])->name('daily-closings.data');
    });

    Route::middleware('role:superadmin')->group(function () {
        Route::post('units/{unit}/approve', [UnitController::class, 'approve'])->name('units.approve');
        Route::post('accessories/{accessory}/approve', [AccessoryController::class, 'approve'])->name('accessories.approve');
        Route::resource('admin-users', AdminUserController::class);
        Route::get('finance', [FinanceController::class, 'index'])->name('finance.index');
        Route::get('finance/export', [FinanceController::class, 'export'])->name('finance.export');
        Route::resource('capitals', CapitalController::class)->except(['index','show','create']);
        Route::patch('debts/{debt}/pay-legacy', [DebtController::class, 'markPaid'])->name('debts.mark_paid_legacy');
        Route::get('reports/cashflow', [ReportController::class, 'cashflow'])->name('reports.cashflow');
        Route::post('fund-transfers', [FundTransferController::class, 'store'])->name('fund-transfers.store');
        Route::delete('fund-transfers/{fundTransfer}', [FundTransferController::class, 'destroy'])->name('fund-transfers.destroy');
        Route::get('fund-transfers', [FundTransferController::class, 'index'])->name('fund-transfers.index');
        Route::post('daily-closings/{dailyClosing}/verify', [DailyClosingController::class, 'verify'])->name('daily-closings.verify');
        Route::post('daily-closings/{dailyClosing}/revert', [DailyClosingController::class, 'revert'])->name('daily-closings.revert');
    });
});

require __DIR__.'/auth.php';
