<?php

namespace Tests\Feature;

use App\Models\Debt;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DebtRepaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Debt $debt;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an active admin user (Breeze custom schema uses 'username' and 'role')
        $this->user = User::create([
            'name'      => 'Test Admin',
            'username'  => 'testadmin',
            'password'  => bcrypt('password'),
            'role'      => 'admin',
            'is_active' => true,
        ]);

        // Create a parent sale record
        $sale = Sale::create([
            'created_by'     => $this->user->id,
            'invoice_number' => 'INV-20260530-0001',
            'sale_date'      => '2026-05-30',
            'total_price'    => 1000000,
            'total_paid'     => 500000,
            'profit'         => 200000,
            'status'         => 'approved',
        ]);

        // Create an outstanding debt of 500.000 (partially paid, 500.000 outstanding)
        $this->debt = Debt::create([
            'sale_id'     => $sale->id,
            'amount'      => 1000000,
            'paid_amount' => 500000,
            'due_date'    => '2026-06-30',
            'status'      => 'partial',
        ]);
    }

    public function test_debts_index_page_is_rendered_for_authenticated_users(): void
    {
        $response = $this->actingAs($this->user)->get(route('debts.index'));

        $response->assertStatus(200);
        $response->assertViewIs('debts.index');
        $response->assertSee('INV-20260530-0001');
    }

    public function test_debt_can_be_paid_in_full(): void
    {
        $response = $this->actingAs($this->user)->post(route('debts.pay', $this->debt), [
            'type'           => 'full',
            'payment_method' => 'cash',
            'payment_date'   => '2026-06-15',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->debt->refresh();
        $this->assertEquals(1000000, (float) $this->debt->paid_amount);
        $this->assertEquals('paid', $this->debt->status);
    }

    public function test_debt_can_be_paid_partially_via_installments(): void
    {
        // Pay an installment of 200.000
        $response = $this->actingAs($this->user)->post(route('debts.pay', $this->debt), [
            'type'           => 'partial',
            'amount'         => 200000,
            'payment_method' => 'transfer',
            'payment_date'   => '2026-06-15',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->debt->refresh();
        // 500.000 (original paid) + 200.000 = 700.000
        $this->assertEquals(700000, (float) $this->debt->paid_amount);
        $this->assertEquals('partial', $this->debt->status);
        $this->assertEquals(
            '2026-06-15',
            $this->debt->sale->payments()
                ->where('source', 'debt_payment')
                ->where('amount', 200000)
                ->first()
                ->created_at
                ->toDateString()
        );
    }

    public function test_payment_exceeding_outstanding_balance_is_rejected(): void
    {
        // Try to pay 600.000 when only 500.000 is outstanding
        $response = $this->actingAs($this->user)->post(route('debts.pay', $this->debt), [
            'type'           => 'partial',
            'amount'         => 600000,
            'payment_method' => 'cash',
            'payment_date'   => '2026-06-15',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Jumlah cicilan tidak boleh melebihi sisa utang yang harus dibayar.');

        $this->debt->refresh();
        // Should remain unchanged
        $this->assertEquals(500000, (float) $this->debt->paid_amount);
    }

    public function test_invalid_payment_amount_fails_validation(): void
    {
        $response = $this->actingAs($this->user)->post(route('debts.pay', $this->debt), [
            'type'           => 'partial',
            'amount'         => -50,
            'payment_method' => 'cash',
            'payment_date'   => '2026-06-15',
        ]);

        $response->assertSessionHasErrors(['amount']);
    }
}
