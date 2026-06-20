<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fund_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users');
            $table->enum('direction', ['cash_to_atm', 'atm_to_cash'])->comment('cash_to_atm = setor ke ATM; atm_to_cash = tarik tunai dari ATM');
            $table->decimal('amount', 14, 2)->unsigned();
            $table->string('description', 255)->nullable();
            $table->date('transfer_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fund_transfers');
    }
};
