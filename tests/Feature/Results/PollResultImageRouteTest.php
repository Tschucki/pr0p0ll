<?php

declare(strict_types=1);

use App\Jobs\GenerateResultPostScreenshot;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('lets the owner download a generated result image', function () {
    Storage::fake();
    $owner = User::factory()->create();
    $poll = makeClosedPoll($owner);
    Storage::put(GenerateResultPostScreenshot::pathFor($poll->getKey()), 'PNGDATA');

    $this->actingAs($owner)
        ->get(route('poll.results.image', ['poll' => $poll->getKey()]))
        ->assertOk()
        ->assertDownload('auswertung-'.$poll->getKey().'.png');
});

it('returns 404 when no image has been generated yet', function () {
    Storage::fake();
    $owner = User::factory()->create();
    $poll = makeClosedPoll($owner);

    $this->actingAs($owner)
        ->get(route('poll.results.image', ['poll' => $poll->getKey()]))
        ->assertNotFound();
});

it('forbids a stranger from downloading a non-public result image', function () {
    Storage::fake();
    $poll = makeClosedPoll(resultsPublic: false);
    Storage::put(GenerateResultPostScreenshot::pathFor($poll->getKey()), 'PNGDATA');

    $this->actingAs(User::factory()->create())
        ->get(route('poll.results.image', ['poll' => $poll->getKey()]))
        ->assertForbidden();
});

it('requires authentication', function () {
    $poll = makeClosedPoll();

    $this->get(route('poll.results.image', ['poll' => $poll->getKey()]))
        ->assertRedirect();
});
