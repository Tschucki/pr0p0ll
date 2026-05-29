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

it('returns the item id of the newest upload carrying the title tag', function () {
    $after = now()->subMinute()->timestamp;
    Http::fake([
        '*items/get*' => Http::response(['items' => [
            ['id' => 500, 'created' => $after + 30, 'user' => 'pr0p0ll_bot'],
            ['id' => 400, 'created' => $after - 3600, 'user' => 'pr0p0ll_bot'],
        ]]),
        '*items/info*' => Http::response(['tags' => [
            ['tag' => 'pr0p0ll'],
            ['tag' => 'Test Umfrage'],
        ]]),
    ]);

    $id = app(Pr0grammBotService::class)->findRecentUploadItemId('Test Umfrage', $after);

    expect($id)->toBe(500);
    Http::assertSent(fn ($request) => str_contains($request->url(), 'items/info') && (int) $request['itemId'] === 500);
});

it('returns null when no recent upload carries the title tag', function () {
    $after = now()->subMinute()->timestamp;
    Http::fake([
        '*items/get*' => Http::response(['items' => [
            ['id' => 500, 'created' => $after + 30, 'user' => 'pr0p0ll_bot'],
        ]]),
        '*items/info*' => Http::response(['tags' => [
            ['tag' => 'pr0p0ll'],
            ['tag' => 'Ein anderer Titel'],
        ]]),
    ]);

    $id = app(Pr0grammBotService::class)->findRecentUploadItemId('Test Umfrage', $after);

    expect($id)->toBeNull();
});

it('ignores uploads created before the upload timestamp', function () {
    $after = now()->timestamp;
    Http::fake([
        '*items/get*' => Http::response(['items' => [
            ['id' => 400, 'created' => $after - 3600, 'user' => 'pr0p0ll_bot'],
        ]]),
        '*items/info*' => Http::response(['tags' => [['tag' => 'Test Umfrage']]]),
    ]);

    $id = app(Pr0grammBotService::class)->findRecentUploadItemId('Test Umfrage', $after);

    expect($id)->toBeNull();
    Http::assertNotSent(fn ($request) => str_contains($request->url(), 'items/info'));
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
