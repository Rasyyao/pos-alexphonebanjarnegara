<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Accessory extends Model
{
    protected $fillable = [
        'name', 'category', 'stock_qty', 'purchase_price', 'purchase_payment_method',
        'purchase_cash', 'purchase_transfer',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price'    => 'decimal:2',
            'purchase_cash'     => 'decimal:2',
            'purchase_transfer' => 'decimal:2',
        ];
    }

    public function saleItems() { return $this->hasMany(SaleItem::class); }
}
