<?php

declare(strict_types=1);

use App\Filament\Pages\Pr0PostCreator;
use App\Jobs\PostPollResultToPr0gramm;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('pr0p0ll'));
});

it('dispatches the post job when an admin triggers postToPr0gramm', function () {
    Queue::fake();
    $admin = User::factory()->create(['admin' => true]);
    $poll = makeClosedPoll($admin);

    Livewire::actingAs($admin)
        ->test(Pr0PostCreator::class, ['record' => $poll->getKey()])
        ->call('postToPr0gramm')
        ->assertHasNoErrors();

    Queue::assertPushed(PostPollResultToPr0gramm::class, 1);
});

it('forbids a non-admin poll owner from calling postToPr0gramm', function () {
    Queue::fake();
    $owner = User::factory()->create(['admin' => false]);
    $poll = makeClosedPoll($owner);

    Livewire::actingAs($owner)
        ->test(Pr0PostCreator::class, ['record' => $poll->getKey()])
        ->call('postToPr0gramm')
        ->assertForbidden();

    Queue::assertNothingPushed();
});
