<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleShowVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_estimasi_margin_is_hidden_from_admin(): void
    {
        $admin = $this->user('admin-margin', 'admin');
        $sale = $this->sale($admin);

        $this->actingAs($admin)
            ->get(route('sales.show', $sale))
            ->assertOk()
            ->assertDontSee('Estimasi Margin')
            ->assertDontSee('Untung 25% dari total penjualan');
    }

    public function test_estimasi_margin_is_visible_to_superadmin(): void
    {
        $superadmin = $this->user('superadmin-margin', 'superadmin');
        $sale = $this->sale($superadmin);

        $this->actingAs($superadmin)
            ->get(route('sales.show', $sale))
            ->assertOk()
            ->assertSee('Estimasi Margin')
            ->assertSee('Untung 25% dari total penjualan');
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
        $brand = \App\Models\ProductBrand::create(['name' => 'Apple-' . $creator->id]);
        $model = \App\Models\ProductModel::create([
            'brand_id' => $brand->id,
            'name' => 'iPhone Test',
        ]);
        $unit = \App\Models\Unit::create([
            'model_id' => $model->id,
            'created_by' => $creator->id,
            'unit_type' => 'baru',
            'ram' => '8GB',
            'rom' => '256GB',
            'color' => 'Black',
            'imei' => 'IMEI-' . $creator->id,
            'serial_number' => 'SN-' . $creator->id,
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
            'invoice_number' => 'INV-' . $creator->id,
            'customer_name' => 'Customer',
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
