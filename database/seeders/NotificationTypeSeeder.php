<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\NotificationType;
use Illuminate\Database\Seeder;

class NotificationTypeSeeder extends Seeder
{
    public function run(): void
    {

        $types = [
            [
                'identifier' => \App\Enums\NotificationType::NEWPOLLPUBLISHED,
                'title' => \App\Enums\NotificationType::NEWPOLLPUBLISHED->getLabel(),
                'description' => 'Es wurde eine neue Umfrage veröffentlicht und es kann daran teilgenommen werden.',
            ],
            [
                'identifier' => \App\Enums\NotificationType::POLLDECLINED,
                'title' => \App\Enums\NotificationType::POLLDECLINED->getLabel(),
                'description' => 'Deine Umfrage wurde abgelehnt und kann nicht veröffentlicht werden. Benachrichtigung enthält den Grund',
            ],
            [
                'identifier' => \App\Enums\NotificationType::POLLACCEPTED,
                'title' => \App\Enums\NotificationType::POLLACCEPTED->getLabel(),
                'description' => 'Deine Umfrage wurde akzeptiert und ist nun veröffentlicht.',
            ],
            [
                'identifier' => \App\Enums\NotificationType::OWNPOLLHASENDED,
                'title' => \App\Enums\NotificationType::OWNPOLLHASENDED->getLabel(),
                'description' => 'Deine Umfrage wurde beendet und du kannst jetzt einen Post erstellen.',
            ],
            [
                'identifier' => \App\Enums\NotificationType::PARTICIPATEDPOLLHASFINISHED,
                'title' => \App\Enums\NotificationType::PARTICIPATEDPOLLHASFINISHED->getLabel(),
                'description' => 'Eine Umfrage, an der du Teilgenommen hast wurde beendet.',
            ],
            [
                'identifier' => \App\Enums\NotificationType::CREATEPOSTREMINDER,
                'title' => \App\Enums\NotificationType::CREATEPOSTREMINDER->getLabel(),
                'description' => 'Erinnerung daran, dass du einen Post zu deiner Umfrage erstellen solltest. (Einmalig eine Woche nach Umfrageende)',
            ],
        ];

        foreach ($types as $type) {
            NotificationType::create($type);
        }
    }
}
