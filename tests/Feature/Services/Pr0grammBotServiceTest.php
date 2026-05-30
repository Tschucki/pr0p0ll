<?php

declare(strict_types=1);

use App\Services\Pr0grammBotService;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Http;
use Tschucki\Pr0grammApi\Pr0grammApi;

beforeEach(function () {
    // Facade cached statischen Cookie über Tests hinweg — zurücksetzen.
    Facade::clearResolvedInstance(Pr0grammApi::class);
    config([
        'services.pr0gramm.cookie' => 'me=%7B%22id%22%3A%22abcdef0123456789zz%22%7D',
        'services.pr0gramm.username' => 'pr0p0ll_bot',
    ]);
});

it('returns the newest bot upload created at or after the upload timestamp', function () {
    $after = now()->timestamp;
    Http::fake([
        '*items/get*' => Http::response(['items' => [
            ['id' => 7031800, 'created' => $after + 5, 'user' => 'pr0p0ll_bot'],
            ['id' => 6999999, 'created' => $after - 3600, 'user' => 'pr0p0ll_bot'],
        ]]),
    ]);

    $itemId = app(Pr0grammBotService::class)->findRecentUploadItemId($after);

    expect($itemId)->toBe(7031800);
    Http::assertSent(fn ($request) => str_contains($request->url(), 'items/get') && $request['user'] === 'pr0p0ll_bot');
});

it('returns null while the upload is still processing and not yet listed', function () {
    $after = now()->timestamp;
    Http::fake([
        '*items/get*' => Http::response(['items' => [
            ['id' => 6999999, 'created' => $after - 3600, 'user' => 'pr0p0ll_bot'],
        ]]),
    ]);

    expect(app(Pr0grammBotService::class)->findRecentUploadItemId($after))->toBeNull();
});

it('logs in via the bot account when not yet authenticated', function () {
    config(['services.pr0gramm.cookie' => null]);
    config(['services.pr0gramm.username' => 'pr0p0ll_bot', 'services.pr0gramm.password' => 'secret']);

    Http::fake([
        '*user/loggedin' => Http::response(['loggedIn' => false], 200),
        '*user/login' => Http::response(['success' => true], 200, [
            'Set-Cookie' => 'me=%7B%22id%22%3A%22abcdef0123456789zz%22%7D',
        ]),
    ]);

    app(Pr0grammBotService::class)->ensureLoggedIn();

    Http::assertSent(fn ($request) => str_contains($request->url(), 'user/login'));
});
