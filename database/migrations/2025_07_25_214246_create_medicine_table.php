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
        Schema::create('medicine', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name', 191);
            $table->string('titer', 191);
            $table->integer('category_id')->index('medicine_category_id_fkey');
            $table->integer('company_id')->index('medicine_company_id_fkey');
            $table->decimal('unit_price', 65, 30);
            $table->integer('supplier_id')->index('medicine_supplier_id_fkey');
            $table->string('Type', 191)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine');
    }
};
