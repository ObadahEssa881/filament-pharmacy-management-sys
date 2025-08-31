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
        Schema::table('invoice', function (Blueprint $table) {
            $table->foreign(['order_id'], 'Invoice_order_id_fkey')->references(['id'])->on('purchaseorder')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign(['supplier_id'], 'Invoice_supplier_id_fkey')->references(['id'])->on('supplier')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice', function (Blueprint $table) {
            $table->dropForeign('Invoice_order_id_fkey');
            $table->dropForeign('Invoice_supplier_id_fkey');
        });
    }
};
