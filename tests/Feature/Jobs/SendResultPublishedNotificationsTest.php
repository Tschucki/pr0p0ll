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

it('includes the pr0gramm and pr0.app links in the telegram result notification', function () {
    $poll = makeClosedPoll();
    $poll->update(['original_content_link' => 'https://pr0gramm.com/new/777']);

    $aPayload = (new ResultPublishedTelegramNotification($poll))->toTelegram(new stdClass)->toArray();

    expect($aPayload['text'])
        ->toContain('https://pr0gramm.com/new/777')
        ->toContain('https://pr0.app/new/777');

    $aKeyboard = json_decode((string) $aPayload['reply_markup'], true, 512, JSON_THROW_ON_ERROR);
    $aButtons = collect($aKeyboard['inline_keyboard'])->flatten(1);

    expect($aButtons->pluck('url')->all())
        ->toContain('https://pr0gramm.com/new/777')
        ->toContain('https://pr0.app/new/777');
});

it('sends the discord channel notification about an auto-published result', function () {
    Notification::fake();
    $poll = makeClosedPoll();
    $poll->update(['original_content_link' => 'https://pr0gramm.com/new/777']);

    (new SendResultPublishedDiscordNotification($poll))->handle();

    Notification::assertSentOnDemand(ResultPublishedDiscordNotification::class);
});
