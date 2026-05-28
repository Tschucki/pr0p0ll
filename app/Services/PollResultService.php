<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Abstracts\Poll;
use App\Models\Answer;
use App\Models\AnswerTypes\BoolAnswer;
use App\Models\AnswerTypes\ColorAnswer;
use App\Models\AnswerTypes\DateAnswer;
use App\Models\AnswerTypes\DateTimeAnswer;
use App\Models\AnswerTypes\MultipleChoiceAnswer;
use App\Models\AnswerTypes\NumberAnswer;
use App\Models\AnswerTypes\SingleOptionAnswer;
use App\Models\AnswerTypes\TextAnswer;
use App\Models\AnswerTypes\TimeAnswer;
use App\Models\Question;
use App\Support\ResultPostConfig;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

// Baut reine View-Model-Daten für die Auswertung, entkoppelt von Filament und Chart-Libs (screenshot-tauglich).
class PollResultService
{
    public function __construct(private Poll $poll, private array $aFilters = []) {}

    public function buildEvaluation(ResultPostConfig $config): array
    {
        $aResults = $this->getQuestionResults();

        $aQuestions = [];
        foreach ($this->poll->questions as $question) {
            $id = $question->getKey();
            $aQuestionConfig = $config->questionConfig($id);
            if (! $aQuestionConfig['display']) {
                continue;
            }

            $vm = $aResults[$id];
            $vm['title'] = $aQuestionConfig['title'] !== '' ? $aQuestionConfig['title'] : (string) $question->title;
            $vm['description'] = $aQuestionConfig['description'];
            $vm['chart'] = $this->resolveChart($vm['type'], $aQuestionConfig['chart']);
            $vm['footerText'] = $this->footerText($vm);

            if ($vm['type'] === 'text') {
                // Config-Auswahl respektieren, aber nur eindeutige Antworten anzeigen (Count = tatsächliche Zahl).
                $vm['textAnswers'] = array_values(array_filter(
                    $vm['textAnswers'],
                    fn (array $answer) => $config->wantsAnswer($id, $answer['id']),
                ));
                $aSeen = [];
                $vm['textAnswers'] = array_values(array_filter($vm['textAnswers'], function (array $answer) use (&$aSeen) {
                    $key = mb_strtolower(trim((string) $answer['value']));
                    if ($key === '' || isset($aSeen[$key])) {
                        return false;
                    }
                    $aSeen[$key] = true;

                    return true;
                }));
            }

            $aQuestions[] = $this->applyColors($vm, $config->color);
        }

        $demographics = null;
        if ($config->showDemographics) {
            $demographics = $this->applyDemographicColors((new PollDemographicsService($this->poll))->get(), $config->color);
        }

        return [
            'header' => [
                'title' => $config->title,
                'description' => $config->description,
                'color' => $config->color,
            ],
            'questions' => $aQuestions,
            'demographics' => $demographics,
            'footer' => [
                'participants' => $this->poll->participants()->count(),
                'questionCount' => count($aQuestions),
                'period' => $this->periodLabel(),
                'category' => $this->poll->category?->title,
                'author' => $this->poll->not_anonymous ? $this->poll->user?->name : null,
                'pollId' => $this->poll->getKey(),
            ],
        ];
    }

    public function getQuestionResults(): array
    {
        $aResults = [];
        foreach ($this->poll->questions as $question) {
            $aResults[$question->getKey()] = $this->buildQuestionResult($question);
        }

        return $aResults;
    }

    private function buildQuestionResult(Question $question): array
    {
        $type = $question->answerType();

        return match (true) {
            $type instanceof SingleOptionAnswer => $this->optionResult($question, false),
            $type instanceof MultipleChoiceAnswer => $this->optionResult($question, true),
            $type instanceof BoolAnswer => $this->boolResult($question),
            $type instanceof ColorAnswer => $this->colorResult($question),
            $type instanceof NumberAnswer => $this->numberResult($question),
            $type instanceof DateAnswer => $this->temporalResult($question, 'date'),
            $type instanceof DateTimeAnswer => $this->temporalResult($question, 'datetime'),
            $type instanceof TimeAnswer => $this->temporalResult($question, 'time'),
            $type instanceof TextAnswer => $this->textResult($question),
            default => $this->emptyResult($question),
        };
    }

