<?php

use App\Models\User;
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
        Schema::create('notification_preferences', static function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class);
            $table->boolean('notify_via_email')->default(false);
            $table->boolean('notify_via_pr0gramm')->default(false);
            $table->boolean('notify_about_new_polls')->default(false);
            $table->boolean('notify_about_poll_reviews')->default(false);
            $table->boolean('notify_about_poll_ends')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
