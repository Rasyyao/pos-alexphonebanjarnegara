<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = ['created_by', 'description', 'amount', 'category', 'expense_date', 'notes'];

    protected function casts(): array
    {
        return [
            'amount'       => 'decimal:2',
            'expense_date' => 'date',
        ];
    }

    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
