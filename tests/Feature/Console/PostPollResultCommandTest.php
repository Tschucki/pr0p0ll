<?php

declare(strict_types=1);

use App\Jobs\PostPollResultToPr0gramm;
use Illuminate\Support\Facades\Queue;

it('dispatches exactly one job for the longest-closed eligible poll', function () {
    Queue::fake();

    $older = makeClosedPoll();
    $older->update(['closes_at' => now()->subWeeks(6)]);
    $newer = makeClosedPoll();
    $newer->update(['closes_at' => now()->subWeeks(3)]);

    $this->artisan('app:post-poll-result')->assertSuccessful();

    Queue::assertPushed(PostPollResultToPr0gramm::class, 1);
    Queue::assertPushed(PostPollResultToPr0gramm::class, function (PostPollResultToPr0gramm $job) use ($older) {
        return (new ReflectionProperty($job, 'poll'))->getValue($job)->getKey() === $older->getKey();
    });
});

it('does nothing when there is no eligible poll', function () {
    Queue::fake();
    makeClosedPoll(resultsPublic: false); // closes_at = now()-1d → nicht eligible

    $this->artisan('app:post-poll-result')->assertSuccessful();

    Queue::assertNothingPushed();
});
