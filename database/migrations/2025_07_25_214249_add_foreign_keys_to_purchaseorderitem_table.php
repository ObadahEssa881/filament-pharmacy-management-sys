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
        Schema::table('purchaseorderitem', function (Blueprint $table) {
            $table->foreign(['medicine_id'], 'PurchaseOrderItem_medicine_id_fkey')->references(['id'])->on('medicine')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign(['order_id'], 'PurchaseOrderItem_order_id_fkey')->references(['id'])->on('purchaseorder')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchaseorderitem', function (Blueprint $table) {
            $table->dropForeign('PurchaseOrderItem_medicine_id_fkey');
            $table->dropForeign('PurchaseOrderItem_order_id_fkey');
        });
    }
};
