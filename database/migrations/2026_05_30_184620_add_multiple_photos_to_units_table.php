<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->string('photo_path_2')->nullable()->after('photo_path');
            $table->string('photo_path_3')->nullable()->after('photo_path_2');
        });
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn(['photo_path_2', 'photo_path_3']);
        });
    }
};
