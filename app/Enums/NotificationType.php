<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum NotificationType: string implements HasLabel
{
    case NEWPOLLPUBLISHED = 'new-poll-published';
    case POLLDECLINED = 'own-poll-declined';
    case POLLACCEPTED = 'own-poll-accepted';
    case OWNPOLLHASENDED = 'own-poll-ended';
    case PARTICIPATEDPOLLHASFINISHED = 'participated-poll-ended';
    case CREATEPOSTREMINDER = 'create-post-reminder';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::NEWPOLLPUBLISHED => 'Neue Umfrage veröffentlicht',
            self::POLLDECLINED => 'Umfrage abgelehnt',
            self::POLLACCEPTED => 'Umfrage angenommen',
            self::OWNPOLLHASENDED => 'Eigene Umfrage beendet',
            self::PARTICIPATEDPOLLHASFINISHED => 'Teilgenommene Umfrage beendet',
            self::CREATEPOSTREMINDER => 'Erinnerung an Beitragserstellung',
        };
    }
}
