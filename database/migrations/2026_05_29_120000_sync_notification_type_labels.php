<?php

declare(strict_types=1);

use Database\Seeders\NotificationTypeSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    // Synchronisiert die NotificationType-Titel/Beschreibungen auf bestehende Datenbanken
    // (u.a. das umbenannte PARTICIPATEDPOLLHASFINISHED-Label). Seeder ist idempotent (updateOrCreate).
    public function up(): void
    {
        (new NotificationTypeSeeder)->run();
    }

    public function down(): void
    {
        //
    }
};
