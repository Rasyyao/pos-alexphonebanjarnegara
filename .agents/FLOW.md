# FLOW.md — Application Flow & Feature Logic

> Read this before building any controller action, route, or feature.

---

## Route map

```php
// routes/web.php

Route::middleware('auth')->group(function () {

    // Dashboard — both roles
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Stock — both roles
    Route::resource('units', UnitController::class);
    Route::resource('accessories', AccessoryController::class);
    Route::get('reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
    Route::get('reports/stock/export', [ReportController::class, 'exportStock'])
         ->name('reports.stock.export');

    // Sales — both roles (approve gate enforced inside controller)
    Route::resource('sales', SaleController::class)->except(['destroy', 'edit', 'update']);
    Route::post('sales/{sale}/approve', [SaleController::class, 'approve'])
         ->name('sales.approve');
    Route::get('sales/{sale}/print', [SaleController::class, 'printReceipt'])
         ->name('sales.print');

    // Finance report — admin sees daily only
    Route::get('reports/finance/daily', [ReportController::class, 'dailyFinance'])
         ->name('reports.finance.daily');

    // Superadmin only
    Route::middleware('role:superadmin')->group(function () {
        Route::resource('admin-users', AdminUserController::class);
        Route::get('finance', [FinanceController::class, 'index'])->name('finance.index');
        Route::resource('capitals', CapitalController::class)->except('show');
        Route::patch('debts/{debt}/pay', [DebtController::class, 'markPaid'])
             ->name('debts.pay');
        Route::get('reports/finance/full', [ReportController::class, 'fullFinance'])
             ->name('reports.finance.full');
        Route::get('reports/export/{type}', [ReportController::class, 'export'])
             ->name('reports.export');
    });

});
```

---

## Role access matrix

| Page / Action | superadmin | admin |
|---|:---:|:---:|
| Dashboard | ✓ | ✓ |
| Units: view & CRUD | ✓ | ✓ |
| Accessories: view & CRUD | ✓ | ✓ |
| Input penjualan | ✓ | ✓ |
| Approve penjualan | ✓ | ✗ |
| Laporan keuangan harian | ✓ | ✓ (read-only) |
| Laporan keuangan lengkap | ✓ | ✗ |
| Export Excel (all) | ✓ | ✗ |
| Export stok harian | ✓ | ✓ |
| Kelola admin (CRUD) | ✓ | ✗ |
| Halaman keuangan & modal | ✓ | ✗ |
| Kelola utang | ✓ | ✗ |

---

## Feature flows

### Sale creation flow

```
Admin fills SaleForm (Livewire)
  → picks unit(s) from dropdown — only status='ready' units shown
  → adds accessories (expandable rows)
  → system shows purchase_price per item (read-only)
  → admin inputs selling_price per item
  → live total updates via Livewire
  → admin selects payment(s): cash / transfer / utang (multi-method split)
  → admin inputs sale_date
  → clicks Simpan

SaleController::store()
  → dispatches to SaleService::create($data, $user)
  → SaleService validates:
      [ ] at least 1 unit with status='ready'
      [ ] selling_price >= purchase_price per item (no loss sale)
      [ ] sum(payments.amount) >= sum(items.subtotal) (no negative balance)
  → DB::transaction:
      [ ] insert sale (status=pending)
      [ ] insert sale_items (snapshot purchase_price)
      [ ] insert sale_payments
      [ ] if any payment.method='utang' → insert debt record
      [ ] calculate & store profit
  → returns Sale model

Controller redirects to sales.show
  → user offered: cetak struk? (button triggers sales.print in new tab)
```

### Sale approve flow (superadmin only)

```
Superadmin clicks Acc on pending sale

SaleController::approve()
  → Gate::authorize('superadmin') — abort 403 if not
  → SaleService::approve($sale):
      [ ] check sale.status === 'pending' — throw if not
      [ ] DB::transaction:
          [ ] set units.status = 'sold' for all unit items
          [ ] decrement accessories.stock_qty for all accessory items
          [ ] set sale.approved_by = auth()->id()
          [ ] set sale.status = 'approved'
  → redirects back with success flash
```

### Print struk flow

```
GET sales/{sale}/print
  → returns Blade view: resources/views/sales/print.blade.php
  → layout: print-only (no sidebar, no nav)
  → content: invoice_number, sale_date, items table, payment breakdown
  → page has @media print CSS, window.print() fires on load
```

