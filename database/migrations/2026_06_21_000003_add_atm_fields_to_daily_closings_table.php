<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_closings', function (Blueprint $table) {
            $table->decimal('atm_physical', 14, 2)->default(0)->after('cash_system');
            $table->decimal('atm_system', 14, 2)->default(0)->after('atm_physical');
        });
    }

    public function down(): void
    {
        Schema::table('daily_closings', function (Blueprint $table) {
            $table->dropColumn(['atm_physical', 'atm_system']);
        });
    }
};
