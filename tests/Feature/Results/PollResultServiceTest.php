<?php

declare(strict_types=1);

use App\Services\PollResultService;
use App\Support\ResultPostConfig;

it('aggregates single option answers with counts and percentages', function () {
    $poll = makeClosedPoll();
    $question = addQuestion($poll, 'radio', [['title' => 'Apfel'], ['title' => 'Birne'], ['title' => 'Kirsche']]);
    addAnswer($question, 'Apfel');
    addAnswer($question, 'Apfel');
    addAnswer($question, 'Birne');

    $vm = (new PollResultService($poll))->getQuestionResults()[$question->getKey()];
    $byLabel = collect($vm['options'])->keyBy('label');

    expect($vm['type'])->toBe('options')
        ->and($vm['totalAnswers'])->toBe(3)
        ->and($vm['isMultiSelect'])->toBeFalse()
        ->and($byLabel['Apfel']['count'])->toBe(2)
        ->and($byLabel['Apfel']['percentage'])->toBe(66.7)
        ->and($byLabel['Birne']['count'])->toBe(1)
        ->and($byLabel['Kirsche']['count'])->toBe(0);
});

it('uses respondents as percentage base for multiple choice', function () {
    $poll = makeClosedPoll();
    $question = addQuestion($poll, 'checkbox-list', [['title' => 'A'], ['title' => 'B']]);

    $respondent = makeAnon();
    addAnswer($question, 'A', $respondent);
    addAnswer($question, 'B', $respondent);
    addAnswer($question, 'A');

    $vm = (new PollResultService($poll))->getQuestionResults()[$question->getKey()];
    $byLabel = collect($vm['options'])->keyBy('label');

    expect($vm['isMultiSelect'])->toBeTrue()
        ->and($vm['totalAnswers'])->toBe(3)
        ->and($vm['totalRespondents'])->toBe(2)
        ->and($byLabel['A']['count'])->toBe(2)
        ->and($byLabel['A']['percentage'])->toBe(100.0)
        ->and($byLabel['B']['percentage'])->toBe(50.0);
});

it('aggregates boolean answers into Ja and Nein', function () {
    $poll = makeClosedPoll();
    $question = addQuestion($poll, 'toggle');
    addAnswer($question, true);
    addAnswer($question, true);
    addAnswer($question, false);

    $vm = (new PollResultService($poll))->getQuestionResults()[$question->getKey()];

    expect($vm['type'])->toBe('bool')
        ->and($vm['options'][0])->toMatchArray(['label' => 'Ja', 'count' => 2])
        ->and($vm['options'][1])->toMatchArray(['label' => 'Nein', 'count' => 1]);
});

it('collects non-empty text answers', function () {
    $poll = makeClosedPoll();
    $question = addQuestion($poll, 'text');
    addAnswer($question, 'Hallo');
    addAnswer($question, 'Welt');
    addAnswer($question, '');

    $vm = (new PollResultService($poll))->getQuestionResults()[$question->getKey()];

    expect($vm['type'])->toBe('text')
        ->and($vm['textAnswers'])->toHaveCount(2)
        ->and(collect($vm['textAnswers'])->pluck('value')->all())->toContain('Hallo', 'Welt');
});

it('builds a number histogram with stats', function () {
    $poll = makeClosedPoll();
    $question = addQuestion($poll, 'number');
    foreach ([10, 20, 30] as $value) {
        addAnswer($question, $value);
    }

    $vm = (new PollResultService($poll))->getQuestionResults()[$question->getKey()];

    expect($vm['type'])->toBe('number')
        ->and($vm['stats'])->toMatchArray(['min' => 10, 'max' => 30, 'avg' => 20.0, 'median' => 20.0])
        ->and(collect($vm['histogram'])->sum('count'))->toBe(3);
});

it('aggregates color answers keyed by hex value', function () {
    $poll = makeClosedPoll();
    $question = addQuestion($poll, 'color');
    addAnswer($question, '#ffffff');
    addAnswer($question, '#ffffff');
    addAnswer($question, '#000000');

    $vm = (new PollResultService($poll))->getQuestionResults()[$question->getKey()];
    $byLabel = collect($vm['options'])->keyBy('label');

    expect($vm['type'])->toBe('color')
        ->and($byLabel['#ffffff']['count'])->toBe(2)
        ->and($byLabel['#ffffff']['color'])->toBe('#ffffff')
        ->and($byLabel['#000000']['count'])->toBe(1);
});

it('applies demographic filters to question results', function () {
    $poll = makeClosedPoll();
    $question = addQuestion($poll, 'radio', [['title' => 'A']]);
    addAnswer($question, 'A', makeAnon(['gender' => 'M']));
    addAnswer($question, 'A', makeAnon(['gender' => 'M']));
    addAnswer($question, 'A', makeAnon(['gender' => 'F']));

    $vm = (new PollResultService($poll, ['gender' => 'M']))->getQuestionResults()[$question->getKey()];

    expect($vm['totalAnswers'])->toBe(2)
        ->and(collect($vm['options'])->firstWhere('label', 'A')['count'])->toBe(2);
});

it('assembles an evaluation honouring the config', function () {
    $poll = makeClosedPoll();
    $shown = addQuestion($poll, 'radio', [['title' => 'A']]);
    $hidden = addQuestion($poll, 'radio', [['title' => 'B']]);
    addAnswer($shown, 'A');
    addAnswer($hidden, 'B');

    $config = ResultPostConfig::fromArray([
        'title' => 'Mein Titel',
        'showDemographics' => false,
        'questions' => [
            $shown->getKey() => ['title' => 'Überschrieben', 'chart' => 'donut'],
            $hidden->getKey() => ['display' => false],
        ],
    ], $poll);

    $evaluation = (new PollResultService($poll))->buildEvaluation($config);

    expect($evaluation['header']['title'])->toBe('Mein Titel')
        ->and($evaluation['questions'])->toHaveCount(1)
        ->and($evaluation['questions'][0]['title'])->toBe('Überschrieben')
        ->and($evaluation['questions'][0]['chart'])->toBe('donut')
        ->and($evaluation['demographics'])->toBeNull();
});
