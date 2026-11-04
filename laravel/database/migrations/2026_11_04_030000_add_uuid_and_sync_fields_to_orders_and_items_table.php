<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'uuid')) {
                $table->uuid('uuid')->nullable()->after('id')->index();
            }
            if (!Schema::hasColumn('orders', 'origin')) {
                $table->string('origin', 30)->default('web')->after('order_number')->index();
            }
            if (!Schema::hasColumn('orders', 'client_updated_at')) {
                $table->timestamp('client_updated_at')->nullable()->after('updated_at');
            }
            if (!Schema::hasColumn('orders', 'sync_status')) {
                $table->string('sync_status', 20)->default('synced')->after('origin')->index();
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'uuid')) {
                $table->uuid('uuid')->nullable()->after('id')->index();
            }
        });

        // Preenche UUID para registros existentes caso estejam vazios
        $orders = DB::table('orders')->whereNull('uuid')->get();
        foreach ($orders as $order) {
            DB::table('orders')->where('id', $order->id)->update([
                'uuid' => (string) Str::uuid(),
                'client_updated_at' => $order->updated_at ?? now(),
            ]);
        }

        $items = DB::table('order_items')->whereNull('uuid')->get();
        foreach ($items as $item) {
            DB::table('order_items')->where('id', $item->id)->update([
                'uuid' => (string) Str::uuid(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'uuid')) {
                $table->dropColumn('uuid');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'sync_status')) {
                $table->dropColumn('sync_status');
            }
            if (Schema::hasColumn('orders', 'client_updated_at')) {
                $table->dropColumn('client_updated_at');
            }
            if (Schema::hasColumn('orders', 'origin')) {
                $table->dropColumn('origin');
            }
            if (Schema::hasColumn('orders', 'uuid')) {
                $table->dropColumn('uuid');
            }
        });
    }
};

