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
        Schema::create('anonymous_users', function (Blueprint $table) {
            $table->id();
            $table->date('birthday')->nullable(); // Always the 1st - Month & Year relevant
            $table->string('nationality')->nullable();
            $table->enum('gender', ['M', 'F', 'N/A'])->default('N/A');
            $table->string('region')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anonymous_users');
    }
};
