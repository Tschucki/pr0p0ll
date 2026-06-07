<?php

declare(strict_types=1);

use App\Filament\Pages\PollResults;
use App\Filament\Resources\PublicPollsResource;
use App\Models\Polls\PublicPoll;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('pr0p0ll'));
});

it('allows a non-admin stranger to view public results', function () {
    $poll = makeClosedPoll();
    $publicPoll = PublicPoll::findOrFail($poll->getKey());

    $this->actingAs(User::factory()->create(['admin' => false]));

    expect(PublicPollsResource::canViewResults($publicPoll))->toBeTrue();
});

it('denies a non-admin stranger when results are not public', function () {
    $poll = makeClosedPoll(resultsPublic: false);
    $publicPoll = PublicPoll::findOrFail($poll->getKey());

    $this->actingAs(User::factory()->create(['admin' => false]));

    expect(PublicPollsResource::canViewResults($publicPoll))->toBeFalse();
});

it('allows the poll owner to view non-public results', function () {
    $owner = User::factory()->create(['admin' => false]);
    $poll = makeClosedPoll($owner, resultsPublic: false);
    $publicPoll = PublicPoll::findOrFail($poll->getKey());

    $this->actingAs($owner);

    expect(PublicPollsResource::canViewResults($publicPoll))->toBeTrue();
});

it('allows an admin to view non-public results', function () {
    $poll = makeClosedPoll(resultsPublic: false);
    $publicPoll = PublicPoll::findOrFail($poll->getKey());

    $this->actingAs(User::factory()->create(['admin' => true]));

    expect(PublicPollsResource::canViewResults($publicPoll))->toBeTrue();
});

it('mounts the results page for a non-admin when results are public', function () {
    $poll = makeClosedPoll();

    Livewire::actingAs(User::factory()->create(['admin' => false]))
        ->test(PollResults::class, ['record' => $poll->getKey()])
        ->assertOk();
});

it('returns 403 on the results page for a non-admin when results are not public', function () {
    $poll = makeClosedPoll(resultsPublic: false);

    Livewire::actingAs(User::factory()->create(['admin' => false]))
        ->test(PollResults::class, ['record' => $poll->getKey()])
        ->assertForbidden();
});
