<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->decimal('purchase_cash', 12, 2)->default(0)->after('purchase_price');
            $table->decimal('purchase_transfer', 12, 2)->default(0)->after('purchase_cash');
        });

        Schema::table('accessories', function (Blueprint $table) {
            $table->decimal('purchase_cash', 10, 2)->default(0)->after('purchase_price');
            $table->decimal('purchase_transfer', 10, 2)->default(0)->after('purchase_cash');
        });

        // Populate existing units
        DB::table('units')->where('purchase_payment_method', 'cash')->update([
            'purchase_cash' => DB::raw('purchase_price'),
            'purchase_transfer' => 0
        ]);
        DB::table('units')->where('purchase_payment_method', 'transfer')->update([
            'purchase_cash' => 0,
            'purchase_transfer' => DB::raw('purchase_price')
        ]);

        // Populate existing accessories
        DB::table('accessories')->where('purchase_payment_method', 'cash')->update([
            'purchase_cash' => DB::raw('purchase_price'),
            'purchase_transfer' => 0
        ]);
        DB::table('accessories')->where('purchase_payment_method', 'transfer')->update([
            'purchase_cash' => 0,
            'purchase_transfer' => DB::raw('purchase_price')
        ]);
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn(['purchase_cash', 'purchase_transfer']);
        });

        Schema::table('accessories', function (Blueprint $table) {
            $table->dropColumn(['purchase_cash', 'purchase_transfer']);
        });
    }
};
