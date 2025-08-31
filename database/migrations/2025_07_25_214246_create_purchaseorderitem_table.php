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
        Schema::create('purchaseorderitem', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('order_id')->index('purchaseorderitem_order_id_fkey');
            $table->integer('medicine_id')->index('purchaseorderitem_medicine_id_fkey');
            $table->integer('quantity');
            $table->decimal('unit_price', 65, 30);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchaseorderitem');
    }
};
