<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\NotificationChannel;
use Illuminate\Database\Seeder;

class NotificationChannelSeeder extends Seeder
{
    public function run(): void
    {
        $channels = [
            [
                'title' => 'E-Mail',
                'icon' => 'heroicon-o-envelope',
                'route' => 'mail',
            ],
            [
                'title' => 'Pr0gramm PN',
                'icon' => 'icon-pr0gramm',
                'route' => 'pr0gramm',
            ],
        ];

        foreach ($channels as $channel) {
            NotificationChannel::create($channel);
        }
    }
}
