<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductModel extends Model
{
    public $timestamps = false;
    protected $fillable = ['brand_id', 'name'];

    public function brand() { return $this->belongsTo(ProductBrand::class, 'brand_id'); }
    public function units() { return $this->hasMany(Unit::class, 'model_id'); }
}
