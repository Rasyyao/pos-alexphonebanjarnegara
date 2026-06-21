<?php

namespace Tests\Feature;

use App\Models\ProductBrand;
use App\Models\ProductModel;
use App\Models\Debt;
use App\Models\Sale;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleEditPaymentSplitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_superadmin_can_update_sale_payment_split(): void
    {
        $superadmin = $this->user('superadmin-split', 'superadmin');
        $sale = $this->sale($superadmin);
        $item = $sale->items()->first();

        $response = $this->actingAs($superadmin)->withSession(['_token' => 'test-token'])->put(route('sales.update', $sale), [
            '_token' => 'test-token',
            'sale_date' => now()->toDateString(),
            'description' => 'Pembayaran di-split ulang',
            'items' => [
                $item->id => ['selling_price' => 4500000],
            ],
            'payments' => [
                ['method' => 'cash', 'amount' => 2000000],
                ['method' => 'transfer', 'amount' => 1500000],
                ['method' => 'utang', 'amount' => 1000000],
            ],
        ]);

        $response->assertRedirect(route('sales.show', $sale));

        $sale->refresh();
        $this->assertEquals(4500000, (float) $sale->total_price);
        $this->assertEquals(4500000, (float) $sale->total_paid);
        $this->assertEquals(1500000, (float) $sale->profit);
        $this->assertEquals('Pembayaran di-split ulang', $sale->description);

        $this->assertDatabaseHas('sale_payments', [
            'sale_id' => $sale->id,
            'method' => 'cash',
            'amount' => 2000000,
        ]);
        $this->assertDatabaseHas('sale_payments', [
            'sale_id' => $sale->id,
            'method' => 'transfer',
            'amount' => 1500000,
        ]);
        $this->assertDatabaseHas('sale_payments', [
            'sale_id' => $sale->id,
            'method' => 'utang',
            'amount' => 1000000,
        ]);

        $this->assertDatabaseHas('debts', [
            'sale_id' => $sale->id,
            'amount' => 1000000,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);
    }

    public function test_sale_payment_split_must_cover_total_price(): void
    {
        $superadmin = $this->user('superadmin-underpaid', 'superadmin');
        $sale = $this->sale($superadmin);
        $item = $sale->items()->first();

        $response = $this->actingAs($superadmin)->withSession(['_token' => 'test-token'])->from(route('sales.edit', $sale))->put(route('sales.update', $sale), [
            '_token' => 'test-token',
            'sale_date' => now()->toDateString(),
            'items' => [
                $item->id => ['selling_price' => 4000000],
            ],
            'payments' => [
                ['method' => 'cash', 'amount' => 1000000],
            ],
        ]);

        $response->assertRedirect(route('sales.edit', $sale));
        $response->assertSessionHasErrors('payments');
    }

    public function test_paid_debt_sale_payment_split_cannot_be_changed(): void
    {
        $superadmin = $this->user('superadmin-paid-debt', 'superadmin');
        $sale = $this->sale($superadmin);
        $item = $sale->items()->first();

        Debt::create([
            'sale_id' => $sale->id,
            'amount' => 1000000,
            'paid_amount' => 500000,
            'status' => 'partial',
        ]);

        $response = $this->actingAs($superadmin)->withSession(['_token' => 'test-token'])->from(route('sales.edit', $sale))->put(route('sales.update', $sale), [
            '_token' => 'test-token',
            'sale_date' => now()->toDateString(),
            'items' => [
                $item->id => ['selling_price' => 4000000],
            ],
            'payments' => [
                ['method' => 'cash', 'amount' => 3000000],
                ['method' => 'utang', 'amount' => 1000000],
            ],
        ]);

        $response->assertRedirect(route('sales.edit', $sale));
        $response->assertSessionHasErrors('payments');
    }

    private function user(string $username, string $role): User
    {
        return User::create([
            'name' => $username,
            'username' => $username,
            'password' => bcrypt('password'),
            'role' => $role,
            'is_active' => true,
        ]);
    }

    private function sale(User $creator): Sale
    {
        $brand = ProductBrand::create(['name' => 'Apple ' . $creator->id]);
        $model = ProductModel::create([
            'brand_id' => $brand->id,
            'name' => 'iPhone Test',
        ]);
        $unit = Unit::create([
            'model_id' => $model->id,
            'created_by' => $creator->id,
            'unit_type' => 'baru',
            'ram' => '8GB',
            'rom' => '256GB',
            'color' => 'Black',
            'imei' => 'IMEI-SPLIT-' . $creator->id,
            'serial_number' => 'SN-SPLIT-' . $creator->id,
            'purchase_price' => 3000000,
            'purchase_date' => now()->toDateString(),
            'purchase_payment_method' => 'cash',
            'purchase_cash' => 3000000,
            'purchase_transfer' => 0,
            'status' => 'sold',
        ]);

        $sale = Sale::create([
            'created_by' => $creator->id,
            'approved_by' => $creator->id,
            'invoice_number' => 'INV-SPLIT-' . $creator->id,
            'sale_date' => now()->toDateString(),
            'total_price' => 4000000,
            'total_paid' => 4000000,
            'profit' => 1000000,
            'status' => 'approved',
        ]);

        $sale->items()->create([
            'unit_id' => $unit->id,
            'purchase_price' => 3000000,
            'selling_price' => 4000000,
            'quantity' => 1,
            'subtotal' => 4000000,
        ]);

        $sale->payments()->create([
            'method' => 'cash',
            'amount' => 4000000,
        ]);

        return $sale;
    }
}
