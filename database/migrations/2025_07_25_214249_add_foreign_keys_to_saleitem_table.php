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
        Schema::table('saleitem', function (Blueprint $table) {
            $table->foreign(['medicine_id'], 'SaleItem_medicine_id_fkey')->references(['id'])->on('medicine')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign(['sale_id'], 'SaleItem_sale_id_fkey')->references(['id'])->on('sale')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('saleitem', function (Blueprint $table) {
            $table->dropForeign('SaleItem_medicine_id_fkey');
            $table->dropForeign('SaleItem_sale_id_fkey');
        });
    }
};
