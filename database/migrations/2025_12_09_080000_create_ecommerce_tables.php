<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('monthly_closures', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('month');
            $table->integer('year');
            $table->dateTime('opening_date')->useCurrent();
            $table->dateTime('closing_date')->nullable();
            $table->date('inventory_date')->nullable();
            $table->decimal('total_collected', 12, 2)->default(0);
            $table->integer('processed_orders')->default(0);
            $table->enum('closure_status', ['abierto', 'cerrado', 'procesado'])->default('abierto');
            $table->boolean('in_inventory')->default(0);
            $table->timestamps();

            $table->unique(['month', 'year'], 'unique_month_year');
            $table->index(['month', 'year']);
        });

        Schema::create('products', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->decimal('current_unit_price', 10, 2);
            $table->enum('measurement_unit', ['caja', 'pieza']);
            $table->integer('pieces_per_box')->default(1);
            $table->enum('inventory_status', ['disponible', 'agotado'])->default('disponible');
            $table->enum('product_status', ['activo', 'inactivo'])->default('activo');
            $table->dateTime('updated_act')->useCurrent();
            $table->integer('updated_by_user_id')->nullable();
            $table->timestamps();

            $table->foreign('updated_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['inventory_status', 'product_status']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('user_id');
            $table->integer('monthly_closure_id');
            $table->dateTime('order_date')->useCurrent();
            $table->date('estimated_delivery_date')->nullable();
            $table->dateTime('actual_delivery_date')->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->enum('order_status', ['pendiente', 'confirmado', 'en_camino', 'entregado', 'cancelado'])->default('pendiente');
            $table->enum('payment_method', ['efectivo', 'transferencia']);
            $table->enum('payment_status', ['pendiente', 'abonado', 'liquidado'])->default('pendiente');
            $table->text('observations')->nullable();
            $table->integer('confirmed_by_user_id')->nullable();
            $table->dateTime('confirmation_date')->nullable();
            $table->integer('delivered_by_user_id')->nullable();
            $table->dateTime('delivery_date')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('monthly_closure_id')->references('id')->on('monthly_closures');
            $table->foreign('confirmed_by_user_id')->references('id')->on('users');
            $table->foreign('delivered_by_user_id')->references('id')->on('users');

            $table->index('user_id');
            $table->index('monthly_closure_id');
            $table->index(['order_status', 'payment_status']);
        });

        Schema::create('order_details', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('order_id');
            $table->integer('product_id');
            $table->integer('quantity');
            $table->enum('quantity_type', ['caja', 'pieza']);
            $table->decimal('unit_price_at_order', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products');

            $table->index('order_id');
            $table->index('product_id');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('order_id');
            $table->dateTime('payment_date')->useCurrent();
            $table->decimal('payment_amount', 10, 2);
            $table->enum('payment_method_used', ['efectivo', 'transferencia']);
            $table->string('transfer_reference', 100)->nullable();
            $table->enum('transaction_status', ['completado', 'pendiente', 'fallido'])->default('completado');
            $table->string('receipt_url', 255)->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->index('order_id');
        });

        Schema::create('price_history', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('product_id');
            $table->decimal('previous_price', 10, 2)->nullable();
            $table->decimal('new_price', 10, 2);
            $table->dateTime('change_date')->useCurrent();
            $table->integer('changed_by_user_id');
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('changed_by_user_id')->references('id')->on('users');
            $table->index('product_id');
            $table->index('changed_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_history');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_details');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
        Schema::dropIfExists('monthly_closures');
    }
};
