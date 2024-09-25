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
        Schema::create('booking_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('court_id')->constrained('courts')->onDelete('cascade');
            $table->string('full_name');
            $table->string('phone_number');
            $table->decimal('price', 10, 2);
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('source', ['bot', 'manual']);
            $table->enum('status', ['pending', 'paid', 'canceled'])->default('pending');
            $table->boolean('is_edit')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_items');
    }
};
