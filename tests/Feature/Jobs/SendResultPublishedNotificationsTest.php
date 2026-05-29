<?php

declare(strict_types=1);

use App\Jobs\SendResultPublishedDiscordNotification;
use App\Jobs\SendResultPublishedTelegramNotification;
use App\Notifications\Discord\ResultPublishedDiscordNotification;
use App\Notifications\Telegram\ResultPublishedTelegramNotification;
use Illuminate\Support\Facades\Notification;

it('sends the telegram channel notification about an auto-published result', function () {
    Notification::fake();
    $poll = makeClosedPoll();
    $poll->update(['original_content_link' => 'https://pr0gramm.com/new/777']);

    (new SendResultPublishedTelegramNotification($poll))->handle();

    Notification::assertSentOnDemand(ResultPublishedTelegramNotification::class);
});

it('sends the discord channel notification about an auto-published result', function () {
    Notification::fake();
    $poll = makeClosedPoll();
    $poll->update(['original_content_link' => 'https://pr0gramm.com/new/777']);

    (new SendResultPublishedDiscordNotification($poll))->handle();

    Notification::assertSentOnDemand(ResultPublishedDiscordNotification::class);
});
