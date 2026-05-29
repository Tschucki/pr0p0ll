<?php

declare(strict_types=1);

use App\Models\Polls\Poll;

it('matches an approved poll closed more than two weeks ago without a post link', function () {
    $poll = makeClosedPoll(); // closes_at = now()-3w, original_content_link = null, approved

    expect($poll->isEligibleForResultPost())->toBeTrue()
        ->and(Poll::query()->eligibleForResultPost()->pluck('id'))->toContain($poll->getKey());
});

it('excludes polls that already have a post link', function () {
    $poll = makeClosedPoll();
    $poll->update(['original_content_link' => 'https://pr0gramm.com/new/123']);

    expect($poll->fresh()->isEligibleForResultPost())->toBeFalse()
        ->and(Poll::query()->eligibleForResultPost()->pluck('id'))->not->toContain($poll->getKey());
});

it('excludes polls closed less than two weeks ago', function () {
    $poll = makeClosedPoll(resultsPublic: false); // closes_at = now()-1d

    expect($poll->isEligibleForResultPost())->toBeFalse()
        ->and(Poll::query()->eligibleForResultPost()->pluck('id'))->not->toContain($poll->getKey());
});
