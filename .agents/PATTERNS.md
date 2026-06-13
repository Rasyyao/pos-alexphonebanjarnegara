# PATTERNS.md — Code Architecture & Patterns

> Read this before creating any new class, file, or feature.
> This project uses Repository–Service pattern throughout.

---

## Directory structure

```
app/
  Http/
    Controllers/         — thin, delegate to Services
    Requests/            — one FormRequest per action
    Middleware/
      RoleMiddleware.php
  Livewire/              — Livewire component classes
  Models/                — Eloquent models only (no logic)
  Repositories/          — DB query abstraction (per model)
    Contracts/           — interfaces
  Services/              — all business logic
  Exports/               — Laravel Excel export classes
  Enums/                 — PHP 8.5 backed enums (role, status, etc.)
  Observers/             — model observers (SaleObserver, SaleItemObserver)

database/
  migrations/
  seeders/
  factories/

resources/views/         — see FLOW.md for full tree
routes/
  web.php
```

---

## Repository–Service pattern

### Rule
- **Repository** = only DB queries. No business logic. Returns Eloquent models/collections.
- **Service** = business logic, validation, orchestration. Calls repositories. Wrapped in `DB::transaction` when writing.
- **Controller** = HTTP layer only. Validates request (FormRequest), calls Service, returns response.
- **Livewire component** = UI state + user interaction. Calls Services directly. No raw DB queries.

### Repository interface

```php
// app/Repositories/Contracts/UnitRepositoryInterface.php
namespace App\Repositories\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Unit;

interface UnitRepositoryInterface
{
    public function allReady(array $filters = []): LengthAwarePaginator;
    public function findById(int $id): Unit;
    public function create(array $data): Unit;
    public function update(Unit $unit, array $data): Unit;
    public function delete(Unit $unit): void;
    public function latestReady(int $limit = 5): \Illuminate\Support\Collection;
}
```

### Repository implementation

```php
// app/Repositories/UnitRepository.php
namespace App\Repositories;

use App\Models\Unit;
use App\Repositories\Contracts\UnitRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class UnitRepository implements UnitRepositoryInterface
{
    public function allReady(array $filters = []): LengthAwarePaginator
    {
        return Unit::with('model.brand')
            ->where('status', 'ready')
            ->when($filters['brand_id'] ?? null,
                fn($q, $v) => $q->whereHas('model', fn($q) => $q->where('brand_id', $v)))
            ->when($filters['model_id'] ?? null,
                fn($q, $v) => $q->where('model_id', $v))
            ->when($filters['unit_type'] ?? null,
                fn($q, $v) => $q->where('unit_type', $v))
            ->when($filters['ram'] ?? null,
                fn($q, $v) => $q->where('ram', $v))
            ->when($filters['rom'] ?? null,
                fn($q, $v) => $q->where('rom', $v))
            ->when($filters['color'] ?? null,
                fn($q, $v) => $q->where('color', $v))
            ->latest()
            ->paginate(20);
    }

    public function findById(int $id): Unit
    {
        return Unit::with('model.brand')->findOrFail($id);
    }

    public function create(array $data): Unit
    {
        return Unit::create($data);
    }

    public function update(Unit $unit, array $data): Unit
    {
        $unit->update($data);
        return $unit->fresh();
    }

    public function delete(Unit $unit): void
    {
        $unit->delete();
    }

    public function latestReady(int $limit = 5): \Illuminate\Support\Collection
    {
        return Unit::with('model.brand')
            ->where('status', 'ready')
            ->latest()
            ->limit($limit)
            ->get();
    }
}
```

### Service (calls repository, owns business logic)

```php
// app/Services/UnitService.php
namespace App\Services;

use App\Models\Unit;
use App\Models\User;
use App\Repositories\Contracts\UnitRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UnitService
{
    public function __construct(
        private readonly UnitRepositoryInterface $units
    ) {}

    public function store(array $validated, User $actor, ?UploadedFile $photo = null): Unit
    {
        if ($photo) {
            $validated['photo_path'] = $photo->store('units', 'public');
        }
        $validated['created_by'] = $actor->id;

        return $this->units->create($validated);
    }

    public function update(Unit $unit, array $validated, ?UploadedFile $photo = null): Unit
    {
        if ($photo) {
            if ($unit->photo_path) {
                Storage::disk('public')->delete($unit->photo_path);
            }
            $validated['photo_path'] = $photo->store('units', 'public');
        }

        return $this->units->update($unit, $validated);
    }

    public function destroy(Unit $unit): void
    {
        if ($unit->status === 'sold') {
            throw new \LogicException('Unit yang sudah terjual tidak dapat dihapus.');
        }

        if ($unit->photo_path) {
            Storage::disk('public')->delete($unit->photo_path);
        }

        $this->units->delete($unit);
    }
}
```

