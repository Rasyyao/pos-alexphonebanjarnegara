<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundTransfer extends Model
{
    protected $fillable = [
        'created_by',
        'direction',
        'amount',
        'description',
        'transfer_date',
    ];

    protected function casts(): array
    {
        return [
            'amount'        => 'decimal:2',
            'transfer_date' => 'date',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
