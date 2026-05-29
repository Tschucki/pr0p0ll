<?php

declare(strict_types=1);

use App\Support\ResultPostConfig;

it('builds a complete default config that always renders', function () {
    $poll = makeClosedPoll();
    $options = addQuestion($poll, 'radio', [['title' => 'A']]);
    $bool = addQuestion($poll, 'toggle');

    $config = ResultPostConfig::default($poll->fresh());

    expect($config->title)->toBe($poll->title)
        ->and($config->color)->toBe('#ee4d2e')
        ->and($config->showDemographics)->toBeTrue()
        ->and($config->questionConfig($options->getKey())['display'])->toBeTrue()
        ->and($config->questionConfig($options->getKey())['chart'])->toBe(ResultPostConfig::CHART_BAR)
        ->and($config->questionConfig($bool->getKey())['chart'])->toBe(ResultPostConfig::CHART_DONUT);
});

it('normalizes stored config and fills gaps for new questions', function () {
    $poll = makeClosedPoll();
    $stored = addQuestion($poll, 'radio', [['title' => 'A']]);
    $added = addQuestion($poll, 'radio', [['title' => 'B']]);

    $config = ResultPostConfig::fromArray([
        'color' => '#008fff',
        'questions' => [
            $stored->getKey() => ['display' => false],
        ],
    ], $poll->fresh());

    expect($config->color)->toBe('#008fff')
        ->and($config->questionConfig($stored->getKey())['display'])->toBeFalse()
        ->and($config->questionConfig($added->getKey())['display'])->toBeTrue();
});

it('falls back to the default when no config is stored', function () {
    $poll = makeClosedPoll();
    addQuestion($poll, 'radio', [['title' => 'A']]);

    $config = ResultPostConfig::fromArray(null, $poll->fresh());

    expect($config->toArray())->toEqual(ResultPostConfig::default($poll->fresh())->toArray());
});

it('round-trips through the flat form representation', function () {
    $poll = makeClosedPoll();
    addQuestion($poll, 'radio', [['title' => 'A']]);

    $default = ResultPostConfig::default($poll->fresh());
    $roundTripped = ResultPostConfig::fromFlatForm($default->toFlatForm(), $poll->fresh());

    expect($roundTripped->toArray())->toEqual($default->toArray());
});

it('forces non-switchable question types back to their default chart', function () {
    $poll = makeClosedPoll();
    $text = addQuestion($poll, 'text');

    $config = ResultPostConfig::fromArray([
        'questions' => [
            $text->getKey() => ['chart' => 'donut'],
        ],
    ], $poll->fresh());

    expect($config->questionConfig($text->getKey())['chart'])->toBe(ResultPostConfig::CHART_BAR);
});

it('defaults tags and comment to null so the job can resolve them dynamically', function () {
    $poll = makeClosedPoll();

    $config = ResultPostConfig::default($poll);

    expect($config->tags)->toBeNull()
        ->and($config->comment)->toBeNull();
});

it('round-trips tags and comment through toArray and fromArray', function () {
    $poll = makeClosedPoll();

    $config = ResultPostConfig::fromArray([
        'tags' => 'pr0p0ll,Sonderfall',
        'comment' => 'Mein Kommentar',
    ], $poll);

    expect($config->tags)->toBe('pr0p0ll,Sonderfall')
        ->and($config->comment)->toBe('Mein Kommentar')
        ->and($config->toArray())->toMatchArray([
            'tags' => 'pr0p0ll,Sonderfall',
            'comment' => 'Mein Kommentar',
        ]);
});

it('treats blank tags and comment from the flat form as null', function () {
    $poll = makeClosedPoll();

    $config = ResultPostConfig::fromFlatForm(['tags' => '', 'comment' => '  '], $poll);

    expect($config->tags)->toBeNull()
        ->and($config->comment)->toBeNull();
});

it('builds auto tags and an auto comment containing a signed results link', function () {
    $poll = makeClosedPoll();

    expect(ResultPostConfig::defaultTags($poll))
        ->toContain('pr0p0ll', 'Umfrage', 'Auswertung');

    expect(ResultPostConfig::defaultComment($poll))
        ->toContain('/umfragen/'.$poll->getKey().'/auswertung')
        ->toContain('signature=')
        ->toContain('@'.$poll->user->name)
        ->toContain('Bei Fragen und Anregungen bitte @PimmelmannJones schreiben');
});
