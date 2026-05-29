<?php

declare(strict_types=1);

use App\Jobs\GenerateResultPostScreenshot;
use App\Models\User;
use App\Services\PollResultScreenshotService;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\mock;

it('stores the screenshot and notifies the owner with a same-tab download action', function () {
    Storage::fake();

    mock(PollResultScreenshotService::class)
        ->shouldReceive('png')
        ->once()
        ->andReturn('PNGDATA');

    $owner = User::factory()->create();
    $poll = makeClosedPoll($owner);

    (new GenerateResultPostScreenshot($poll, $owner, []))->handle();

    Storage::assertExists(GenerateResultPostScreenshot::pathFor($poll->getKey()));

    $notification = $owner->fresh()->notifications()->firstOrFail();
    $aAction = $notification->data['actions'][0];

    expect($aAction['url'])->toBe(route('poll.results.image', ['poll' => $poll->getKey()]))
        ->and($aAction['shouldOpenUrlInNewTab'])->toBeFalse();
});