### Stock filter flow (Livewire StockFilter)

```
User changes brand dropdown
  → Livewire wire:model updates $brand_id
  → computed models list re-queries product_models where brand_id = $brand_id
  → model dropdown resets + repopulates
  → unit list re-renders filtered by all active filters
  → filters: brand, model, color, ram, rom, unit_type, status
```

### Payment split logic

```
// In SaleForm Livewire component
$payments = [
    ['method' => 'cash',     'amount' => 500000],
    ['method' => 'transfer', 'amount' => 300000],
    ['method' => 'utang',    'amount' => 200000],
];
// Total items: 1.000.000
// sum(payments) must equal total_price — validated in SaleService
// UI shows running remainder: total_price - sum(entered payments)
```

### Debt payment flow

```
Superadmin on Finance page, DebtList Livewire
  → clicks Tandai Lunas on a debt row

DebtController::markPaid()
  → validates debt.status !== 'paid'
  → sets debt.paid_amount = debt.amount
  → sets debt.status = 'paid'
  → redirects back
```

---

## Livewire components

| Class | View | Responsibility |
|---|---|---|
| `StockFilter` | `livewire/stock-filter.blade.php` | Cascading dropdowns + unit list |
| `SaleForm` | `livewire/sale-form.blade.php` | Expandable items, split payments, live total |
| `DashboardStats` | `livewire/dashboard-stats.blade.php` | KPI cards + 7-day chart |
| `DebtList` | `livewire/debt-list.blade.php` | Debt table + inline pay action |

---

## Blade view structure

```
resources/views/
  layouts/
    app.blade.php       — sidebar nav (role-aware), @yield('content')
    auth.blade.php      — login-only layout
    print.blade.php     — bare layout for struk printing
  dashboard/
    index.blade.php
  units/
    index.blade.php     — uses <livewire:stock-filter />
    create.blade.php
    edit.blade.php
  accessories/
    index.blade.php
    create.blade.php
    edit.blade.php
  sales/
    index.blade.php
    create.blade.php    — uses <livewire:sale-form />
    show.blade.php      — detail + cetak button
    print.blade.php     — struk only (print layout)
  finance/
    index.blade.php     — superadmin: modal, aset, utang, statistik
  reports/
    index.blade.php
    stock.blade.php
    daily.blade.php
  admin-users/
    index.blade.php
    create.blade.php
    edit.blade.php
  components/
    stat-card.blade.php
    table.blade.php
    filter-bar.blade.php
```

---

## Invoice number generation

```php
// SaleService — called inside DB::transaction
private function generateInvoice(): string
{
    $date  = now()->format('Ymd');
    $count = Sale::whereDate('created_at', today())->lockForUpdate()->count() + 1;
    return 'INV-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
}
// lockForUpdate prevents duplicate invoice on concurrent inserts
```

---

## Report & export logic

| Report | Scope | Export class |
|---|---|---|
| Stok hari ini | units purchased today, all accessories | `StockExport` |
| Stok 7 hari | units purchased in last 7 days | `StockExport` |
| Opname stok | all units grouped by status + accessories | `OpnameExport` |
| Penjualan harian | sales approved on a given date, itemized | `SalesExport` |
| Keuangan lengkap | income, expenses (capitals), net profit, debt | `FinanceExport` |

All export classes implement `FromCollection, WithHeadings, WithStyles, ShouldAutoSize`.

---

## Dashboard stat queries reference

See `DATABASE.md` → "Key queries" section for the exact Eloquent queries
that power each dashboard KPI and chart.

---

## Sidebar nav (role-conditional)

```blade
{{-- layouts/app.blade.php --}}
<nav>
  <a href="{{ route('dashboard') }}">Home</a>
  <a href="{{ route('units.index') }}">Stok Barang</a>
  <a href="{{ route('accessories.index') }}">Stok Aksesoris</a>
  <a href="{{ route('sales.index') }}">Penjualan</a>
  <a href="{{ route('reports.finance.daily') }}">Laporan Harian</a>

  @if(auth()->user()->role === 'superadmin')
    <a href="{{ route('finance.index') }}">Keuangan</a>
    <a href="{{ route('reports.finance.full') }}">Laporan Lengkap</a>
    <a href="{{ route('admin-users.index') }}">Kelola Admin</a>
  @endif
</nav>
```
