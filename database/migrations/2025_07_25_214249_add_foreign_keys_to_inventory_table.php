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
        Schema::table('inventory', function (Blueprint $table) {
            $table->foreign(['medicine_id'], 'Inventory_medicine_id_fkey')->references(['id'])->on('medicine')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign(['pharmacy_id'], 'Inventory_pharmacy_id_fkey')->references(['id'])->on('pharmacy')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['warehouse_id'], 'Inventory_warehouse_id_fkey')->references(['id'])->on('warehouse')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->dropForeign('Inventory_medicine_id_fkey');
            $table->dropForeign('Inventory_pharmacy_id_fkey');
            $table->dropForeign('Inventory_warehouse_id_fkey');
        });
    }
};
