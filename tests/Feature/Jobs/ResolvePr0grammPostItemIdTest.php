<?php

declare(strict_types=1);

use App\Jobs\ResolvePr0grammPostItemId;
use App\Jobs\SendParticipatedPollResultPublishedEmailNotification;
use App\Jobs\SendParticipatedPollResultPublishedPr0grammNotification;
use App\Jobs\SendResultPublishedDiscordNotification;
use App\Jobs\SendResultPublishedTelegramNotification;
use App\Models\NotificationChannel;
use App\Models\NotificationType;
use App\Models\User;
use App\Services\Pr0grammBotService;
use Database\Seeders\NotificationChannelSeeder;
use Illuminate\Support\Facades\Bus;

beforeEach(function () {
    Bus::fake();
});

it('resolves the item id, stores the post url and dispatches the result-published notifications', function () {
    $bot = $this->mock(Pr0grammBotService::class, function ($mock) {
        $mock->shouldReceive('ensureLoggedIn')->once();
        $mock->shouldReceive('findRecentUploadItemId')->once()->andReturn(4242);
    });

    $poll = makeClosedPoll();

    (new ResolvePr0grammPostItemId($poll, 'Test Umfrage', now()->subMinute()->timestamp))->handle($bot);

    expect($poll->fresh()->original_content_link)->toBe('https://pr0gramm.com/new/4242');
    Bus::assertDispatched(SendResultPublishedTelegramNotification::class);
    Bus::assertDispatched(SendResultPublishedDiscordNotification::class);
});

it('re-dispatches itself with the next attempt when the item id is not yet available', function () {
    $bot = $this->mock(Pr0grammBotService::class, function ($mock) {
        $mock->shouldReceive('ensureLoggedIn')->once();
        $mock->shouldReceive('findRecentUploadItemId')->once()->andReturnNull();
    });

    $poll = makeClosedPoll();

    (new ResolvePr0grammPostItemId($poll, 'Test Umfrage', now()->subMinute()->timestamp, attempt: 1))->handle($bot);

    expect($poll->fresh()->original_content_link)->toBeNull();
    Bus::assertDispatched(ResolvePr0grammPostItemId::class, function (ResolvePr0grammPostItemId $job) {
        return (new ReflectionProperty($job, 'attempt'))->getValue($job) === 2;
    });
});

it('fails after the maximum number of attempts without re-dispatching', function () {
    $bot = $this->mock(Pr0grammBotService::class, function ($mock) {
        $mock->shouldReceive('ensureLoggedIn')->once();
        $mock->shouldReceive('findRecentUploadItemId')->once()->andReturnNull();
    });

    $poll = makeClosedPoll();

    (new ResolvePr0grammPostItemId($poll, 'Test Umfrage', now()->subMinute()->timestamp, attempt: ResolvePr0grammPostItemId::MAX_ATTEMPTS))->handle($bot);

    expect($poll->fresh()->original_content_link)->toBeNull();
    Bus::assertNotDispatched(ResolvePr0grammPostItemId::class);
});

it('skips work when the post url has already been resolved', function () {
    $bot = $this->mock(Pr0grammBotService::class, function ($mock) {
        $mock->shouldNotReceive('ensureLoggedIn');
        $mock->shouldNotReceive('findRecentUploadItemId');
    });

    $poll = makeClosedPoll();
    $poll->update(['original_content_link' => 'https://pr0gramm.com/new/1']);

    (new ResolvePr0grammPostItemId($poll, 'Test Umfrage', now()->subMinute()->timestamp))->handle($bot);

    expect($poll->fresh()->original_content_link)->toBe('https://pr0gramm.com/new/1');
});

it('dispatches participant notification jobs for opted-in participants once resolved', function () {
    (new NotificationChannelSeeder)->run();

    $bot = $this->mock(Pr0grammBotService::class, function ($mock) {
        $mock->shouldReceive('ensureLoggedIn')->once();
        $mock->shouldReceive('findRecentUploadItemId')->once()->andReturn(77);
    });

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

    (new ResolvePr0grammPostItemId($poll, 'Test Umfrage', now()->subMinute()->timestamp))->handle($bot);

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
