<?php

namespace App\Models;

use App\Enums\UnitStatus;
use App\Enums\UnitType;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = [
        'model_id', 'created_by', 'unit_type', 'grade', 'ram', 'rom', 'color',
        'imei', 'serial_number', 'purchase_price', 'photo_path', 'photo_path_2', 'photo_path_3',
        'notes', 'status', 'purchase_date',
    ];

    protected function casts(): array
    {
        return [
            'status'         => UnitStatus::class,
            'unit_type'      => UnitType::class,
            'purchase_price' => 'decimal:2',
            'purchase_date'  => 'date',
        ];
    }

    public function model()    { return $this->belongsTo(ProductModel::class, 'model_id'); }
    public function creator()  { return $this->belongsTo(User::class, 'created_by'); }
    public function saleItem() { return $this->hasOne(SaleItem::class); }
}
