<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\User;
use App\Repositories\Contracts\AccessoryRepositoryInterface;
use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Repositories\Contracts\UnitRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            $total = collect($data['items'])->sum(fn($i) => $i['selling_price'] * ($i['quantity'] ?? 1));
            $paid  = collect($data['payments'])->sum('amount');

            $sale = $this->sales->create([
                'created_by'     => $actor->id,
                'invoice_number' => $this->generateInvoice(),
                'customer_name'  => $data['customer_name'] ?? null,
                'description'    => $data['description'] ?? null,
                'sale_date'      => $data['sale_date'],
                'total_price'    => $total,
                'total_paid'     => $paid,
                'profit'         => $this->calculateProfit($data['items']),
                'status'         => 'pending',
            ]);

            foreach ($data['items'] as $item) {
                $purchasePrice = !empty($item['unit_id'])
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

            $utangTotal = collect($data['payments'])->where('method', 'utang')->sum('amount');
            if ($utangTotal > 0) {
                $sale->debt()->create(['amount' => $utangTotal, 'paid_amount' => 0, 'status' => 'unpaid']);
            }

            Log::info('Sale created', ['sale_id' => $sale->id, 'invoice' => $sale->invoice_number, 'by' => $actor->id]);
            return $sale;
        });
    }

    public function approve(Sale $sale, User $actor): Sale
    {
        if ($sale->status->value !== 'pending') {
            throw new \LogicException('Hanya transaksi pending yang bisa di-approve.');
        }

        return DB::transaction(function () use ($sale, $actor) {
            foreach ($sale->items as $item) {
                if ($item->unit_id) $item->unit->update(['status' => 'sold']);
                if ($item->accessory_id) $item->accessory->decrement('stock_qty', $item->quantity);
            }
            $sale->update(['approved_by' => $actor->id, 'status' => 'approved']);

            $sale->loadMissing('payments');

            foreach ($sale->payments as $payment) {
                $methodStr = $payment->method instanceof \App\Enums\PaymentMethod
                    ? $payment->method->value
                    : $payment->method;

                if (in_array($methodStr, ['cash', 'transfer'])) {
                    \App\Models\Capital::create([
                        'created_by'     => $actor->id,
                        'description'    => 'Penjualan: ' . $sale->invoice_number,
                        'amount'         => $payment->amount,
                        'type'           => 'addition',
                        'entry_date'     => $sale->sale_date ?? now()->toDateString(),
                        'payment_method' => $methodStr,
                        'sale_id'        => $sale->id,
                    ]);
                }
            }

            Log::info('Sale approved', ['sale_id' => $sale->id, 'approved_by' => $actor->id]);
            return $sale->fresh();
        });
    }

    private function validateItems(array $items): void
    {
        foreach ($items as $item) {
            if (!empty($item['unit_id'])) {
                $unit = $this->units->findById($item['unit_id']);
                if ($unit->status->value !== 'ready') {
                    throw new \InvalidArgumentException("Unit ID {$item['unit_id']} tidak dalam status ready.");
                }
                if ($item['selling_price'] < $unit->purchase_price) {
                    $beli = 'Rp ' . number_format($unit->purchase_price, 0, ',', '.');
                    throw new \InvalidArgumentException("Harga jual tidak boleh lebih rendah dari harga beli ({$beli}).");
                }
            }
        }
    }

    private function validatePayments(array $items, array $payments): void
    {
        $total = collect($items)->sum(fn($i) => $i['selling_price'] * ($i['quantity'] ?? 1));
        $paid  = collect($payments)->sum('amount');
        if ($paid < $total) {
            $paidRp  = 'Rp ' . number_format($paid, 0, ',', '.');
            $totalRp = 'Rp ' . number_format($total, 0, ',', '.');
            throw new \InvalidArgumentException("Total pembayaran ({$paidRp}) kurang dari total penjualan ({$totalRp}).");
        }
    }

    private function calculateProfit(array $items): float
    {
        return collect($items)->sum(function ($item) {
            $buyPrice = !empty($item['unit_id'])
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
