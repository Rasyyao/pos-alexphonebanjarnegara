<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Accessory;

$cats = Accessory::select('category')->distinct()->pluck('category')->toArray();
echo "Accessory categories: " . implode(', ', $cats) . "\n";

$names = Accessory::select('name')->distinct()->pluck('name')->toArray();
echo "Accessory names: " . implode(', ', $names) . "\n";
