<?php

declare(strict_types=1);

use App\Jobs\SendParticipatedPollResultPublishedEmailNotification;
use App\Jobs\SendParticipatedPollResultPublishedPr0grammNotification;
use App\Models\NotificationChannel;
use App\Models\NotificationType;
use App\Models\User;
use App\Notifications\Email\ParticipatedPollResultPublishedEmailNotification;
use App\Notifications\Pr0gramm\ParticipatedPollResultPublishedPr0grammNotification;
use Database\Seeders\NotificationChannelSeeder;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    (new NotificationChannelSeeder)->run();
});

function makeParticipant(): User
{
    return User::factory()->create([
        'email' => 'teilnehmer@example.com',
        'email_verified_at' => now(),
    ]);
}

function optInForType(User $user, string $channelRoute): void
{
    $type = NotificationType::where('identifier', App\Enums\NotificationType::PARTICIPATEDPOLLHASFINISHED)->firstOrFail();
    $channel = NotificationChannel::where('route', $channelRoute)->firstOrFail();

    $user->notificationSettings()->create([
        'notification_type_id' => $type->getKey(),
        'notification_channel_id' => $channel->getKey(),
        'enabled' => true,
    ]);
}

// ── E-Mail-Job ────────────────────────────────────────────────────────────────

it('email job sends when user opted into mail for the type', function () {
    Notification::fake();
    $poll = makeClosedPoll();
    $poll->update(['original_content_link' => 'https://pr0gramm.com/new/999']);
    $user = makeParticipant();
    optInForType($user, 'mail');

    (new SendParticipatedPollResultPublishedEmailNotification($poll, $user))->handle();

    Notification::assertSentOnDemand(ParticipatedPollResultPublishedEmailNotification::class);
});

it('email job does not send when user has not opted in', function () {
    Notification::fake();
    $poll = makeClosedPoll();
    $user = makeParticipant();
    // Keine NotificationSetting angelegt → kein Opt-in.

    (new SendParticipatedPollResultPublishedEmailNotification($poll, $user))->handle();

    Notification::assertNothingSent();
});

it('email job does not send when user email is not verified', function () {
    Notification::fake();
    $poll = makeClosedPoll();
    $user = User::factory()->create([
        'email' => 'unverified@example.com',
        'email_verified_at' => null,
    ]);
    optInForType($user, 'mail');

    (new SendParticipatedPollResultPublishedEmailNotification($poll, $user))->handle();

    Notification::assertNothingSent();
});

// ── Pr0gramm-PN-Job ───────────────────────────────────────────────────────────

it('pr0gramm job sends when user opted into pr0gramm for the type', function () {
    Notification::fake();
    $poll = makeClosedPoll();
    $poll->update(['original_content_link' => 'https://pr0gramm.com/new/999']);
    $user = makeParticipant();
    optInForType($user, 'pr0gramm');

    (new SendParticipatedPollResultPublishedPr0grammNotification($poll, $user))->handle();

    Notification::assertSentTo($user, ParticipatedPollResultPublishedPr0grammNotification::class);
});

it('pr0gramm job does not send when user has not opted in', function () {
    Notification::fake();
    $poll = makeClosedPoll();
    $user = makeParticipant();
    // Keine NotificationSetting angelegt → kein Opt-in.

    (new SendParticipatedPollResultPublishedPr0grammNotification($poll, $user))->handle();

    Notification::assertNothingSent();
});
