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
        Schema::create('purchaseorder', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('supplier_id')->index('purchaseorder_supplier_id_fkey');
            $table->integer('pharmacy_id')->index('purchaseorder_pharmacy_id_fkey');
            $table->dateTime('order_date', 3)->useCurrent();
            $table->dateTime('delivery_date', 3)->nullable();
            $table->string('status', 191);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchaseorder');
    }
};