    private function optionResult(Question $question, bool $multiSelect): array
    {
        $morphClass = get_class($question->answerType());
        $aCounts = $this->valueCounts($question, $morphClass);
        $totalAnswers = $this->totalAnswers($question, $morphClass);
        $respondents = $this->respondents($question, $morphClass);
        $base = $multiSelect ? max($respondents, 1) : max($totalAnswers, 1);

        $aOptions = [];
        foreach ($question->options ?? [] as $option) {
            $label = (string) ($option['title'] ?? '');
            $count = (int) ($aCounts[$label] ?? 0);
            $aOptions[] = [
                'label' => $label,
                'count' => $count,
                'percentage' => round($count / $base * 100, 1),
                'helperText' => $option['helperText'] ?? null,
            ];
        }

        return [
            'questionId' => $question->getKey(),
            'type' => 'options',
            'totalAnswers' => $totalAnswers,
            'totalRespondents' => $respondents,
            'isMultiSelect' => $multiSelect,
            'options' => $aOptions,
        ];
    }

    private function boolResult(Question $question): array
    {
        $aCounts = $this->valueCounts($question, BoolAnswer::class);
        $true = (int) ($aCounts[1] ?? $aCounts['1'] ?? 0);
        $false = (int) ($aCounts[0] ?? $aCounts['0'] ?? 0);
        $base = max($true + $false, 1);

        return [
            'questionId' => $question->getKey(),
            'type' => 'bool',
            'totalAnswers' => $true + $false,
            'totalRespondents' => $true + $false,
            'isMultiSelect' => false,
            'options' => [
                ['label' => 'Ja', 'count' => $true, 'percentage' => round($true / $base * 100, 1)],
                ['label' => 'Nein', 'count' => $false, 'percentage' => round($false / $base * 100, 1)],
            ],
        ];
    }

    private function colorResult(Question $question): array
    {
        $aCounts = $this->valueCounts($question, ColorAnswer::class);
        $total = max((int) $aCounts->sum(), 1);

        $aOptions = $aCounts
            ->map(fn (int $count, string $hex) => [
                'label' => $hex,
                'count' => $count,
                'percentage' => round($count / $total * 100, 1),
                'color' => $hex,
            ])
            ->sortByDesc('count')
            ->values()
            ->all();

        return [
            'questionId' => $question->getKey(),
            'type' => 'color',
            'totalAnswers' => (int) $aCounts->sum(),
            'totalRespondents' => (int) $aCounts->sum(),
            'isMultiSelect' => false,
            'options' => $aOptions,
        ];
    }

    private function numberResult(Question $question): array
    {
        $aValues = $this->rawValues($question, NumberAnswer::class)
            ->map(fn ($value) => (int) $value)
            ->sort()
            ->values();

        return [
            'questionId' => $question->getKey(),
            'type' => 'number',
            'totalAnswers' => $aValues->count(),
            'totalRespondents' => $aValues->count(),
            'isMultiSelect' => false,
            'histogram' => $this->numberHistogram($aValues),
            'stats' => $this->numberStats($aValues),
        ];
    }

