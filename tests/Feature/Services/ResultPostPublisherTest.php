<?php

declare(strict_types=1);

use App\Jobs\SendParticipatedPollResultPublishedEmailNotification;
use App\Jobs\SendParticipatedPollResultPublishedPr0grammNotification;
use App\Jobs\SendResultPublishedDiscordNotification;
use App\Jobs\SendResultPublishedTelegramNotification;
use App\Models\NotificationChannel;
use App\Models\NotificationType;
use App\Models\User;
use App\Services\ResultPostPublisher;
use Database\Seeders\NotificationChannelSeeder;
use Illuminate\Support\Facades\Bus;

beforeEach(function () {
    Bus::fake();
});

it('writes the post url and dispatches the result-published notifications', function () {
    $poll = makeClosedPoll();

    app(ResultPostPublisher::class)->publish($poll, 4242);

    expect($poll->fresh()->original_content_link)->toBe('https://pr0gramm.com/new/4242');
    Bus::assertDispatched(SendResultPublishedTelegramNotification::class);
    Bus::assertDispatched(SendResultPublishedDiscordNotification::class);
});

it('dispatches participant notification jobs for opted-in participants', function () {
    (new NotificationChannelSeeder)->run();

    $poll = makeClosedPoll();
    $participant = User::factory()->create([
        'email' => 'teilnehmer@example.com',
        'email_verified_at' => now(),
    ]);

    $type = NotificationType::where('identifier', App\Enums\NotificationType::PARTICIPATEDPOLLHASFINISHED)->firstOrFail();
    $mailChannel = NotificationChannel::where('route', 'mail')->firstOrFail();
    $pr0grammChannel = NotificationChannel::where('route', 'pr0gramm')->firstOrFail();

    $participant->notificationSettings()->create([
        'notification_type_id' => $type->getKey(),
        'notification_channel_id' => $mailChannel->getKey(),
        'enabled' => true,
    ]);
    $participant->notificationSettings()->create([
        'notification_type_id' => $type->getKey(),
        'notification_channel_id' => $pr0grammChannel->getKey(),
        'enabled' => true,
    ]);

    $poll->participants()->attach($participant->getKey());

    app(ResultPostPublisher::class)->publish($poll, 77);

    Bus::assertDispatched(SendParticipatedPollResultPublishedEmailNotification::class, function ($job) use ($participant) {
        $reflection = new ReflectionProperty($job, 'user');
        $reflection->setAccessible(true);

        return $reflection->getValue($job)->getKey() === $participant->getKey();
    });
    Bus::assertDispatched(SendParticipatedPollResultPublishedPr0grammNotification::class, function ($job) use ($participant) {
        $reflection = new ReflectionProperty($job, 'user');
        $reflection->setAccessible(true);

        return $reflection->getValue($job)->getKey() === $participant->getKey();
    });
});
