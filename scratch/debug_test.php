<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $admin = \App\Models\User::first() ?: \App\Models\User::create([
        'name' => 'Super Admin',
        'username' => 'superadmin',
        'password' => bcrypt('password'),
        'role' => 'superadmin',
        'is_active' => true,
    ]);
    
    auth()->login($admin);
    
    // Try reportSummary
    $finance = app(\App\Services\FinanceService::class);
    $finance->reportSummary();
    echo "reportSummary passed!\n";
    
    // Try AccessoryController store
    $request = \App\Http\Requests\StoreAccessoryRequest::create(route('accessories.store'), 'POST', [
        'name' => 'Premium Phone Case',
        'category' => 'Case',
        'stock_qty' => 10,
        'purchase_price' => '150.000',
        'purchase_cash' => '50.000',
        'purchase_transfer' => '100.000',
        'selling_price' => '250.000',
    ]);
    app()->instance('request', $request);
    
    $request->setContainer(app());
    $request->validateResolved();
    
    $controller = app(\App\Http\Controllers\AccessoryController::class);
    $controller->store($request);
    echo "Controller store passed!\n";
} catch (\Throwable $e) {
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
