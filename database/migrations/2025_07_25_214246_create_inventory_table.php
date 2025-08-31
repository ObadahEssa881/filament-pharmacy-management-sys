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
        Schema::create('inventory', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('medicine_id')->index('inventory_medicine_id_fkey');
            $table->enum('location_type', ['PHARMACY', 'WAREHOUSE']);
            $table->integer('quantity');
            $table->decimal('cost_price', 65, 30);
            $table->decimal('selling_price', 65, 30);
            $table->dateTime('expiry_date', 3);
            $table->dateTime('last_updated', 3)->useCurrent();
            $table->integer('pharmacy_id')->nullable()->index('inventory_pharmacy_id_fkey');
            $table->integer('warehouse_id')->nullable()->index('inventory_warehouse_id_fkey');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};
