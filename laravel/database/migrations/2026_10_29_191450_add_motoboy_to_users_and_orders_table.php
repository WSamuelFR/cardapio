<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Altera a coluna role na tabela users para incluir motoboy se for MySQL
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'attendant', 'motoboy') NOT NULL DEFAULT 'attendant'");
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('motoboy_id')->nullable()->after('status')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['motoboy_id']);
            $table->dropColumn('motoboy_id');
        });

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'attendant') NOT NULL DEFAULT 'attendant'");
    }
};
