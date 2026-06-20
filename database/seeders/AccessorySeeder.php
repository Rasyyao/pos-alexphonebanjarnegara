<?php

namespace Database\Seeders;

use App\Models\Accessory;
use Illuminate\Database\Seeder;

class AccessorySeeder extends Seeder
{
    public function run(): void
    {
        $accessories = [
            ['name' => 'Casing Silicone Universal', 'category' => 'Case', 'stock_qty' => 20, 'purchase_price' => 15000, 'purchase_payment_method' => 'cash', 'purchase_cash' => 15000],
            ['name' => 'Tempered Glass Full', 'category' => 'Pelindung Layar', 'stock_qty' => 30, 'purchase_price' => 8000, 'purchase_payment_method' => 'cash', 'purchase_cash' => 8000],
            ['name' => 'Charger Fast Charge 33W', 'category' => 'Charger', 'stock_qty' => 10, 'purchase_price' => 55000, 'purchase_payment_method' => 'cash', 'purchase_cash' => 55000],
            ['name' => 'Kabel USB-C 1m', 'category' => 'Kabel', 'stock_qty' => 25, 'purchase_price' => 12000, 'purchase_payment_method' => 'cash', 'purchase_cash' => 12000],
            ['name' => 'TWS Earbuds Bass+', 'category' => 'Audio', 'stock_qty' => 8, 'purchase_price' => 85000, 'purchase_payment_method' => 'cash', 'purchase_cash' => 85000],
            ['name' => 'Power Bank 10000mAh', 'category' => 'Baterai', 'stock_qty' => 5, 'purchase_price' => 120000, 'purchase_payment_method' => 'cash', 'purchase_cash' => 120000],
        ];

        foreach ($accessories as $data) {
            Accessory::create($data);
        }
    }
}
