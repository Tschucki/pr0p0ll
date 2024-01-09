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
        Schema::create('multiple_choice_answers', function (Blueprint $table) {
            $table->id();
            $table->string('answer_value');
            $table->timestamps();
        });

        Schema::create('text_answers', function (Blueprint $table) {
            $table->id();
            $table->string('answer_value');
            $table->timestamps();
        });

        Schema::create('bool_answers', function (Blueprint $table) {
            $table->id();
            $table->boolean('answer_value');
            $table->timestamps();
        });

        Schema::create('single_option_answers', function (Blueprint $table) {
            $table->id();
            $table->string('answer_value');
            $table->timestamps();
        });

        Schema::create('color_answers', function (Blueprint $table) {
            $table->id();
            $table->string('answer_value');
            $table->timestamps();
        });

        Schema::create('date_answers', function (Blueprint $table) {
            $table->id();
            $table->date('answer_value');
            $table->timestamps();
        });

        Schema::create('date_time_answers', function (Blueprint $table) {
            $table->id();
            $table->dateTime('answer_value');
            $table->timestamps();
        });

        Schema::create('time_answers', function (Blueprint $table) {
            $table->id();
            $table->time('answer_value');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('multiple_choice_answers');
        Schema::dropIfExists('text_answers');
        Schema::dropIfExists('bool_answers');
        Schema::dropIfExists('single_option_answers');
        Schema::dropIfExists('color_answers');
        Schema::dropIfExists('date_answers');
        Schema::dropIfExists('date_time_answers');
        Schema::dropIfExists('time_answers');
    }
};
