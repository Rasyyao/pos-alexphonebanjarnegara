<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Debt extends Model
{
    protected $fillable = ['sale_id', 'amount', 'paid_amount', 'due_date', 'status'];

    protected function casts(): array
    {
        return [
            'amount'      => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_date'    => 'date',
        ];
    }

    public function sale() { return $this->belongsTo(Sale::class); }
}
