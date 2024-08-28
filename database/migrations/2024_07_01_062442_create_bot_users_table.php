<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bot_users', function (Blueprint $table) {
            $table->id();
            $table->string('chat_id');
            $table->string('first_name')->nullable();
            $table->string('second_name')->nullable();
            $table->string('uname')->nullable();
            $table->string('typed_name')->nullable();
            $table->string('phone')->nullable();
            $table->integer('sms_code')->nullable();
            $table->string('step')->nullable();
            $table->string('lang')->nullable();
            $table->boolean('isactive')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bot_users');
    }
};
