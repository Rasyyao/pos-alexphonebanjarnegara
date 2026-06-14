<?php

use App\Livewire\StockFilter;
use App\Repositories\Contracts\UnitRepositoryInterface;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Instantiating StockFilter...\n";
    $component = new StockFilter();
    
    // Set some properties
    $component->search = 'eec';
    $component->brand_id = '5';
    
    echo "Resolving repository...\n";
    $repository = app(UnitRepositoryInterface::class);
    
    echo "Rendering StockFilter...\n";
    $view = $component->render($repository);
    
    echo "Render successful! View name: " . $view->name() . "\n";
    
    // Check elements in compact
    $data = $view->getData();
    echo "Data passed to view:\n";
    echo "- Brands count: " . count($data['brands']) . "\n";
    echo "- Units count: " . count($data['units']) . "\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