### Controller (thin — only HTTP concern)

```php
// app/Http/Controllers/UnitController.php
namespace App\Http\Controllers;

use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Models\Unit;
use App\Services\UnitService;

class UnitController extends Controller
{
    public function __construct(private readonly UnitService $service) {}

    public function index()
    {
        return view('units.index');  // filter handled by Livewire StockFilter
    }

    public function create()
    {
        return view('units.create');
    }

    public function store(StoreUnitRequest $request)
    {
        $this->service->store(
            $request->validated(),
            $request->user(),
            $request->file('photo')
        );

        return redirect()->route('units.index')
            ->with('success', 'Unit berhasil ditambahkan.');
    }

    public function edit(Unit $unit)
    {
        return view('units.edit', compact('unit'));
    }

    public function update(UpdateUnitRequest $request, Unit $unit)
    {
        $this->service->update($unit, $request->validated(), $request->file('photo'));

        return redirect()->route('units.index')
            ->with('success', 'Unit berhasil diperbarui.');
    }

    public function destroy(Unit $unit)
    {
        $this->service->destroy($unit);

        return redirect()->route('units.index')
            ->with('success', 'Unit berhasil dihapus.');
    }
}
```

---

## SaleService — full example (most complex service)

```php
// app/Services/SaleService.php
namespace App\Services;

use App\Models\Sale;
use App\Models\User;
use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Repositories\Contracts\UnitRepositoryInterface;
use App\Repositories\Contracts\AccessoryRepositoryInterface;
use Illuminate\Support\Facades\DB;

class SaleService
{
    public function __construct(
        private readonly SaleRepositoryInterface $sales,
        private readonly UnitRepositoryInterface $units,
        private readonly AccessoryRepositoryInterface $accessories,
    ) {}

    public function create(array $data, User $actor): Sale
    {
        $this->validateItems($data['items']);
        $this->validatePayments($data['items'], $data['payments']);

        return DB::transaction(function () use ($data, $actor) {
            $total = collect($data['items'])->sum(fn($i) => $i['selling_price'] * $i['quantity']);
            $paid  = collect($data['payments'])->sum('amount');

            $sale = $this->sales->create([
                'created_by'     => $actor->id,
                'invoice_number' => $this->generateInvoice(),
                'sale_date'      => $data['sale_date'],
                'total_price'    => $total,
                'total_paid'     => $paid,
                'profit'         => $this->calculateProfit($data['items']),
                'status'         => 'pending',
            ]);

            foreach ($data['items'] as $item) {
                $purchasePrice = $item['unit_id']
                    ? $this->units->findById($item['unit_id'])->purchase_price
                    : $this->accessories->findById($item['accessory_id'])->purchase_price;

                $sale->items()->create([
                    'unit_id'        => $item['unit_id'] ?? null,
                    'accessory_id'   => $item['accessory_id'] ?? null,
                    'purchase_price' => $purchasePrice,
                    'selling_price'  => $item['selling_price'],
                    'quantity'       => $item['quantity'] ?? 1,
                    'subtotal'       => $item['selling_price'] * ($item['quantity'] ?? 1),
                ]);
            }

            foreach ($data['payments'] as $payment) {
                $sale->payments()->create($payment);
            }

            $utangTotal = collect($data['payments'])
                ->where('method', 'utang')
                ->sum('amount');

            if ($utangTotal > 0) {
                $sale->debt()->create([
                    'amount'      => $utangTotal,
                    'paid_amount' => 0,
                    'status'      => 'unpaid',
                ]);
            }

            return $sale;
        });
    }

    public function approve(Sale $sale, User $actor): Sale
    {
        if ($sale->status !== 'pending') {
            throw new \LogicException('Hanya transaksi pending yang bisa di-approve.');
        }

        return DB::transaction(function () use ($sale, $actor) {
            foreach ($sale->items as $item) {
                if ($item->unit_id) {
                    $item->unit->update(['status' => 'sold']);
                }
                if ($item->accessory_id) {
                    $item->accessory->decrement('stock_qty', $item->quantity);
                }
            }

            $sale->update([
                'approved_by' => $actor->id,
                'status'      => 'approved',
            ]);

            return $sale->fresh();
        });
    }

    private function validateItems(array $items): void
    {
        foreach ($items as $item) {
            if (isset($item['unit_id'])) {
                $unit = $this->units->findById($item['unit_id']);
                if ($unit->status !== 'ready') {
                    throw new \InvalidArgumentException("Unit ID {$item['unit_id']} tidak dalam status ready.");
                }
                if ($item['selling_price'] < $unit->purchase_price) {
                    throw new \InvalidArgumentException("Harga jual tidak boleh lebih rendah dari harga beli.");
                }
            }
        }
    }

    private function validatePayments(array $items, array $payments): void
    {
        $total = collect($items)->sum(fn($i) => $i['selling_price'] * ($i['quantity'] ?? 1));
        $paid  = collect($payments)->sum('amount');

        if ($paid < $total) {
            throw new \InvalidArgumentException("Total pembayaran ({$paid}) kurang dari total penjualan ({$total}).");
        }
    }

    private function calculateProfit(array $items): float
    {
        return collect($items)->sum(function ($item) {
            $buyPrice = $item['unit_id']
                ? $this->units->findById($item['unit_id'])->purchase_price
                : $this->accessories->findById($item['accessory_id'])->purchase_price;
            return ($item['selling_price'] - $buyPrice) * ($item['quantity'] ?? 1);
        });
    }

    private function generateInvoice(): string
    {
        $date  = now()->format('Ymd');
        $count = Sale::whereDate('created_at', today())->lockForUpdate()->count() + 1;
        return 'INV-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
```

