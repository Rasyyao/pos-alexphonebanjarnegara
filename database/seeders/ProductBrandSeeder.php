<?php

namespace Database\Seeders;

use App\Models\ProductBrand;
use App\Models\ProductModel;
use Illuminate\Database\Seeder;

class ProductBrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            'Samsung'  => ['Galaxy A15', 'Galaxy A35', 'Galaxy A55', 'Galaxy S24', 'Galaxy S24 Ultra'],
            'Xiaomi'   => ['Redmi 13', 'Redmi Note 13', 'POCO X6', 'Redmi Note 13 Pro', '14T'],
            'Oppo'     => ['A18', 'A38', 'Reno 12', 'Reno 12 Pro', 'Find X7'],
            'Vivo'     => ['Y18', 'Y28', 'V30', 'V30 Pro', 'X100'],
            'Apple'    => ['iPhone 13', 'iPhone 14', 'iPhone 15', 'iPhone 15 Pro', 'iPhone 15 Pro Max'],
            'Realme'   => ['C63', 'C75', 'GT 6T', 'Narzo N65'],
        ];

        foreach ($brands as $brandName => $models) {
            $brand = ProductBrand::create(['name' => $brandName]);
            foreach ($models as $modelName) {
                ProductModel::create(['brand_id' => $brand->id, 'name' => $modelName]);
            }
        }
    }
}
