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
            $table->decimal('pending_amount', 10, 2)->after('total_amount')->default(0);
        });

        // Initialize pending_amount for existing orders
        DB::statement("
            UPDATE orders 
            SET pending_amount = total_amount - (
                SELECT COALESCE(SUM(payment_amount), 0) 
                FROM payments 
                WHERE payments.order_id = orders.id 
                AND transaction_status = 'completado'
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('pending_amount');
        });
    }
};
