<?php

namespace App\Services;

use App\Enums\UserRole;
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
        \App\Services\DailyClosingService::assertDateNotLocked($data['sale_date']);
        $this->validateItems($data['items']);
        $this->validatePayments($data['items'], $data['payments']);

        return DB::transaction(function () use ($data, $actor) {
            $total = collect($data['items'])->sum(fn($i) => $i['selling_price'] * ($i['quantity'] ?? 1));
            $paid  = collect($data['payments'])->sum('amount');

            $isSuperadmin = $actor->role === UserRole::Superadmin;

            $sale = $this->sales->create([
                'created_by'     => $actor->id,
                'invoice_number' => $this->generateInvoice(),
                'customer_name'  => $data['customer_name'] ?? null,
                'description'    => $data['description'] ?? null,
                'sale_date'      => $data['sale_date'],
                'total_price'    => $total,
                'total_paid'     => $paid,
                'profit'         => $this->calculateProfit($data['items']),
                'status'         => $isSuperadmin ? 'approved' : 'pending',
                'approved_by'    => $isSuperadmin ? $actor->id : null,
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

            if ($isSuperadmin) {
                foreach ($sale->items as $item) {
                    if ($item->unit_id) $item->unit->update(['status' => 'sold']);
                    if ($item->accessory_id) $item->accessory->decrement('stock_qty', $item->quantity);
                }
            }

            foreach ($data['payments'] as $payment) {
                $sale->payments()->create($payment);
            }

            $utangTotal = collect($data['payments'])->where('method', 'utang')->sum('amount');
            if ($utangTotal > 0) {
                $sale->debt()->create(['amount' => $utangTotal, 'paid_amount' => 0, 'status' => 'unpaid']);
            }

            Log::info('Sale created', ['sale_id' => $sale->id, 'invoice' => $sale->invoice_number, 'by' => $actor->id, 'auto_approved' => $isSuperadmin]);
            return $sale;
        });
    }

    public function approve(Sale $sale, User $actor): Sale
    {
        \App\Services\DailyClosingService::assertDateNotLocked($sale->sale_date->toDateString());
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
            if (!empty($item['accessory_id'])) {
                $acc = $this->accessories->findById($item['accessory_id']);
                if ($acc->status !== \App\Enums\AccessoryStatus::Approved) {
                    throw new \InvalidArgumentException("Aksesoris {$acc->name} belum disetujui oleh Superadmin.");
                }
                if ($item['selling_price'] < $acc->purchase_price) {
                    $beli = 'Rp ' . number_format($acc->purchase_price, 0, ',', '.');
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
