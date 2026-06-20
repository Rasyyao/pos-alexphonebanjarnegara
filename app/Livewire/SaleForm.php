<?php

namespace App\Livewire;

use App\Models\Accessory;
use App\Models\Unit;
use App\Services\SaleService;
use Livewire\Component;

class SaleForm extends Component
{
    public array $items          = [];
    public array $payments        = [];
    public string $saleDate       = '';
    public string $customerName   = '';
    public string $description    = '';
    public ?int $savedSaleId      = null;
    public string $savedInvoice   = '';

    public function mount(): void
    {
        $this->saleDate = today()->toDateString();
        $this->items    = [['type' => 'unit', 'unit_id' => null, 'accessory_id' => null, 'selling_price' => 0, 'quantity' => 1]];
        $this->payments = [['method' => 'cash', 'amount' => 0]];
    }

    public function addItem(): void
    {
        $this->items[] = ['type' => 'unit', 'unit_id' => null, 'accessory_id' => null, 'selling_price' => 0, 'quantity' => 1];
    }

    /**
     * Auto-fill the selling price from the chosen product so the cashier
     * never types it from scratch:
     *   - accessory → its retail selling_price
     *   - unit (phone) → its purchase_price as an editable floor (phones are negotiated)
     * Switching the row's type clears the previous selection.
     */
    public function updated(string $name, $value): void
    {
        if (preg_match('/^items\.(\d+)\.accessory_id$/', $name, $m)) {
            $i = (int) $m[1];
            $acc = $value ? Accessory::find($value) : null;
            $this->items[$i]['selling_price'] = $acc ? (float) $acc->selling_price : 0;
        } elseif (preg_match('/^items\.(\d+)\.unit_id$/', $name, $m)) {
            $i = (int) $m[1];
            $unit = $value ? Unit::find($value) : null;
            $this->items[$i]['selling_price'] = $unit ? (float) $unit->purchase_price : 0;
        } elseif (preg_match('/^items\.(\d+)\.type$/', $name, $m)) {
            $i = (int) $m[1];
            $this->items[$i]['unit_id']       = null;
            $this->items[$i]['accessory_id']  = null;
            $this->items[$i]['selling_price'] = 0;
            $this->items[$i]['quantity']      = 1;
        }
    }

    /** Cost (purchase price) of the product chosen on a row — for the read-only "Harga Beli" hint. */
    public function purchasePriceFor(array $item): float
    {
        if (($item['type'] ?? 'unit') === 'unit' && !empty($item['unit_id'])) {
            return (float) optional(Unit::find($item['unit_id']))->purchase_price;
        }
        if (($item['type'] ?? '') === 'accessory' && !empty($item['accessory_id'])) {
            return (float) optional(Accessory::find($item['accessory_id']))->purchase_price;
        }
        return 0;
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

    public function removePayment(int $index): void
    {
        unset($this->payments[$index]);
        $this->payments = array_values($this->payments);
    }

    public function getTotal(): float
    {
        return collect($this->items)->sum(fn($i) => ($i['selling_price'] ?? 0) * ($i['quantity'] ?? 1));
    }

    public function getRemainder(): float
    {
        return $this->getTotal() - collect($this->payments)->sum(fn($p) => $p['amount'] ?? 0);
    }

    public function submit(SaleService $service): void
    {
        $this->validate([
            'saleDate'               => 'required|date',
            'items'                  => 'required|array|min:1',
            'items.*.selling_price'  => 'required|numeric|min:0',
            'items.*.quantity'       => 'required|integer|min:1',
            'payments'               => 'required|array|min:1',
            'payments.*.method'      => 'required|in:cash,transfer,utang',
            'payments.*.amount'      => 'required|numeric|min:1',
        ]);

        $items = collect($this->items)->map(function ($item) {
            return [
                'unit_id'       => ($item['type'] === 'unit' && $item['unit_id']) ? (int) $item['unit_id'] : null,
                'accessory_id'  => ($item['type'] === 'accessory' && $item['accessory_id']) ? (int) $item['accessory_id'] : null,
                'selling_price' => (float) $item['selling_price'],
                'quantity'      => (int) $item['quantity'],
            ];
        })->toArray();

        try {
            $sale = $service->create([
                'sale_date'     => $this->saleDate,
                'customer_name' => trim($this->customerName) ?: null,
                'description'   => trim($this->description) ?: null,
                'items'         => $items,
                'payments'      => $this->payments,
            ], auth()->user());

            // Show success modal with print option — no redirect
            $this->savedSaleId    = $sale->id;
            $this->savedInvoice   = $sale->invoice_number;
        } catch (\Exception $e) {
            $this->addError('general', $e->getMessage());
        }
    }

    public function render()
    {
        $readyUnits  = Unit::with('model.brand')->where('status', 'ready')->get();
        $accessories = Accessory::where('status', \App\Enums\AccessoryStatus::Approved)->where('stock_qty', '>', 0)->get();

        return view('livewire.sale-form', [
            'readyUnits'  => $readyUnits,
            'accessories' => $accessories,
            'total'       => $this->getTotal(),
            'remainder'   => $this->getRemainder(),
        ]);
    }
}
