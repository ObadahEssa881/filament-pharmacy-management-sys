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
        Schema::create('sale', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('pharmacy_id')->index('sale_pharmacy_id_fkey');
            $table->string('customer_name', 191)->nullable();
            $table->dateTime('sale_date', 3)->useCurrent();
            $table->decimal('total_amount', 65, 30);
            $table->string('payment_mode', 191);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale');
    }
};
