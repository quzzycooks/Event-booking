<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('organizer_id')->constrained('users')->cascadeOnDelete();

            $table->string('title');
            $table->text('description');
            $table->string('location');
            $table->dateTime('event_date');
            $table->decimal('price', 10);

            $table->integer('total_seats')->nullable();
            $table->integer('remaining_seats')->nullable();

            $table->enum('status', ['draft', 'published', 'cancelled'])->default('draft');

            $table->timestamps();

            // Indexes for performance
            $table->index('organizer_id');
            $table->index('status');
            $table->index('event_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
