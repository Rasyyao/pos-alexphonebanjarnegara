<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_closings', function (Blueprint $table) {
            $table->id();
            $table->date('closing_date')->unique();
            $table->enum('status', ['draft', 'closed', 'verified'])->default('draft');
            $table->decimal('total_income', 14, 2)->default(0);
            $table->decimal('gas_income', 14, 2)->default(0);
            $table->decimal('hp_purchase', 14, 2)->default(0);
            $table->decimal('hp_sale', 14, 2)->default(0);
            $table->decimal('laba', 14, 2)->default(0);
            $table->decimal('cash_physical', 14, 2)->default(0);
            $table->decimal('cash_system', 14, 2)->default(0);
            $table->decimal('transfer_income', 14, 2)->default(0);
            $table->decimal('debt_amount', 14, 2)->default(0);
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_closings');
    }
};
