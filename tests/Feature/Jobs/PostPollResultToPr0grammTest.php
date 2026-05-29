<?php

declare(strict_types=1);

use App\Jobs\PostPollResultToPr0gramm;
use App\Jobs\ResolvePr0grammPostItemId;
use App\Jobs\SendResultPublishedDiscordNotification;
use App\Jobs\SendResultPublishedTelegramNotification;
use App\Services\PollResultScreenshotService;
use App\Services\Pr0grammBotService;
use App\Support\ResultPostConfig;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tschucki\Pr0grammApi\Pr0grammApi;

beforeEach(function () {
    Bus::fake();

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

    // items/post liefert KEINE itemId — pr0gramm verarbeitet das Bild erst asynchron.
    Http::fake([
        '*user/loggedin' => Http::response(['loggedIn' => true], 200),
        '*items/upload' => Http::response(['key' => 'UPLOADKEY'], 200),
        '*items/post' => Http::response(['success' => true], 200),
    ]);
}

function runPostJob($poll, array $aConfig): void
{
    (new PostPollResultToPr0gramm($poll, $aConfig))->handle(app(Pr0grammBotService::class));
}

it('uploads the screenshot, posts with tags and comment, marks the poll as uploaded and dispatches the resolver', function () {
    fakePr0grammHappyPath();
    $poll = makeClosedPoll();
    $config = ResultPostConfig::fromArray([
        'tags' => 'pr0p0ll,Spezialtag',
        'comment' => 'Spezialkommentar',
    ], $poll);

    runPostJob($poll, $config->toArray());

    expect($poll->fresh()->original_content_link)->toBeNull()
        ->and($poll->fresh()->result_post_uploaded_at)->not->toBeNull();

    Http::assertSent(fn ($request) => str_contains($request->url(), 'items/post')
        && $request['tags'] === 'pr0p0ll,Spezialtag'
        && $request['comment'] === 'Spezialkommentar'
        && $request['key'] === 'UPLOADKEY');

    Bus::assertDispatched(ResolvePr0grammPostItemId::class, function (ResolvePr0grammPostItemId $job) use ($poll) {
        return (new ReflectionProperty($job, 'expectedTitleTag'))->getValue($job) === ResultPostConfig::titleTag($poll);
    });

    Bus::assertNotDispatched(SendResultPublishedTelegramNotification::class);
    Bus::assertNotDispatched(SendResultPublishedDiscordNotification::class);
});

it('falls back to auto tags and comment and links the unsigned filament results page', function () {
    fakePr0grammHappyPath();
    $poll = makeClosedPoll();

    runPostJob($poll, ResultPostConfig::default($poll)->toArray());

    Http::assertSent(fn ($request) => str_contains($request->url(), 'items/post')
        && str_contains((string) $request['tags'], 'Auswertung')
        && str_contains((string) $request['comment'], '/pr0p0ll/umfragen/'.$poll->getKey().'/auswertung')
        && str_contains((string) $request['siteUrl'], '/pr0p0ll/umfragen/'.$poll->getKey().'/auswertung'));
});

it('does nothing when the poll is no longer eligible', function () {
    fakePr0grammHappyPath();
    $poll = makeClosedPoll();
    $poll->update(['original_content_link' => 'https://pr0gramm.com/new/1']);

    runPostJob($poll, ResultPostConfig::default($poll)->toArray());

    expect($poll->fresh()->original_content_link)->toBe('https://pr0gramm.com/new/1');
    Http::assertNotSent(fn ($request) => str_contains($request->url(), 'items/post'));
    Bus::assertNotDispatched(ResolvePr0grammPostItemId::class);
});

it('logs in when the bot session is not yet authenticated and then hands off to the resolver', function () {
    config(['services.pr0gramm.cookie' => null]);
    config(['services.pr0gramm.username' => 'bot', 'services.pr0gramm.password' => 'secret']);
    $poll = makeClosedPoll();

    Http::fake([
        '*user/loggedin' => Http::response(['loggedIn' => false], 200),
        '*user/login' => Http::response(['success' => true], 200, [
            'Set-Cookie' => 'me=%7B%22id%22%3A%22abcdef0123456789zz%22%7D',
        ]),
        '*items/upload' => Http::response(['key' => 'UPLOADKEY'], 200),
        '*items/post' => Http::response(['success' => true], 200),
    ]);

    runPostJob($poll, ResultPostConfig::default($poll)->toArray());

    Http::assertSent(fn ($request) => str_contains($request->url(), 'user/login'));
    expect($poll->fresh()->result_post_uploaded_at)->not->toBeNull();
    Bus::assertDispatched(ResolvePr0grammPostItemId::class);
});
