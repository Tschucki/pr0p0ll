<?php

declare(strict_types=1);

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
        Schema::create('questions', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained('polls')->cascadeOnDelete()->cascadeOnUpdate();
            $table->text('title');
            $table->foreignId('question_type_id')->constrained('question_types')->cascadeOnDelete()->cascadeOnUpdate();
            $table->json('options')->nullable();

            $table->text('description')->nullable();
            $table->integer('position')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
