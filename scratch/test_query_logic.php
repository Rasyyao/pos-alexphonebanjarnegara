<?php

use App\Models\Unit;
use App\Models\ProductBrand;
use App\Models\ProductModel;
use App\Models\User;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Create a superadmin if not exists
$admin = User::where('role', 'superadmin')->first();
if (!$admin) {
    $admin = User::create([
        'name' => 'Super Admin',
        'username' => 'superadmin',
        'password' => Hash::make('password'),
        'role' => 'superadmin',
        'is_active' => true,
    ]);
}

// 2. Create Brands and Models if not exist
$oppo = ProductBrand::firstOrCreate(['name' => 'Oppo']);
$oppoModel = ProductModel::firstOrCreate(['brand_id' => $oppo->id, 'name' => 'Opopo c31']);

$apple = ProductBrand::firstOrCreate(['name' => 'Apple']);
$appleModel = ProductModel::firstOrCreate(['brand_id' => $apple->id, 'name' => 'iPhone 15']);

// 3. Clear existing test units to make it clean
Unit::whereIn('color', ['biru', 'Black Test'])->delete();

// 4. Create Oppo Opopo c31 ready unit
$oppoUnit = Unit::create([
    'model_id' => $oppoModel->id,
    'created_by' => $admin->id,
    'unit_type' => 'baru',
    'grade' => 'A',
    'ram' => '4',
    'rom' => '128',
    'color' => 'biru',
    'imei' => '123456789012345',
    'serial_number' => 'SN-OPPO-C31',
    'purchase_price' => 1500000,
    'purchase_date' => now()->toDateString(),
    'status' => 'ready',
]);

// 5. Create Apple iPhone 15 ready unit
$appleUnit = Unit::create([
    'model_id' => $appleModel->id,
    'created_by' => $admin->id,
    'unit_type' => 'baru',
    'grade' => 'A',
    'ram' => '6',
    'rom' => '128',
    'color' => 'Black Test',
    'imei' => '543210987654321',
    'serial_number' => 'SN-IPHONE-15',
    'purchase_price' => 13500000,
    'purchase_date' => now()->toDateString(),
    'status' => 'ready',
]);

echo "Created test units.\n";

// Now run the query with various filters
function testQuery($search, $brand_id, $unit_type, $status, $grade, $ram, $rom) {
    echo "\n--- RUNNING QUERY WITH FILTERS ---\n";
    echo "search: '$search', brand_id: '$brand_id', unit_type: '$unit_type', status: '$status', grade: '$grade', ram: '$ram', rom: '$rom'\n";

    $query = Unit::with('model.brand')
        ->when($search !== '', function ($q) use ($search) {
            $q->where(function ($subQ) use ($search) {
                $subQ->where('color', 'like', "%{$search}%")
                     ->orWhere('ram', 'like', "%{$search}%")
                     ->orWhere('rom', 'like', "%{$search}%")
                     ->orWhere('grade', 'like', "%{$search}%")
                     ->orWhere('imei', 'like', "%{$search}%")
                     ->orWhere('serial_number', 'like', "%{$search}%")
                     ->orWhereHas('model', function ($modelQ) use ($search) {
                         $modelQ->where('name', 'like', "%{$search}%")
                                ->orWhereHas('brand', function ($brandQ) use ($search) {
                                    $brandQ->where('name', 'like', "%{$search}%");
                                });
                     });
            });
        })
        ->when($brand_id !== '', fn($q) => $q->whereHas('model', fn($q) => $q->where('brand_id', $brand_id)))
        ->when($unit_type !== '', fn($q) => $q->where('unit_type', $unit_type))
        ->when($status !== '', fn($q) => $q->where('status', $status))
        ->when($ram !== '', fn($q) => $q->where('ram', 'like', "%{$ram}%"))
        ->when($rom !== '', fn($q) => $q->where('rom', 'like', "%{$rom}%"))
        ->when($grade !== '', fn($q) => $q->where('grade', $grade));

    $results = $query->get();
    echo "Found " . $results->count() . " units:\n";
    foreach ($results as $u) {
        echo "- ID: {$u->id}, Brand: {$u->model->brand->name}, Model: {$u->model->name}, Color: {$u->color}, Status: {$u->status->value}, RAM: {$u->ram}, ROM: {$u->rom}\n";
    }
}

// Case 1: Search 'eec', Brand Apple, etc.
testQuery('eec', $apple->id, 'baru', 'ready', 'A', '4', '4');

// Case 2: No filters (except ready status)
testQuery('', '', '', 'ready', '', '', '');

// Case 3: Filter by Oppo brand
testQuery('', $oppo->id, '', 'ready', '', '', '');
