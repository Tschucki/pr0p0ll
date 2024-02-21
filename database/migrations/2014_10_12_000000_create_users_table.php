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
        Schema::create('users', static function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('pr0gramm_identifier')->index();
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->date('birthday')->nullable(); // Always the 1st - Month & Year relevant
            $table->string('nationality')->nullable();
            $table->enum('gender', ['M', 'F', 'N/A'])->default('N/A');
            $table->string('region')->nullable();
            $table->timestamp('banned_at')->nullable();
            $table->string('password'); // Required for Session
            $table->boolean('admin')->default(false);
            $table->boolean('needs_data_review')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
