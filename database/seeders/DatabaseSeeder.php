<?php

declare(strict_types=1);

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            QuestionTypeSeeder::class,
            CategorySeeder::class,
            NotificationChannelSeeder::class,
            NotificationTypeSeeder::class,
        ]);

        if (config('app.env') === 'local') {
            $this->call([
                UserSeeder::class,
                PollSeeder::class,
            ]);
        }
    }
}
