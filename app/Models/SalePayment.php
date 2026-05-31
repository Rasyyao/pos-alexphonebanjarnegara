<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Model;

class SalePayment extends Model
{
    public $timestamps = false;
    protected $fillable = ['sale_id', 'method', 'amount'];

    protected function casts(): array
    {
        return [
            'method' => PaymentMethod::class,
            'amount' => 'decimal:2',
        ];
    }

    public function sale() { return $this->belongsTo(Sale::class); }
}
