<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = ['created_by', 'description', 'amount', 'category', 'expense_date', 'notes', 'payment_method'];

    protected function casts(): array
    {
        return [
            'amount'         => 'decimal:2',
            'expense_date'   => 'date',
            'payment_method' => 'string',
        ];
    }

    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
