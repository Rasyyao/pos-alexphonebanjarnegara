<?php

namespace Tests\Feature;

use App\Models\Accessory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessoryCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_accessory_with_formatted_currency_prices(): void
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

        $response = $this->actingAs($admin)->post(route('accessories.store'), [
            'name' => 'Premium Phone Case',
            'category' => 'Case',
            'stock_qty' => 10,
            'purchase_price' => '150.000',
            'purchase_cash' => '50.000',
            'purchase_transfer' => '100.000',
            'selling_price' => '250.000', // Note: selling_price is not validated in StoreAccessoryRequest, but let's include it
        ]);

        $response->assertRedirect(route('accessories.index'));
        $this->assertDatabaseHas('accessories', [
            'name' => 'Premium Phone Case',
            'stock_qty' => 10,
            'purchase_price' => 1500000, // Total purchase price is purchase_price * stock_qty in DB? No, in DB it is purchase_price per unit!
            // Wait, in DB accessories table has: purchase_price decimal(10,2) and selling_price decimal(10,2)
            'purchase_price' => 150000,
            'purchase_cash' => 50000,
            'purchase_transfer' => 100000,
        ]);
    }
}
