<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_payments', function (Blueprint $table) {
            $table->string('source', 20)->default('sale')->after('amount');
        });

        DB::statement("
            UPDATE sale_payments
            SET source = 'debt_payment'
            WHERE method IN ('cash', 'transfer')
              AND EXISTS (
                  SELECT 1
                  FROM sales
                  WHERE sales.id = sale_payments.sale_id
                    AND DATE(sale_payments.created_at) > DATE(sales.created_at)
              )
        ");
    }

    public function down(): void
    {
        Schema::table('sale_payments', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
