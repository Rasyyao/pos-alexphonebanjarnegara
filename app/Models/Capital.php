<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Capital extends Model
{
    protected $fillable = ['created_by', 'description', 'amount', 'type', 'entry_date'];

    protected function casts(): array
    {
        return [
            'amount'     => 'decimal:2',
            'entry_date' => 'date',
        ];
    }

    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
