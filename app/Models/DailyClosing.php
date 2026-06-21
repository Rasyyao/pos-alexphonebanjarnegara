<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyClosing extends Model
{
    protected $fillable = [
        'closing_date',
        'status',
        'total_income',
        'gas_income',
        'hp_purchase',
        'hp_sale',
        'laba',
        'cash_physical',
        'cash_system',
        'atm_physical',
        'atm_system',
        'transfer_income',
        'debt_amount',
        'closed_by',
        'closed_at',
        'verified_by',
        'verified_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'closing_date'    => 'date',
            'total_income'    => 'decimal:2',
            'gas_income'      => 'decimal:2',
            'hp_purchase'     => 'decimal:2',
            'hp_sale'         => 'decimal:2',
            'laba'            => 'decimal:2',
            'cash_physical'   => 'decimal:2',
            'cash_system'     => 'decimal:2',
            'atm_physical'    => 'decimal:2',
            'atm_system'      => 'decimal:2',
            'transfer_income' => 'decimal:2',
            'debt_amount'     => 'decimal:2',
            'closed_at'       => 'datetime',
            'verified_at'     => 'datetime',
        ];
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
