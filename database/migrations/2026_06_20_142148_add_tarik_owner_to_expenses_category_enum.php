<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('expenses', function (Blueprint $table) {
                DB::statement("ALTER TABLE expenses MODIFY COLUMN category ENUM('operasional', 'gaji', 'sewa', 'listrik', 'lainnya', 'tarik_owner') NOT NULL DEFAULT 'lainnya'");
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('expenses', function (Blueprint $table) {
                DB::statement("ALTER TABLE expenses MODIFY COLUMN category ENUM('operasional', 'gaji', 'sewa', 'listrik', 'lainnya') NOT NULL DEFAULT 'lainnya'");
            });
        }
    }
};
