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
        Schema::create('polls', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->text('title');
            $table->fullText('title');
            $table->text('description');
            $table->fullText('description');
            $table->string('closes_after');
            $table->boolean('not_anonymous')->default(true);
            $table->text('original_content_link')->nullable();
            $table->boolean('visible_to_public')->default(false);
            $table->boolean('in_review')->nullable();
            $table->boolean('approved')->default(false);
            $table->dateTime('published_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('polls');
    }
};
