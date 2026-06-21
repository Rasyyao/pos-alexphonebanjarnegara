<?php

namespace App\Services;

use App\Models\DailyClosing;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\Expense;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DailyClosingService
{
    /**
     * Get aggregated closing metrics for a given date.
     */
    public function getClosingDataForDate(string $date): array
    {
        // 1. Total Income (Cash + Transfer payments of approved sales)
        $totalIncome = (float) SalePayment::whereIn('method', ['cash', 'transfer'])
            ->whereHas('sale', fn($q) => $q->approved()->whereDate('sale_date', $date))
            ->sum('amount');

        // 2. Gas Income (Accessories category 'Gas' or containing 'gas' in name)
        $gasIncome = (float) SaleItem::whereNotNull('accessory_id')
            ->whereHas('accessory', function ($q) {
                $q->where('category', 'like', '%gas%')
                  ->orWhere('name', 'like', '%gas%');
            })
            ->whereHas('sale', fn($q) => $q->approved()->whereDate('sale_date', $date))
            ->sum('subtotal');

        // 3. HP Purchase (Cost of HP stock purchased today)
        $hpPurchase = (float) Unit::whereDate('purchase_date', $date)->sum('purchase_price');

        // 4. HP Sale (Income from HP sales)
        $hpSale = (float) SaleItem::whereNotNull('unit_id')
            ->whereHas('sale', fn($q) => $q->approved()->whereDate('sale_date', $date))
            ->sum('subtotal');

        // 5. Laba (Profit of sales)
        $laba = (float) Sale::approved()->whereDate('sale_date', $date)->sum('profit');

        // 6. Cash System (Received Cash from Sales - Cash Expenses - Cash HP Purchases)
        $cashSystemSales = (float) SalePayment::where('method', 'cash')
            ->whereHas('sale', fn($q) => $q->approved()->whereDate('sale_date', $date))
            ->sum('amount');
        $cashExpenses = (float) Expense::where('payment_method', 'cash')
            ->whereDate('expense_date', $date)
            ->sum('amount');
        $hpCashPurchases = (float) Unit::whereDate('purchase_date', $date)
            ->sum('purchase_cash');
        $cashSystem = $cashSystemSales - $cashExpenses - $hpCashPurchases;

        // 7. Transfer Income
        $transferIncome = (float) SalePayment::where('method', 'transfer')
            ->whereHas('sale', fn($q) => $q->approved()->whereDate('sale_date', $date))
            ->sum('amount');

        // ATM/Transfer System calculation (Sales Transfer In - Expenses Transfer Out - HP Transfer Purchases Out + Cash to ATM transfers - ATM to Cash transfers)
        $transferExpenses = (float) Expense::where('payment_method', 'transfer')
            ->whereDate('expense_date', $date)
            ->sum('amount');
        $hpTransferPurchases = (float) Unit::whereDate('purchase_date', $date)
            ->sum('purchase_transfer');
        $cashToAtm = (float) \App\Models\FundTransfer::where('direction', 'cash_to_atm')
            ->whereDate('transfer_date', $date)
            ->sum('amount');
        $atmToCash = (float) \App\Models\FundTransfer::where('direction', 'atm_to_cash')
            ->whereDate('transfer_date', $date)
            ->sum('amount');
        $atmSystem = $transferIncome - $transferExpenses - $hpTransferPurchases + $cashToAtm - $atmToCash;

        // 8. Piutang/Utang (Debt payments of approved sales)
        $debtAmount = (float) SalePayment::where('method', 'utang')
            ->whereHas('sale', fn($q) => $q->approved()->whereDate('sale_date', $date))
            ->sum('amount');

        return [
            'total_income'    => $totalIncome,
            'gas_income'      => $gasIncome,
            'hp_purchase'     => $hpPurchase,
            'hp_sale'         => $hpSale,
            'laba'            => $laba,
            'cash_system'     => $cashSystem,
            'atm_system'      => $atmSystem,
            'transfer_income' => $transferIncome,
            'debt_amount'     => $debtAmount,
        ];
    }

    /**
     * Check if a specific date is closed or verified (locked).
     */
    public static function isDateLocked(string $date): bool
    {
        return DailyClosing::whereDate('closing_date', $date)
            ->whereIn('status', ['closed', 'verified'])
            ->exists();
    }

    /**
     * Helper to assert date is not locked.
     */
    public static function assertDateNotLocked(string $date): void
    {
        if (self::isDateLocked($date)) {
            throw ValidationException::withMessages([
                'date' => 'Transaksi tidak dapat diubah karena laporan keuangan harian untuk tanggal ' . Carbon::parse($date)->format('d/m/Y') . ' sudah ditutup / diverifikasi.',
            ]);
        }
    }

    /**
     * Close the book for a given date.
     */
    public function closeBook(string $date, float $cashPhysical, float $atmPhysical, ?string $notes, User $actor): DailyClosing
    {
        return DB::transaction(function () use ($date, $cashPhysical, $atmPhysical, $notes, $actor) {
            $existing = DailyClosing::whereDate('closing_date', $date)->first();
            if ($existing && $existing->status === 'verified') {
                throw ValidationException::withMessages([
                    'date' => 'Laporan keuangan harian untuk tanggal ini sudah diverifikasi dan dikunci.',
                ]);
            }

            $isSuperadmin = $actor->isSuperAdmin();
            $metrics = $this->getClosingDataForDate($date);

            $attributes = array_merge($metrics, [
                'cash_physical' => $cashPhysical,
                'atm_physical'  => $atmPhysical,
                'closed_by'     => $actor->id,
                'closed_at'     => now(),
                'notes'         => $notes,
            ]);

            if ($isSuperadmin) {
                $attributes['status']      = 'verified';
                $attributes['verified_by'] = $actor->id;
                $attributes['verified_at'] = now();
            } else {
                $attributes['status'] = 'closed';
            }

            return DailyClosing::updateOrCreate(['closing_date' => $date], $attributes);
        });
    }

    /**
     * Verify the closed daily report (Super Admin only).
     */
    public function verifyClosing(DailyClosing $closing, User $actor): DailyClosing
    {
        if ($closing->status !== 'closed') {
            throw new \LogicException('Hanya laporan berstatus closed yang dapat diverifikasi.');
        }

        $closing->update([
            'status'      => 'verified',
            'verified_by' => $actor->id,
            'verified_at' => now(),
        ]);

        return $closing;
    }

    /**
     * Revert the closed/verified daily report to draft (Super Admin only).
     */
    public function revertToDraft(DailyClosing $closing): DailyClosing
    {
        $closing->update([
            'status'      => 'draft',
            'verified_by' => null,
            'verified_at' => null,
            'closed_by'   => null,
            'closed_at'   => null,
        ]);

        return $closing;
    }
}
