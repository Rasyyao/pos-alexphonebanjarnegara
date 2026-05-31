<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBrand extends Model
{
    public $timestamps = false;
    protected $fillable = ['name'];

    public function models() { return $this->hasMany(ProductModel::class, 'brand_id'); }
}
