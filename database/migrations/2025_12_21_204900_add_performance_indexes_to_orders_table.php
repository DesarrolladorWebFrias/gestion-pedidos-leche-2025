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
            // Add indexes safely checking if they don't exist is hard in standard schema builder
            // So we use specific names to avoid collision if possible, or try/catch logic is better handled by just doing it.
            // But standard practice:
            $table->index('user_id', 'orders_user_id_index_perf');
            $table->index('order_status', 'orders_order_status_index_perf');
            $table->index('payment_status', 'orders_payment_status_index_perf');
            $table->index('created_at', 'orders_created_at_index_perf');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_user_id_index_perf');
            $table->dropIndex('orders_order_status_index_perf');
            $table->dropIndex('orders_payment_status_index_perf');
            $table->dropIndex('orders_created_at_index_perf');
        });
    }
};
