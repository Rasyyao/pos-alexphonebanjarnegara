<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'sale_id', 'unit_id', 'accessory_id',
        'purchase_price', 'selling_price', 'quantity', 'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'selling_price'  => 'decimal:2',
            'subtotal'       => 'decimal:2',
        ];
    }

    public function sale()      { return $this->belongsTo(Sale::class); }
    public function unit()      { return $this->belongsTo(Unit::class)->withDefault(); }
    public function accessory() { return $this->belongsTo(Accessory::class)->withDefault(); }
}
