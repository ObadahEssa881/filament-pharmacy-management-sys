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
        Schema::create('user', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('username', 191);
            $table->string('email', 191)->unique();
            $table->string('password_hash', 191);
            $table->enum('role', ['PHARMACY_OWNER', 'PHARMACIST']);
            $table->dateTime('created_at', 3)->useCurrent();
            $table->integer('pharmacy_id')->index('user_pharmacy_id_fkey');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user');
    }
};
