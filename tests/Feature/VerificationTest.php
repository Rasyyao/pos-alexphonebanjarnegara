<?php

namespace Tests\Feature;

use App\Models\Accessory;
use App\Models\ProductBrand;
use App\Models\ProductModel;
use App\Models\Unit;
use App\Models\User;
use App\Models\Capital;
use App\Enums\UnitStatus;
use App\Enums\AccessoryStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerificationTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'password' => bcrypt('password'),
            'role' => 'superadmin',
            'is_active' => true,
        ]);

        $this->admin = User::create([
            'name' => 'Admin Staff',
            'username' => 'admin',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Fund capital so both Cash and Transfer accounts have balance for unit/accessory purchases
        Capital::create([
            'created_by' => $this->superadmin->id,
            'description' => 'Initial Capital Cash',
            'amount' => 50000000,
            'type' => 'initial',
            'entry_date' => now()->toDateString(),
            'payment_method' => 'cash',
        ]);
        Capital::create([
            'created_by' => $this->superadmin->id,
            'description' => 'Initial Capital Transfer',
            'amount' => 50000000,
            'type' => 'initial',
            'entry_date' => now()->toDateString(),
            'payment_method' => 'transfer',
        ]);
    }

    public function test_unit_creation_status_defaults(): void
    {
        // 1. Admin creates a unit -> should be pending
        $responseAdmin = $this->actingAs($this->admin)->post(route('units.store'), [
            'brand_name' => 'Xiaomi',
            'model_name' => 'Redmi Note 13',
            'unit_type' => 'baru',
            'purchase_price' => '3.000.000',
            'purchase_date' => now()->toDateString(),
            'purchase_payment_method' => 'cash',
            'imei' => '111112222233333',
        ]);

        $responseAdmin->assertRedirect(route('units.index'));
        $this->assertDatabaseHas('units', [
            'imei' => '111112222233333',
            'status' => 'pending',
        ]);

        // 2. Superadmin creates a unit -> should be ready
        $responseSuper = $this->actingAs($this->superadmin)->post(route('units.store'), [
            'brand_name' => 'Apple',
            'model_name' => 'iPhone 15',
            'unit_type' => 'baru',
            'purchase_price' => '12.000.000',
            'purchase_date' => now()->toDateString(),
            'purchase_payment_method' => 'transfer',
            'imei' => '999998888877777',
        ]);

        $responseSuper->assertRedirect(route('units.index'));
        $this->assertDatabaseHas('units', [
            'imei' => '999998888877777',
            'status' => 'ready',
        ]);
    }

    public function test_accessory_creation_status_defaults(): void
    {
        // 1. Admin creates an accessory -> should be pending
        $responseAdmin = $this->actingAs($this->admin)->post(route('accessories.store'), [
            'name' => 'Admin Headset',
            'category' => 'Audio',
            'stock_qty' => 5,
            'purchase_price' => '100.000',
            'purchase_payment_method' => 'cash',
        ]);

        $responseAdmin->assertRedirect(route('accessories.index'));
        $this->assertDatabaseHas('accessories', [
            'name' => 'Admin Headset',
            'status' => 'pending',
        ]);

        // 2. Superadmin creates an accessory -> should be approved
        $responseSuper = $this->actingAs($this->superadmin)->post(route('accessories.store'), [
            'name' => 'Super Headset',
            'category' => 'Audio',
            'stock_qty' => 5,
            'purchase_price' => '100.000',
            'purchase_payment_method' => 'transfer',
        ]);

        $responseSuper->assertRedirect(route('accessories.index'));
        $this->assertDatabaseHas('accessories', [
            'name' => 'Super Headset',
            'status' => 'approved',
        ]);
    }

    public function test_superadmin_can_approve_unit(): void
    {
        $brand = ProductBrand::create(['name' => 'Oppo']);
        $model = ProductModel::create(['brand_id' => $brand->id, 'name' => 'Reno 10']);
        $unit = Unit::create([
            'model_id' => $model->id,
            'created_by' => $this->admin->id,
            'unit_type' => 'baru',
            'purchase_price' => 4500000,
            'purchase_date' => now()->toDateString(),
            'purchase_payment_method' => 'cash',
            'purchase_cash' => 4500000,
            'status' => 'pending',
            'imei' => '888887777766666',
        ]);

        $response = $this->actingAs($this->superadmin)->post(route('units.approve', $unit));
        $response->assertRedirect();
        
        $this->assertEquals(UnitStatus::Ready, $unit->fresh()->status);
    }

    public function test_superadmin_can_approve_accessory(): void
    {
        $accessory = Accessory::create([
            'name' => 'Admin Charger',
            'category' => 'Charger',
            'stock_qty' => 10,
            'purchase_price' => 50000,
            'selling_price' => 80000,
            'purchase_payment_method' => 'cash',
            'purchase_cash' => 50000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->superadmin)->post(route('accessories.approve', $accessory));
        $response->assertRedirect();

        $this->assertEquals(AccessoryStatus::Approved, $accessory->fresh()->status);
    }

    public function test_admin_cannot_approve_unit_or_accessory(): void
    {
        $brand = ProductBrand::create(['name' => 'Oppo']);
        $model = ProductModel::create(['brand_id' => $brand->id, 'name' => 'Reno 10']);
        $unit = Unit::create([
            'model_id' => $model->id,
            'created_by' => $this->admin->id,
            'unit_type' => 'baru',
            'purchase_price' => 4500000,
            'purchase_date' => now()->toDateString(),
            'purchase_payment_method' => 'cash',
            'purchase_cash' => 4500000,
            'status' => 'pending',
            'imei' => '888887777766666',
        ]);

        $accessory = Accessory::create([
            'name' => 'Admin Charger',
            'category' => 'Charger',
            'stock_qty' => 10,
            'purchase_price' => 50000,
            'selling_price' => 80000,
            'purchase_payment_method' => 'cash',
            'purchase_cash' => 50000,
            'status' => 'pending',
        ]);

        // 1. Try to approve Unit as Admin -> 403 Forbidden
        $responseUnit = $this->actingAs($this->admin)->post(route('units.approve', $unit));
        $responseUnit->assertStatus(403);

        // 2. Try to approve Accessory as Admin -> 403 Forbidden
        $responseAcc = $this->actingAs($this->admin)->post(route('accessories.approve', $accessory));
        $responseAcc->assertStatus(403);
    }

    public function test_superadmin_can_reject_and_delete_pending_unit_or_accessory(): void
    {
        $brand = ProductBrand::create(['name' => 'Oppo']);
        $model = ProductModel::create(['brand_id' => $brand->id, 'name' => 'Reno 10']);
        $unit = Unit::create([
            'model_id' => $model->id,
            'created_by' => $this->admin->id,
            'unit_type' => 'baru',
            'purchase_price' => 4500000,
            'purchase_date' => now()->toDateString(),
            'purchase_payment_method' => 'cash',
            'purchase_cash' => 4500000,
            'status' => 'pending',
            'imei' => '888887777766666',
        ]);

        $accessory = Accessory::create([
            'name' => 'Admin Charger',
            'category' => 'Charger',
            'stock_qty' => 10,
            'purchase_price' => 50000,
            'selling_price' => 80000,
            'purchase_payment_method' => 'cash',
            'purchase_cash' => 50000,
            'status' => 'pending',
        ]);

        // Delete unit as superadmin
        $responseUnit = $this->actingAs($this->superadmin)->delete(route('units.destroy', $unit));
        $responseUnit->assertRedirect();
        $this->assertDatabaseMissing('units', ['id' => $unit->id]);

        // Delete accessory as superadmin
        $responseAcc = $this->actingAs($this->superadmin)->delete(route('accessories.destroy', $accessory));
        $responseAcc->assertRedirect();
        $this->assertDatabaseMissing('accessories', ['id' => $accessory->id]);
    }
}
