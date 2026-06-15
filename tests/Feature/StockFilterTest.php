<?php

namespace Tests\Feature;

use App\Livewire\StockFilter;
use App\Models\ProductBrand;
use App\Models\ProductModel;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StockFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_filter_logic(): void
    {
        $admin = User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'password' => bcrypt('password'),
            'role' => 'superadmin',
            'is_active' => true,
        ]);

        $oppo = ProductBrand::create(['name' => 'Oppo']);
        $oppoModel = ProductModel::create(['brand_id' => $oppo->id, 'name' => 'Opopo c31']);

        $apple = ProductBrand::create(['name' => 'Apple']);
        $appleModel = ProductModel::create(['brand_id' => $apple->id, 'name' => 'iPhone 15']);

        Unit::create([
            'model_id' => $oppoModel->id,
            'created_by' => $admin->id,
            'unit_type' => 'baru',
            'grade' => 'A',
            'ram' => '4',
            'rom' => '128',
            'color' => 'biru',
            'imei' => '123456789012345',
            'serial_number' => 'SN-OPPO-C31',
            'purchase_price' => 1500000,
            'purchase_date' => now()->toDateString(),
            'status' => 'ready',
        ]);

        Unit::create([
            'model_id' => $appleModel->id,
            'created_by' => $admin->id,
            'unit_type' => 'baru',
            'grade' => 'A',
            'ram' => '6',
            'rom' => '128',
            'color' => 'Black',
            'imei' => '543210987654321',
            'serial_number' => 'SN-IPHONE-15',
            'purchase_price' => 13000000,
            'purchase_date' => now()->toDateString(),
            'status' => 'ready',
        ]);

        // Test with empty filters (shows both)
        Livewire::test(StockFilter::class)
            ->assertSee('Opopo c31')
            ->assertSee('iPhone 15')
            // Filter by Brand: Apple
            ->set('brand_id', (string)$apple->id)
            ->assertSee('iPhone 15')
            ->assertDontSee('Opopo c31')
            // Filter by Search: eec (should see nothing)
            ->set('search', 'eec')
            ->assertDontSee('iPhone 15')
            ->assertDontSee('Opopo c31')
            // Filter by search: Apple iPhone 15 (should see iPhone 15)
            ->set('search', 'Apple iPhone 15')
            ->assertSee('iPhone 15')
            ->assertDontSee('Opopo c31')
            // Filter by search: Opopo c31 (should see Opopo c31)
            ->set('brand_id', '')
            ->set('search', 'Opopo c31')
            ->assertSee('Opopo c31')
            ->assertDontSee('iPhone 15')
            // Reset filters
            ->call('resetFilters')
            ->assertSee('Opopo c31')
            ->assertSee('iPhone 15');
    }

    public function test_can_create_unit_with_formatted_currency_prices(): void
    {
        $admin = User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'password' => bcrypt('password'),
            'role' => 'superadmin',
            'is_active' => true,
        ]);

        // Deposit initial capital so there is enough balance
        \App\Models\Capital::create([
            'created_by' => $admin->id,
            'description' => 'Initial Capital Cash',
            'amount' => 50000000,
            'type' => 'initial',
            'entry_date' => now()->toDateString(),
            'payment_method' => 'cash',
        ]);
        \App\Models\Capital::create([
            'created_by' => $admin->id,
            'description' => 'Initial Capital Transfer',
            'amount' => 50000000,
            'type' => 'initial',
            'entry_date' => now()->toDateString(),
            'payment_method' => 'transfer',
        ]);

        $response = $this->actingAs($admin)->post(route('units.store'), [
            'brand_name' => 'Apple',
            'model_name' => 'iPhone 16 Pro',
            'unit_type' => 'baru',
            'purchase_price' => '20.000.000',
            'purchase_date' => now()->toDateString(),
            'purchase_cash' => '10.000.000',
            'purchase_transfer' => '10.000.000',
            'imei' => '999998888877777',
        ]);

        $response->assertRedirect(route('units.index'));
        $this->assertDatabaseHas('units', [
            'purchase_price' => 20000000,
            'purchase_cash' => 10000000,
            'purchase_transfer' => 10000000,
            'imei' => '999998888877777',
        ]);
    }
}