---

## FormRequest example

```php
// app/Http/Requests/StoreSaleRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gate handled by middleware
    }

    public function rules(): array
    {
        return [
            'sale_date'              => ['required', 'date'],
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.unit_id'        => ['nullable', 'exists:units,id', 'distinct'],
            'items.*.accessory_id'   => ['nullable', 'exists:accessories,id'],
            'items.*.selling_price'  => ['required', 'numeric', 'min:0'],
            'items.*.quantity'       => ['required', 'integer', 'min:1'],
            'payments'               => ['required', 'array', 'min:1'],
            'payments.*.method'      => ['required', 'in:cash,transfer,utang'],
            'payments.*.amount'      => ['required', 'numeric', 'min:1'],
        ];
    }
    // Cross-field (selling >= purchase) validated in SaleService, not here
}
```

---

## Enums (PHP 8.5)

```php
// app/Enums/UnitStatus.php
namespace App\Enums;

enum UnitStatus: string
{
    case Ready    = 'ready';
    case Sold     = 'sold';
    case Returned = 'returned';
}

// app/Enums/SaleStatus.php
enum SaleStatus: string
{
    case Pending  = 'pending';
    case Approved = 'approved';
    case Cancelled = 'cancelled';
}

// app/Enums/PaymentMethod.php
enum PaymentMethod: string
{
    case Cash     = 'cash';
    case Transfer = 'transfer';
    case Utang    = 'utang';
}

// app/Enums/UserRole.php
enum UserRole: string
{
    case Superadmin = 'superadmin';
    case Admin      = 'admin';
}
```

Cast enums in models:
```php
// Unit.php
protected $casts = [
    'status'        => UnitStatus::class,
    'unit_type'     => \App\Enums\UnitType::class,
    'purchase_price'=> 'decimal:2',
    'purchase_date' => 'date',
];
```

---

## Observer example

```php
// app/Observers/SaleItemObserver.php
namespace App\Observers;

use App\Models\SaleItem;

class SaleItemObserver
{
    public function saving(SaleItem $item): void
    {
        // Enforce: exactly one of unit_id or accessory_id must be set
        $hasUnit      = ! is_null($item->unit_id);
        $hasAccessory = ! is_null($item->accessory_id);

        if ($hasUnit && $hasAccessory) {
            throw new \LogicException('SaleItem cannot have both unit_id and accessory_id.');
        }

        if (! $hasUnit && ! $hasAccessory) {
            throw new \LogicException('SaleItem must have either unit_id or accessory_id.');
        }
    }
}

// Register in AppServiceProvider::boot()
SaleItem::observe(SaleItemObserver::class);
```

---

## Binding repositories in AppServiceProvider

