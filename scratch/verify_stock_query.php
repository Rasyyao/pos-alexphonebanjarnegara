<?php

use App\Models\Unit;
use App\Models\ProductBrand;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "BRANDS:\n";
foreach (ProductBrand::all() as $brand) {
    echo "- ID: {$brand->id}, Name: {$brand->name}\n";
}

echo "\nUNITS:\n";
$units = Unit::with('model.brand')->get();
foreach ($units as $unit) {
    echo "- ID: {$unit->id}, Brand: " . ($unit->model->brand->name ?? 'None') . ", Model: " . ($unit->model->name ?? 'None') . ", Type: {$unit->unit_type->value}, Status: {$unit->status->value}, RAM: {$unit->ram}, ROM: {$unit->rom}, Grade: {$unit->grade}, Color: {$unit->color}\n";
}

echo "\nFiltering by brand_id = 5 (if exists) or Apple:\n";
$apple = ProductBrand::where('name', 'like', '%Apple%')->first();
if ($apple) {
    $appleUnits = Unit::with('model.brand')
        ->whereHas('model', fn($q) => $q->where('brand_id', $apple->id))
        ->get();
    echo "Found " . $appleUnits->count() . " units for Apple:\n";
    foreach ($appleUnits as $unit) {
        echo "- ID: {$unit->id}, Model: {$unit->model->name}\n";
    }
}
