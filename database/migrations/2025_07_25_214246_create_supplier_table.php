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
        Schema::create('supplier', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name', 191);
            $table->string('email', 191)->unique('supplier_email_key');
            $table->string('password_hash', 191);
            $table->enum('role', ['SUPPLIER_ADMIN', 'SUPPLIER_EMPLOYEE']);
            $table->string('contact_person', 191)->nullable();
            $table->string('phone', 191);
            $table->string('address', 191);
            $table->integer('warehouseId')->index('supplier_warehouseid_fkey');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier');
    }
};
