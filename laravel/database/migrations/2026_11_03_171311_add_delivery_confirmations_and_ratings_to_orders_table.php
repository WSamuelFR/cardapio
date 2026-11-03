<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('motoboy_confirmed_at')->nullable()->after('status');
            $table->timestamp('customer_confirmed_at')->nullable()->after('motoboy_confirmed_at');
            $table->unsignedTinyInteger('food_rating')->nullable()->after('customer_confirmed_at');
            $table->unsignedTinyInteger('delivery_rating')->nullable()->after('food_rating');
            $table->text('review_comment')->nullable()->after('delivery_rating');
            $table->timestamp('reviewed_at')->nullable()->after('review_comment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'motoboy_confirmed_at',
                'customer_confirmed_at',
                'food_rating',
                'delivery_rating',
                'review_comment',
                'reviewed_at',
            ]);
        });
    }
};
