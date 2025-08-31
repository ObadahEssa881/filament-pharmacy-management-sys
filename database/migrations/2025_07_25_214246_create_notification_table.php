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
        Schema::create('notification', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('user_id')->index('notification_user_id_fkey');
            $table->string('message', 191);
            $table->string('type', 191);
            $table->dateTime('created_at', 3)->useCurrent();
            $table->boolean('is_read')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification');
    }
};
