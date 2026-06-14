<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('capitals', function (Blueprint $table) {
            $table->foreignId('sale_id')->nullable()->after('payment_method')->constrained('sales')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('capitals', function (Blueprint $table) {
            $table->dropForeign(['sale_id']);
            $table->dropColumn('sale_id');
        });
    }
};
