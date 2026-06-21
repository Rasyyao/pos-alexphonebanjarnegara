<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Model;

class SalePayment extends Model
{
    public $timestamps = false;
    protected $fillable = ['sale_id', 'method', 'amount', 'created_at'];

    protected function casts(): array
    {
        return [
            'method'     => PaymentMethod::class,
            'amount'     => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (is_null($model->created_at)) {
                $model->created_at = now();
            }
        });
    }

    public function sale() { return $this->belongsTo(Sale::class); }
}
