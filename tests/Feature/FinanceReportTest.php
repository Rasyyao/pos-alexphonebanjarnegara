<?php

namespace Tests\Feature;

use App\Models\Capital;
use App\Models\Sale;
use App\Models\ProductBrand;
use App\Models\ProductModel;
use App\Models\Unit;
use App\Models\User;
use App\Models\Expense;
use Illuminate\Support\Carbon;
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

    public function test_hp_stock_purchases_are_excluded_from_operational_expenses(): void
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

        // 3. Now get reports again. Operational expenses should remain 500.000.
        $response = $this->actingAs($this->superadmin)->get(route('reports.finance'));
        $response->assertStatus(200);
        $response->assertViewHas('today', function ($today) {
            return $today['expenses'] == 500000;
        });

        // 4. Verify that the stock purchase is still visible in the finance log, but not counted as operational expense.
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

    public function test_daily_income_counts_sale_payment_and_debt_repayment_on_their_actual_dates(): void
    {
        $saleDate = '2026-06-20';
        $repaymentDate = '2026-06-21';

        $sale = Sale::create([
            'created_by'     => $this->superadmin->id,
            'approved_by'    => $this->superadmin->id,
            'invoice_number' => 'INV-20260620-0001',
            'customer_name'  => 'Pembeli Test',
            'sale_date'      => $saleDate,
            'total_price'    => 5500000,
            'total_paid'     => 5500000,
            'profit'         => 1000000,
            'status'         => 'approved',
        ]);
        $sale->forceFill([
            'created_at' => Carbon::parse("{$saleDate} 10:00:00"),
            'updated_at' => Carbon::parse("{$saleDate} 10:00:00"),
        ])->save();

        $sale->payments()->create([
            'method'     => 'transfer',
            'amount'     => 5000000,
            'source'     => 'sale',
            'created_at' => Carbon::parse("{$saleDate} 10:00:00"),
        ]);
        $sale->payments()->create([
            'method'     => 'utang',
            'amount'     => 500000,
            'source'     => 'sale',
            'created_at' => Carbon::parse("{$saleDate} 10:00:00"),
        ]);
        $sale->debt()->create([
            'amount'      => 500000,
            'paid_amount' => 500000,
            'status'      => 'paid',
        ]);
        $sale->payments()->create([
            'method'     => 'cash',
            'amount'     => 500000,
            'source'     => 'debt_payment',
            'created_at' => Carbon::parse("{$repaymentDate} 11:00:00"),
        ]);

        $saleDateResponse = $this->actingAs($this->superadmin)->get(route('reports.finance', [
            'start_date' => $saleDate,
            'end_date'   => $saleDate,
        ]));
        $saleDateResponse->assertStatus(200);
        $saleDateResponse->assertViewHas('today', function ($today) {
            return (float) $today['income'] === 5000000.0
                && (float) $today['transfer'] === 5000000.0
                && (float) $today['cash'] === 0.0
                && (float) $today['debt'] === 500000.0;
        });

        $repaymentDateResponse = $this->actingAs($this->superadmin)->get(route('reports.finance', [
            'start_date' => $repaymentDate,
            'end_date'   => $repaymentDate,
        ]));
        $repaymentDateResponse->assertStatus(200);
        $repaymentDateResponse->assertViewHas('today', function ($today) {
            return (float) $today['income'] === 500000.0
                && (float) $today['cash'] === 500000.0
                && (float) $today['transfer'] === 0.0
                && (float) $today['debt'] === 0.0;
        });
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
        
        // Expense trend should be 500.000 (operational expense only, excluding stock purchase and owner withdrawal)
        $response->assertViewHas('monthlyExpData', function ($expData) {
            $currentMonthExpense = end($expData);
            return $currentMonthExpense == 500000;
        });

        // Net profit trend should be -500.000 (since sales is 0 and operational expenses are 500k)
        $response->assertViewHas('monthlyNetProfits', function ($netProfits) {
            $currentMonthNetProfit = end($netProfits);
            return $currentMonthNetProfit == -500000;
        });
    }
}
