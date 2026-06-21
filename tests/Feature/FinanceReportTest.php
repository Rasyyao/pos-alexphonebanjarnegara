<?php

namespace Tests\Feature;

use App\Models\Capital;
use App\Models\ProductBrand;
use App\Models\ProductModel;
use App\Models\Unit;
use App\Models\User;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceReportTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;

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

        Capital::create([
            'created_by' => $this->superadmin->id,
            'description' => 'Initial Capital Cash',
            'amount' => 50000000,
            'type' => 'initial',
            'entry_date' => now()->toDateString(),
            'payment_method' => 'cash',
        ]);
    }

    public function test_hp_stock_purchases_are_included_in_expenses_calculations(): void
    {
        // 1. Initially, expenses should be 0
        $response = $this->actingAs($this->superadmin)->get(route('reports.finance'));
        $response->assertStatus(200);
        $response->assertViewHas('today', function ($today) {
            return $today['expenses'] == 0;
        });

        // 2. Create a unit (costs 3.000.000)
        $brand = ProductBrand::create(['name' => 'Xiaomi']);
        $model = ProductModel::create(['brand_id' => $brand->id, 'name' => 'Redmi Note 13']);
        Unit::create([
            'model_id' => $model->id,
            'created_by' => $this->superadmin->id,
            'unit_type' => 'baru',
            'purchase_price' => 3000000,
            'purchase_date' => now()->toDateString(),
            'purchase_payment_method' => 'cash',
            'purchase_cash' => 3000000,
            'status' => 'ready',
            'imei' => '111112222233333',
        ]);

        // Add a normal operational expense (costs 500.000)
        Expense::create([
            'created_by' => $this->superadmin->id,
            'description' => 'Listrik Toko',
            'amount' => 500000,
            'category' => 'listrik',
            'expense_date' => now()->toDateString(),
            'payment_method' => 'cash',
        ]);

        // 3. Now get reports again. Total expenses should be 3.500.000 (3.000.000 + 500.000)
        $response = $this->actingAs($this->superadmin)->get(route('reports.finance'));
        $response->assertStatus(200);
        $response->assertViewHas('today', function ($today) {
            return $today['expenses'] == 3500000;
        });

        // 4. Verify that the expenses table contains the virtual stok HP row
        $response->assertSee('Pembelian Stok HP: Xiaomi Redmi Note 13');
        $response->assertSee('Listrik Toko');
    }

    public function test_admin_cannot_see_tarik_owner_category(): void
    {
        $admin = User::create([
            'name' => 'Admin Alex',
            'username' => 'admin_test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('reports.finance'));
        $response->assertStatus(200);
        $response->assertDontSee('Tarik Saldo Owner');
    }

    public function test_dashboard_monthly_trends_calculation(): void
    {
        // 1. Create a unit (costs 3.000.000)
        $brand = ProductBrand::create(['name' => 'Xiaomi']);
        $model = ProductModel::create(['brand_id' => $brand->id, 'name' => 'Redmi Note 13']);
        Unit::create([
            'model_id' => $model->id,
            'created_by' => $this->superadmin->id,
            'unit_type' => 'baru',
            'purchase_price' => 3000000,
            'purchase_date' => now()->toDateString(),
            'purchase_payment_method' => 'cash',
            'purchase_cash' => 3000000,
            'status' => 'ready',
            'imei' => '111112222233333',
        ]);

        // 2. Create normal expense (costs 500.000)
        Expense::create([
            'created_by' => $this->superadmin->id,
            'description' => 'Listrik Toko',
            'amount' => 500000,
            'category' => 'listrik',
            'expense_date' => now()->toDateString(),
            'payment_method' => 'cash',
        ]);

        // 3. Create owner withdrawal expense (costs 1.500.000, should be excluded)
        Expense::create([
            'created_by' => $this->superadmin->id,
            'description' => 'Tarik Saldo',
            'amount' => 1500000,
            'category' => 'tarik_owner',
            'expense_date' => now()->toDateString(),
            'payment_method' => 'cash',
        ]);

        // Get dashboard
        $response = $this->actingAs($this->superadmin)->get(route('dashboard'));
        $response->assertStatus(200);
        
        // Expense trend should be 3.500.000 (Unit 3M + Listrik 500k, excluding Tarik Saldo 1.5M)
        $response->assertViewHas('monthlyExpData', function ($expData) {
            $currentMonthExpense = end($expData);
            return $currentMonthExpense == 3500000;
        });

        // Net profit trend should be -3.500.000 (since sales is 0 and expenses is 3.5M)
        $response->assertViewHas('monthlyNetProfits', function ($netProfits) {
            $currentMonthNetProfit = end($netProfits);
            return $currentMonthNetProfit == -3500000;
        });
    }
}
