<?php

declare(strict_types=1);

use App\Services\PollDemographicsService;

it('aggregates gender distribution across distinct participants', function () {
    $poll = makeClosedPoll();
    $question = addQuestion($poll, 'radio', [['title' => 'A']]);
    addAnswer($question, 'A', makeAnon(['gender' => 'M']));
    addAnswer($question, 'A', makeAnon(['gender' => 'M']));
    addAnswer($question, 'A', makeAnon(['gender' => 'F']));

    $demographics = (new PollDemographicsService($poll))->get();
    $byLabel = collect($demographics['gender'])->keyBy('label');

    expect($demographics['total'])->toBe(3)
        ->and($byLabel['Männlich']['count'])->toBe(2)
        ->and($byLabel['Weiblich']['count'])->toBe(1);
});

it('buckets participant ages and reports the average', function () {
    $poll = makeClosedPoll();
    $question = addQuestion($poll, 'radio', [['title' => 'A']]);
    addAnswer($question, 'A', makeAnon(['birthday' => now()->subYears(20)]));
    addAnswer($question, 'A', makeAnon(['birthday' => now()->subYears(30)]));

    $demographics = (new PollDemographicsService($poll))->get();
    $byLabel = collect($demographics['age'])->keyBy('label');

    expect($demographics['averageAge'])->toBe(25.0)
        ->and($byLabel['18–24']['count'])->toBe(1)
        ->and($byLabel['25–34']['count'])->toBe(1);
});

it('reports top regions', function () {
    $poll = makeClosedPoll();
    $question = addQuestion($poll, 'radio', [['title' => 'A']]);
    addAnswer($question, 'A', makeAnon(['region' => 'bavaria']));
    addAnswer($question, 'A', makeAnon(['region' => 'bavaria']));
    addAnswer($question, 'A', makeAnon(['region' => 'Berlin']));

    $demographics = (new PollDemographicsService($poll))->get();

    expect($demographics['regions'][0])->toMatchArray(['count' => 2]);
});
