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
        Schema::create('invoice', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('order_id')->unique('invoice_order_id_key');
            $table->integer('supplier_id')->index('invoice_supplier_id_fkey');
            $table->dateTime('invoice_date', 3)->useCurrent();
            $table->decimal('total_amount', 65, 30);
            $table->string('payment_status', 191);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice');
    }
};
