<?php

declare(strict_types=1);

use App\Enums\NotificationType as NotificationTypeEnum;
use App\Models\NotificationType;
use Database\Seeders\NotificationTypeSeeder;

it('labels the participated-poll notification type as result published', function () {
    expect(NotificationTypeEnum::PARTICIPATEDPOLLHASFINISHED->getLabel())
        ->toBe('Auswertung von teilgenommener Umfrage veröffentlicht');
});

it('syncs the renamed title onto the seeded notification type row', function () {
    // Die neue Daten-Migration lässt den Seeder laufen; in der Test-DB ist der Typ daher bereits angelegt.
    expect(NotificationType::query()->where('identifier', NotificationTypeEnum::PARTICIPATEDPOLLHASFINISHED->value)->value('title'))
        ->toBe('Auswertung von teilgenommener Umfrage veröffentlicht');
});

it('updates an outdated title to the new label when seeding again', function () {
    NotificationType::query()
        ->where('identifier', NotificationTypeEnum::PARTICIPATEDPOLLHASFINISHED->value)
        ->update(['title' => 'Teilgenommene Umfrage beendet']);

    (new NotificationTypeSeeder)->run();

    expect(NotificationType::query()->where('identifier', NotificationTypeEnum::PARTICIPATEDPOLLHASFINISHED->value)->value('title'))
        ->toBe('Auswertung von teilgenommener Umfrage veröffentlicht');
});
