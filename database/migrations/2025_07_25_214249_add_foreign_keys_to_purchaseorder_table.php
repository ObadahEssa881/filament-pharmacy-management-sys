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
        Schema::table('purchaseorder', function (Blueprint $table) {
            $table->foreign(['pharmacy_id'], 'PurchaseOrder_pharmacy_id_fkey')->references(['id'])->on('pharmacy')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign(['supplier_id'], 'PurchaseOrder_supplier_id_fkey')->references(['id'])->on('supplier')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchaseorder', function (Blueprint $table) {
            $table->dropForeign('PurchaseOrder_pharmacy_id_fkey');
            $table->dropForeign('PurchaseOrder_supplier_id_fkey');
        });
    }
};