    private function temporalResult(Question $question, string $type): array
    {
        $morphMap = ['date' => DateAnswer::class, 'datetime' => DateTimeAnswer::class, 'time' => TimeAnswer::class];
        $aValues = $this->rawValues($question, $morphMap[$type])->filter(fn ($value) => filled($value));
        $total = max($aValues->count(), 1);

        $grouped = $aValues
            ->groupBy(function ($value) use ($type) {
                $carbon = Carbon::parse((string) $value);

                return match ($type) {
                    'time' => $carbon->format('H'),
                    default => $carbon->format('Y-m'),
                };
            })
            ->sortKeys();

        $aHistogram = $grouped->map(fn (Collection $group, string $key) => [
            'label' => $this->temporalLabel($key, $type),
            'count' => $group->count(),
            'percentage' => round($group->count() / $total * 100, 1),
        ])->values()->all();

        return [
            'questionId' => $question->getKey(),
            'type' => $type,
            'totalAnswers' => $aValues->count(),
            'totalRespondents' => $aValues->count(),
            'isMultiSelect' => false,
            'histogram' => $aHistogram,
            'stats' => null,
        ];
    }

    private function textResult(Question $question): array
    {
        $answers = $this->answerQuery($question, TextAnswer::class)
            ->with('answerable')
            ->get()
            ->filter(fn (Answer $answer) => filled($answer->answerable?->answer_value));

        $aTextAnswers = $answers->map(fn (Answer $answer) => [
            'id' => $answer->getKey(),
            'value' => (string) $answer->answerable->answer_value,
        ])->values()->all();

        return [
            'questionId' => $question->getKey(),
            'type' => 'text',
            'totalAnswers' => count($aTextAnswers),
            'totalRespondents' => count($aTextAnswers),
            'isMultiSelect' => false,
            'textAnswers' => $aTextAnswers,
        ];
    }

    private function emptyResult(Question $question): array
    {
        return [
            'questionId' => $question->getKey(),
            'type' => 'unsupported',
            'totalAnswers' => 0,
            'totalRespondents' => 0,
            'isMultiSelect' => false,
            'options' => [],
        ];
    }

    private function answerQuery(Question $question, string $morphClass): HasMany
    {
        return $question->answers()
            ->filter($this->aFilters)
            ->where('answerable_type', $morphClass);
    }

    // Gruppierte Counts je answer_value via Join (effizient, filter-fest).
    private function valueCounts(Question $question, string $morphClass): Collection
    {
        $table = (new $morphClass)->getTable();

        return $this->answerQuery($question, $morphClass)
            ->join($table, $table.'.id', '=', 'answers.answerable_id')
            ->groupBy($table.'.answer_value')
            ->selectRaw($table.'.answer_value as value, count(*) as aggregate')
            ->pluck('aggregate', 'value')
            ->map(fn ($count) => (int) $count);
    }

    private function rawValues(Question $question, string $morphClass): Collection
    {
        $table = (new $morphClass)->getTable();

        return $this->answerQuery($question, $morphClass)
            ->join($table, $table.'.id', '=', 'answers.answerable_id')
            ->pluck($table.'.answer_value');
    }

    private function totalAnswers(Question $question, string $morphClass): int
    {
        return (int) $this->answerQuery($question, $morphClass)->count();
    }

    private function respondents(Question $question, string $morphClass): int
    {
        return (int) $this->answerQuery($question, $morphClass)
            ->distinct()
            ->count('anonymous_user_id');
    }

    private function numberHistogram(Collection $aValues): array
    {
        if ($aValues->isEmpty()) {
            return [];
        }

        $min = (int) $aValues->first();
        $max = (int) $aValues->last();
        $total = $aValues->count();

        if ($min === $max) {
            return [['label' => (string) $min, 'count' => $total, 'percentage' => 100.0]];
        }

        $buckets = min(8, $max - $min + 1);
        $size = (int) ceil(($max - $min + 1) / $buckets);

        $aHistogram = [];
        for ($start = $min; $start <= $max; $start += $size) {
            $end = min($start + $size - 1, $max);
            $count = $aValues->filter(fn (int $value) => $value >= $start && $value <= $end)->count();
            $aHistogram[] = [
                'label' => $start === $end ? (string) $start : $start.'–'.$end,
                'count' => $count,
                'percentage' => round($count / $total * 100, 1),
            ];
        }

        return $aHistogram;
    }

