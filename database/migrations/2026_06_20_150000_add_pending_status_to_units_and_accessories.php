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
            DB::statement("ALTER TABLE units MODIFY COLUMN status ENUM('pending', 'ready', 'sold', 'returned') NOT NULL DEFAULT 'pending'");
        }

        Schema::table('accessories', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved'])->default('pending');
        });

        // Set existing accessories as approved so we don't break existing data/seeders
        DB::table('accessories')->update(['status' => 'approved']);
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            // Update any pending units to ready before dropping status
            DB::table('units')->where('status', 'pending')->update(['status' => 'ready']);
            DB::statement("ALTER TABLE units MODIFY COLUMN status ENUM('ready', 'sold', 'returned') NOT NULL DEFAULT 'ready'");
        }

        Schema::table('accessories', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
