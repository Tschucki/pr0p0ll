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
        Schema::create('participants_2_polls', function (Blueprint $table) {
            $table->id();

            $table->foreignId('participant_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('poll_id')->constrained('polls')->cascadeOnUpdate()->cascadeOnDelete();

            $table->tinyInteger('rating')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participants_2_polls');
    }
};