    private function numberStats(Collection $aValues): ?array
    {
        if ($aValues->isEmpty()) {
            return null;
        }

        return [
            'min' => (int) $aValues->first(),
            'max' => (int) $aValues->last(),
            'avg' => round((float) $aValues->average(), 1),
            'median' => round((float) $aValues->median(), 1),
        ];
    }

    private function temporalLabel(string $key, string $type): string
    {
        return match ($type) {
            'time' => $key.' Uhr',
            default => Carbon::createFromFormat('Y-m', $key)->translatedFormat('M Y'),
        };
    }

    private function resolveChart(string $type, string $configuredChart): string
    {
        return match ($type) {
            'options', 'bool' => $configuredChart,
            'color' => ResultPostConfig::CHART_BAR,
            'number', 'date', 'datetime', 'time' => 'histogram',
            'text' => 'text',
            default => 'unsupported',
        };
    }

    private function footerText(array $vm): string
    {
        $text = 'Es wurden '.$vm['totalAnswers'].' Antworten abgegeben.';
        if (! empty($vm['isMultiSelect'])) {
            $text .= ' (Mehrfachauswahl möglich)';
        }

        return $text;
    }

    private function periodLabel(): string
    {
        $from = $this->poll->published_at ? Carbon::make($this->poll->published_at)->format('d.m.Y') : '–';
        $to = $this->poll->closes_at ? Carbon::make($this->poll->closes_at)->format('d.m.Y') : '–';

        return $from.' – '.$to;
    }

    private function applyColors(array $vm, string $accent): array
    {
        if ($vm['type'] === 'color') {
            $vm['options'] = array_map(fn (array $option) => ['color' => $accent, ...$option], $vm['options']);

            return $vm;
        }

        if (in_array($vm['type'], ['options', 'bool'], true)) {
            if ($vm['chart'] === ResultPostConfig::CHART_DONUT) {
                $aColors = $vm['type'] === 'bool'
                    ? ['#5cb85c', $accent]
                    : $this->steppedColors($accent, count($vm['options']));
            } else {
                $aColors = array_fill(0, count($vm['options']), $accent);
            }

            foreach ($vm['options'] as $index => $option) {
                $vm['options'][$index]['color'] = $aColors[$index] ?? $accent;
            }

            return $vm;
        }

        if (isset($vm['histogram'])) {
            foreach ($vm['histogram'] as $index => $bucket) {
                $vm['histogram'][$index]['color'] = $accent;
            }
        }

        return $vm;
    }

    private function applyDemographicColors(array $demographics, string $accent): array
    {
        $aGenderColors = ['Männlich' => '#5b9bd5', 'Weiblich' => '#ed6ea0', 'Keine Angabe' => '#9aa0a6'];
        foreach ($demographics['gender'] as $index => $row) {
            $demographics['gender'][$index]['color'] = $aGenderColors[$row['label']] ?? $accent;
        }

        foreach (['age', 'regions', 'nationalities'] as $section) {
            foreach ($demographics[$section] as $index => $row) {
                $demographics[$section][$index]['color'] = $accent;
            }
        }

        return $demographics;
    }

    private function steppedColors(string $hex, int $count): array
    {
        if ($count <= 1) {
            return [$this->withAlpha($hex, 1.0)];
        }

        $aColors = [];
        for ($i = 0; $i < $count; $i++) {
            $aColors[] = $this->withAlpha($hex, 1.0 - ($i / ($count - 1)) * 0.55);
        }

        return $aColors;
    }

    private function withAlpha(string $hex, float $alpha): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        $alphaHex = str_pad(dechex((int) round(max(0.0, min(1.0, $alpha)) * 255)), 2, '0', STR_PAD_LEFT);

        return '#'.$hex.$alphaHex;
    }
}
