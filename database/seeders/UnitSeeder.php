<?php

namespace Database\Seeders;

use App\Models\ProductModel;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $admin    = User::where('role', 'superadmin')->first();
        $models   = ProductModel::with('brand')->get();

        $units = [
            ['brand' => 'Apple',   'model' => 'iPhone 15',        'type' => 'baru',   'ram' => '6GB',  'rom' => '128GB', 'color' => 'Black',    'price' => 13500000, 'grade' => 'A', 'imei' => '359281002938471', 'sn' => 'SN-IP15BLK01'],
            ['brand' => 'Apple',   'model' => 'iPhone 15 Pro',    'type' => 'baru',   'ram' => '8GB',  'rom' => '256GB', 'color' => 'Titanium', 'price' => 18000000, 'grade' => 'A', 'imei' => '359281002938472', 'sn' => 'SN-IP15P256'],
            ['brand' => 'Samsung', 'model' => 'Galaxy S24',       'type' => 'baru',   'ram' => '8GB',  'rom' => '256GB', 'color' => 'Marble Gray', 'price' => 11000000, 'grade' => 'A', 'imei' => '359281002938473', 'sn' => 'SN-S24GRY01'],
            ['brand' => 'Samsung', 'model' => 'Galaxy A55',       'type' => 'baru',   'ram' => '8GB',  'rom' => '256GB', 'color' => 'Awesome Navy', 'price' => 5200000, 'grade' => 'A', 'imei' => '359281002938474', 'sn' => 'SN-A55NVY01'],
            ['brand' => 'Xiaomi',  'model' => 'Redmi Note 13 Pro','type' => 'baru',   'ram' => '8GB',  'rom' => '256GB', 'color' => 'Midnight Black', 'price' => 3200000, 'grade' => 'B', 'imei' => '359281002938475', 'sn' => 'SN-RN13BLK'],
            ['brand' => 'Xiaomi',  'model' => 'POCO X6',          'type' => 'baru',   'ram' => '12GB', 'rom' => '256GB', 'color' => 'Yellow',   'price' => 3500000, 'grade' => 'A', 'imei' => '359281002938476', 'sn' => 'SN-PX6YEL01'],
            ['brand' => 'Apple',   'model' => 'iPhone 13',        'type' => 'second', 'ram' => '4GB',  'rom' => '128GB', 'color' => 'Starlight', 'price' => 7500000, 'grade' => 'B', 'imei' => '359281002938477', 'sn' => 'SN-IP13STRL'],
            ['brand' => 'Samsung', 'model' => 'Galaxy A35',       'type' => 'second', 'ram' => '6GB',  'rom' => '128GB', 'color' => 'Awesome Silver', 'price' => 2800000, 'grade' => 'C', 'imei' => '359281002938478', 'sn' => 'SN-A35SLV01'],
            ['brand' => 'Oppo',    'model' => 'Reno 12',          'type' => 'baru',   'ram' => '8GB',  'rom' => '256GB', 'color' => 'Sunset Gold', 'price' => 4800000, 'grade' => 'A', 'imei' => '359281002938479', 'sn' => 'SN-R12GLD01'],
            ['brand' => 'Vivo',    'model' => 'V30',              'type' => 'baru',   'ram' => '8GB',  'rom' => '256GB', 'color' => 'Peacock Green', 'price' => 4500000, 'grade' => 'A', 'imei' => '359281002938480', 'sn' => 'SN-V30GRN01'],
        ];

        // foreach ($units as $u) {
        //     $model = $models->first(fn($m) => $m->brand->name === $u['brand'] && $m->name === $u['model']);
        //     if (!$model) continue;

        //     Unit::create([
        //         'model_id'       => $model->id,
        //         'created_by'     => $admin->id,
        //         'unit_type'      => $u['type'],
        //         'grade'          => $u['grade'],
        //         'ram'            => $u['ram'],
        //         'rom'            => $u['rom'],
        //         'color'          => $u['color'],
        //         'imei'           => $u['imei'],
        //         'serial_number'  => $u['sn'],
        //         'purchase_price' => $u['price'],
        //         'purchase_date'  => now()->subDays(rand(1, 30))->toDateString(),
        //         'status'         => 'ready',
        //     ]);
        // }
    }
}