```php
// app/Providers/AppServiceProvider.php
use App\Repositories\Contracts\UnitRepositoryInterface;
use App\Repositories\UnitRepository;
use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Repositories\SaleRepository;
// ... etc

public function register(): void
{
    $this->app->bind(UnitRepositoryInterface::class, UnitRepository::class);
    $this->app->bind(SaleRepositoryInterface::class, SaleRepository::class);
    $this->app->bind(AccessoryRepositoryInterface::class, AccessoryRepository::class);
}
```

---

## Export class example

```php
// app/Exports/SalesExport.php
namespace App\Exports;

use App\Models\Sale;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    public function __construct(private string $date) {}

    public function collection(): Collection
    {
        return Sale::with(['items.unit.model.brand', 'payments'])
            ->approved()
            ->whereDate('sale_date', $this->date)
            ->get()
            ->map(fn($sale) => [
                'invoice'     => $sale->invoice_number,
                'tanggal'     => $sale->sale_date->format('d/m/Y'),
                'total'       => $sale->total_price,
                'bayar'       => $sale->total_paid,
                'laba'        => $sale->profit,
                'metode'      => $sale->payments->pluck('method')->join(', '),
                'status'      => $sale->status->value,
            ]);
    }

    public function headings(): array
    {
        return ['No. Invoice', 'Tanggal', 'Total Harga', 'Total Bayar', 'Laba', 'Metode', 'Status'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
```

---

## Livewire component example

```php
// app/Livewire/SaleForm.php
namespace App\Livewire;

use App\Models\Unit;
use App\Models\Accessory;
use App\Services\SaleService;
use Livewire\Component;

class SaleForm extends Component
{
    public array $items    = [['unit_id' => null, 'accessory_id' => null,
                                'selling_price' => 0, 'quantity' => 1]];
    public array $payments = [['method' => 'cash', 'amount' => 0]];
    public string $saleDate = '';

    public function addItem(): void
    {
        $this->items[] = ['unit_id' => null, 'accessory_id' => null,
                           'selling_price' => 0, 'quantity' => 1];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function addPayment(): void
    {
        $this->payments[] = ['method' => 'cash', 'amount' => 0];
    }

    public function getTotal(): float
    {
        return collect($this->items)->sum(fn($i) => ($i['selling_price'] ?? 0) * ($i['quantity'] ?? 1));
    }

    public function getRemainder(): float
    {
        return $this->getTotal() - collect($this->payments)->sum('amount');
    }

    public function submit(SaleService $service): void
    {
        $this->validate([
            'saleDate'               => 'required|date',
            'items'                  => 'required|array|min:1',
            'items.*.selling_price'  => 'required|numeric|min:0',
            'items.*.quantity'       => 'required|integer|min:1',
            'payments.*.method'      => 'required|in:cash,transfer,utang',
            'payments.*.amount'      => 'required|numeric|min:1',
        ]);

        $sale = $service->create([
            'sale_date' => $this->saleDate,
            'items'     => $this->items,
            'payments'  => $this->payments,
        ], auth()->user());

        $this->redirect(route('sales.show', $sale));
    }

    public function render()
    {
        return view('livewire.sale-form', [
            'readyUnits'   => Unit::with('model.brand')->where('status', 'ready')->get(),
            'accessories'  => Accessory::where('stock_qty', '>', 0)->get(),
        ]);
    }
}
```

---

## Naming conventions

| Artifact | Pattern | Example |
|---|---|---|
| Model | Singular PascalCase | `SaleItem` |
| Repository interface | `{Model}RepositoryInterface` | `SaleRepositoryInterface` |
| Repository class | `{Model}Repository` | `SaleRepository` |
| Service | `{Domain}Service` | `SaleService`, `ReportService` |
| Controller | `{Model}Controller` (plural route, singular class ok) | `SaleController` |
| FormRequest | `{Action}{Model}Request` | `StoreSaleRequest` |
| Export | `{Subject}Export` | `FinanceExport` |
| Enum | PascalCase, backed string | `SaleStatus` |
| Livewire | PascalCase class, kebab tag | `<livewire:sale-form />` |
| Observer | `{Model}Observer` | `SaleObserver` |

---

## What NOT to do

- Do NOT query the DB directly inside Controllers — always go through Repository.
- Do NOT write business rules inside Blade templates or FormRequests.
- Do NOT use `Auth::id()` inside Models — inject via Service.
- Do NOT hardcode enum string values in logic — use Enum cases (`SaleStatus::Approved`).
- Do NOT use `dd()` / `dump()` / `var_dump()` in any committed code.
- Do NOT return raw query results from Controllers — wrap in Resources or pass to views.
