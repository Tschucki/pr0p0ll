<?php

declare(strict_types=1);

use App\Jobs\PostPollResultToPr0gramm;
use App\Jobs\SendParticipatedPollResultPublishedEmailNotification;
use App\Jobs\SendParticipatedPollResultPublishedPr0grammNotification;
use App\Jobs\SendResultPublishedDiscordNotification;
use App\Jobs\SendResultPublishedTelegramNotification;
use App\Models\NotificationChannel;
use App\Models\NotificationType;
use App\Models\User;
use App\Services\PollResultScreenshotService;
use App\Support\ResultPostConfig;
use Database\Seeders\NotificationChannelSeeder;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tschucki\Pr0grammApi\Pr0grammApi;

beforeEach(function () {
    Bus::fake([
        SendResultPublishedTelegramNotification::class,
        SendResultPublishedDiscordNotification::class,
        SendParticipatedPollResultPublishedEmailNotification::class,
        SendParticipatedPollResultPublishedPr0grammNotification::class,
    ]);

    // pr0gramm-Facade cached die Instanz inkl. statischem Cookie über Tests hinweg — zurücksetzen.
    Facade::clearResolvedInstance(Pr0grammApi::class);
    Storage::fake('local');

    // Screenshot-Service mocken, damit Browsershot/Chrome nicht echt läuft.
    $this->mock(PollResultScreenshotService::class)
        ->shouldReceive('png')
        ->andReturn('FAKEPNGBYTES');
});

function fakePr0grammHappyPath(): void
{
    // Bot gilt als eingeloggt → Job überspringt login(); Cookie via config bereitstellen,
    // damit der Facade-Konstruktor einen Cookie + Nonce hat.
    config(['services.pr0gramm.cookie' => 'me=%7B%22id%22%3A%22abcdef0123456789zz%22%7D']);

    Http::fake([
        '*user/loggedin' => Http::response(['loggedIn' => true], 200),
        '*items/upload' => Http::response(['key' => 'UPLOADKEY'], 200),
        '*items/post' => Http::response(['itemId' => 4242], 200),
    ]);
}

it('uploads the screenshot, posts with tags and comment, and writes the post url back', function () {
    fakePr0grammHappyPath();
    $poll = makeClosedPoll();
    $config = ResultPostConfig::fromArray([
        'tags' => 'pr0p0ll,Spezialtag',
        'comment' => 'Spezialkommentar',
    ], $poll);

    (new PostPollResultToPr0gramm($poll, $config->toArray()))->handle();

    expect($poll->fresh()->original_content_link)->toBe('https://pr0gramm.com/new/4242');

    Http::assertSent(fn ($request) => str_contains($request->url(), 'items/post')
        && $request['tags'] === 'pr0p0ll,Spezialtag'
        && $request['comment'] === 'Spezialkommentar'
        && $request['key'] === 'UPLOADKEY');

    Bus::assertDispatched(SendResultPublishedTelegramNotification::class);
    Bus::assertDispatched(SendResultPublishedDiscordNotification::class);
});

it('falls back to auto tags and comment when none are configured', function () {
    fakePr0grammHappyPath();
    $poll = makeClosedPoll();

    (new PostPollResultToPr0gramm($poll, ResultPostConfig::default($poll)->toArray()))->handle();

    Http::assertSent(fn ($request) => str_contains($request->url(), 'items/post')
        && str_contains((string) $request['tags'], 'Auswertung')
        && str_contains((string) $request['comment'], '/auswertung'));
});

it('does nothing when the poll is no longer eligible', function () {
    fakePr0grammHappyPath();
    $poll = makeClosedPoll();
    $poll->update(['original_content_link' => 'https://pr0gramm.com/new/1']);

    (new PostPollResultToPr0gramm($poll, ResultPostConfig::default($poll)->toArray()))->handle();

    expect($poll->fresh()->original_content_link)->toBe('https://pr0gramm.com/new/1');
    Http::assertNotSent(fn ($request) => str_contains($request->url(), 'items/post'));
    Bus::assertNotDispatched(SendResultPublishedTelegramNotification::class);
    Bus::assertNotDispatched(SendResultPublishedDiscordNotification::class);
});

it('logs in when the bot session is not yet authenticated', function () {
    config(['services.pr0gramm.cookie' => null]);
    config(['services.pr0gramm.username' => 'bot', 'services.pr0gramm.password' => 'secret']);
    $poll = makeClosedPoll();

    Http::fake([
        '*user/loggedin' => Http::response(['loggedIn' => false], 200),
        '*user/login' => Http::response(['success' => true], 200, [
            'Set-Cookie' => 'me=%7B%22id%22%3A%22abcdef0123456789zz%22%7D',
        ]),
        '*items/upload' => Http::response(['key' => 'UPLOADKEY'], 200),
        '*items/post' => Http::response(['itemId' => 99], 200),
    ]);

    (new PostPollResultToPr0gramm($poll, ResultPostConfig::default($poll)->toArray()))->handle();

    Http::assertSent(fn ($request) => str_contains($request->url(), 'user/login'));
    expect($poll->fresh()->original_content_link)->toBe('https://pr0gramm.com/new/99');
});

it('dispatches participant notification jobs for opted-in participants after successful post', function () {
    (new NotificationChannelSeeder)->run();
    fakePr0grammHappyPath();

    $poll = makeClosedPoll();
    $participant = User::factory()->create([
        'email' => 'teilnehmer@example.com',
        'email_verified_at' => now(),
    ]);

    // Opt-in für den Teilnahme-Typ anlegen.
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

    (new PostPollResultToPr0gramm($poll, ResultPostConfig::default($poll)->toArray()))->handle();

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
