<?php

use App\Models\Unit;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rams = Unit::select('ram')->distinct()->pluck('ram');
$roms = Unit::select('rom')->distinct()->pluck('rom');
$colors = Unit::select('color')->distinct()->pluck('color');
$grades = Unit::select('grade')->distinct()->pluck('grade');

echo "RAMs:\n";
print_r($rams->toArray());

echo "ROMs:\n";
print_r($roms->toArray());

echo "Colors:\n";
print_r($colors->toArray());

echo "Grades:\n";
print_r($grades->toArray());
