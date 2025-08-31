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
        Schema::create('company', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name', 191)->unique('company_name_key');
            $table->string('contact_person', 191)->nullable();
            $table->string('phone', 191);
            $table->string('email', 191)->unique('company_email_key');
            $table->string('address', 191);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company');
    }
};
