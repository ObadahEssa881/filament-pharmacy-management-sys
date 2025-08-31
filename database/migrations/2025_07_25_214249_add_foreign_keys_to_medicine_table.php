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
        Schema::table('medicine', function (Blueprint $table) {
            $table->foreign(['category_id'], 'Medicine_category_id_fkey')->references(['id'])->on('category')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign(['company_id'], 'Medicine_company_id_fkey')->references(['id'])->on('company')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign(['supplier_id'], 'Medicine_supplier_id_fkey')->references(['id'])->on('supplier')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medicine', function (Blueprint $table) {
            $table->dropForeign('Medicine_category_id_fkey');
            $table->dropForeign('Medicine_company_id_fkey');
            $table->dropForeign('Medicine_supplier_id_fkey');
        });
    }
};
