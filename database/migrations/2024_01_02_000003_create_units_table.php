<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('model_id')->constrained('product_models');
            $table->foreignId('created_by')->constrained('users');
            $table->enum('unit_type', ['baru', 'second']);
            $table->string('ram', 20)->nullable();
            $table->string('rom', 20)->nullable();
            $table->string('color', 50)->nullable();
            $table->string('imei', 20)->nullable()->unique();
            $table->string('serial_number', 50)->nullable();
            $table->decimal('purchase_price', 12, 2);
            $table->string('photo_path')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'ready', 'sold', 'returned'])->default('pending');
            $table->date('purchase_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
