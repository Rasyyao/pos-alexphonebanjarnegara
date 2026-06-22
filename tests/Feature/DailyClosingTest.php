<?php

namespace Tests\Feature;

use App\Models\DailyClosing;
use App\Models\Expense;
use App\Models\ProductBrand;
use App\Models\ProductModel;
use App\Models\Unit;
use App\Models\Accessory;
use App\Models\FundTransfer;
use App\Models\Capital;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyClosingTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;
    private User $admin;
    private string $testDate;

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

        $this->testDate = now()->toDateString();

        // Seed some initial capital
        Capital::create([
            'created_by' => $this->superadmin->id,
            'description' => 'Initial Capital Cash',
            'amount' => 50000000,
            'type' => 'initial',
            'entry_date' => $this->testDate,
            'payment_method' => 'cash',
        ]);
        Capital::create([
            'created_by' => $this->superadmin->id,
            'description' => 'Initial Capital Transfer',
            'amount' => 50000000,
            'type' => 'initial',
            'entry_date' => $this->testDate,
            'payment_method' => 'transfer',
        ]);
    }

    public function test_admin_can_close_daily_report(): void
    {
        $response = $this->actingAs($this->admin)->post(route('daily-closings.store'), [
            'closing_date'  => $this->testDate,
            'cash_physical' => '10.000.000',
            'atm_physical'  => '5.000.000',
            'notes'         => 'Test notes from admin',
        ]);

        $response->assertRedirect();
        
        $closing = DailyClosing::where('closed_by', $this->admin->id)->first();
        $this->assertNotNull($closing);
        $this->assertEquals('closed', $closing->status);
        $this->assertEquals(10000000, (float)$closing->cash_physical);
        $this->assertEquals(5000000, (float)$closing->atm_physical);
        $this->assertEquals('Test notes from admin', $closing->notes);
    }

    public function test_superadmin_can_verify_and_revert_daily_closing(): void
    {
        // 1. Create a closed closing
        $closing = DailyClosing::create([
            'closing_date'  => $this->testDate,
            'status'        => 'closed',
            'cash_physical' => 5000000,
            'cash_system'   => 5000000,
            'closed_by'     => $this->admin->id,
            'closed_at'     => now(),
        ]);

        // 2. Non-superadmin cannot verify
        $response = $this->actingAs($this->admin)->post(route('daily-closings.verify', $closing));
        $response->assertStatus(403);

        // 3. Superadmin can verify
        $response = $this->actingAs($this->superadmin)->post(route('daily-closings.verify', $closing));
        $response->assertRedirect();
        $this->assertDatabaseHas('daily_closings', [
            'id'          => $closing->id,
            'status'      => 'verified',
            'verified_by' => $this->superadmin->id,
        ]);

        // 4. Superadmin can revert back to draft
        $response = $this->actingAs($this->superadmin)->post(route('daily-closings.revert', $closing));
        $response->assertRedirect();
        $this->assertDatabaseHas('daily_closings', [
            'id'        => $closing->id,
            'status'    => 'draft',
            'closed_by' => null,
        ]);
    }

    public function test_locked_dates_block_mutations_for_admin(): void
    {
        // 1. Create verified closing to lock the date
        DailyClosing::create([
            'closing_date'  => $this->testDate,
            'status'        => 'verified',
            'cash_physical' => 5000000,
            'cash_system'   => 5000000,
            'atm_physical'  => 5000000,
            'atm_system'    => 5000000,
            'closed_by'     => $this->admin->id,
            'closed_at'     => now(),
            'verified_by'   => $this->superadmin->id,
            'verified_at'   => now(),
        ]);

        // 2. Try creating an expense on the locked date
        $response = $this->actingAs($this->admin)->post(route('expenses.store'), [
            'description'    => 'Sewa Toko',
            'amount'         => '1.500.000',
            'category'       => 'sewa',
            'expense_date'   => $this->testDate,
            'payment_method' => 'cash',
        ]);
        $response->assertSessionHasErrors('date');

        // 3. Try creating a unit on the locked date
        $response = $this->actingAs($this->admin)->post(route('units.store'), [
            'brand_name' => 'Xiaomi',
            'model_name' => 'Redmi Note 13',
            'unit_type' => 'baru',
            'purchase_price' => '3.000.000',
            'purchase_date' => $this->testDate,
            'purchase_payment_method' => 'cash',
            'imei' => '111112222233333',
        ]);
        $response->assertSessionHasErrors('date');

        // 4. Try creating an accessory on the locked date
        $response = $this->actingAs($this->admin)->post(route('accessories.store'), [
            'name' => 'Charger Typc C Anker',
            'category' => 'Charger',
            'stock_qty' => 10,
            'purchase_price' => '150.000',
            'selling_price' => '200.000',
            'purchase_date' => $this->testDate,
            'purchase_payment_method' => 'cash',
        ]);
        $response->assertSessionHasErrors('date');

        // 5. Try creating a fund transfer on the locked date -> blocked by role middleware (403)
        $response = $this->actingAs($this->admin)->post(route('fund-transfers.store'), [
            'direction' => 'cash_to_atm',
            'amount' => 500000,
            'transfer_date' => $this->testDate,
            'description' => 'Transfer Kas ke Bank',
        ]);
        $response->assertStatus(403);
    }

    public function test_locked_dates_allow_mutations_for_superadmin(): void
    {
        // 1. Create verified closing to lock the date
        DailyClosing::create([
            'closing_date'  => $this->testDate,
            'status'        => 'verified',
            'cash_physical' => 5000000,
            'cash_system'   => 5000000,
            'atm_physical'  => 5000000,
            'atm_system'    => 5000000,
            'closed_by'     => $this->admin->id,
            'closed_at'     => now(),
            'verified_by'   => $this->superadmin->id,
            'verified_at'   => now(),
        ]);

        // 2. Try creating an expense on the locked date -> allowed
        $response = $this->actingAs($this->superadmin)->post(route('expenses.store'), [
            'description'    => 'Sewa Toko',
            'amount'         => '1.500.000',
            'category'       => 'sewa',
            'expense_date'   => $this->testDate,
            'payment_method' => 'cash',
        ]);
        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }

    public function test_admin_cannot_overwrite_closed_closing_but_superadmin_can(): void
    {
        // 1. Create a closed closing
        DailyClosing::create([
            'closing_date'  => $this->testDate,
            'status'        => 'closed',
            'cash_physical' => 5000000,
            'cash_system'   => 5000000,
            'atm_physical'  => 5000000,
            'atm_system'    => 5000000,
            'closed_by'     => $this->admin->id,
            'closed_at'     => now(),
        ]);

        // 2. Admin tries to re-submit closing for that date -> blocked
        $response = $this->actingAs($this->admin)->post(route('daily-closings.store'), [
            'closing_date'  => $this->testDate,
            'cash_physical' => '6.000.000',
            'atm_physical'  => '6.000.000',
            'notes'         => 'Try edit closing as admin',
        ]);
        $response->assertSessionHasErrors('date');

        // 3. Superadmin tries to submit/edit closing for that date -> allowed
        $response = $this->actingAs($this->superadmin)->post(route('daily-closings.store'), [
            'closing_date'  => $this->testDate,
            'cash_physical' => '7.000.000',
            'atm_physical'  => '7.000.000',
            'notes' => 'Superadmin edit closing',
        ]);
        $response->assertRedirect();

        $closing = DailyClosing::whereDate('closing_date', $this->testDate)->first();
        $this->assertEquals(7000000, (float)$closing->cash_physical);
        $this->assertEquals(7000000, (float)$closing->atm_physical);
    }
}
