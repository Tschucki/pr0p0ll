<?php

namespace App\Services;

use App\Filament\Resources\MyPollResource\Widgets\ApexAnswerChart;
use App\Models\AnswerTypes\BoolAnswer;
use App\Models\AnswerTypes\MultipleChoiceAnswer;
use App\Models\AnswerTypes\SingleOptionAnswer;
use App\Models\Polls\MyPoll;
use App\Models\Question;
use Filament\Widgets\WidgetConfiguration;
use Illuminate\Support\Collection;

class PollResultService
{
    private MyPoll $poll;

    /**
     * @var mixed|string
     */
    private string $color;

    public function __construct(MyPoll $poll, string $color = '#ee4d2e')
    {
        $this->poll = $poll;
        $this->color = $color;
    }

    public function getAllWidgets(): array
    {
        $widgets = [];
        $this->poll->questions->each(function (Question $question) use (&$widgets) {
            $widgets[] = $this->createResultWidget($question);
        });

        return array_filter($widgets);
    }

    private function createResultWidget(Question $question): ?WidgetConfiguration
    {
        $answerType = $question->answerType();

        return match (true) {
            $answerType instanceof SingleOptionAnswer, $answerType instanceof MultipleChoiceAnswer => $this->getBarChartWidget($question),
            $answerType instanceof BoolAnswer => $this->getBooleanChartWidget($question),
            default => null,
        };
    }

    private function getBooleanChartWidget(Question $question): WidgetConfiguration
    {
        $cacheKeyTrueAnswers = 'poll-'.$question->poll_id.$question->id.'-bool-true-answer-counts';
        $cacheKeyFalseAnswers = 'poll-'.$question->poll_id.$question->id.'-bool-false-answer-counts';
        $cacheTime = $question->poll->resultsArePublic() ? now()->addDay() : now()->addMinutes(5);

        if (\Cache::has($cacheKeyTrueAnswers)) {
            $trueAnswersCount = \Cache::get($cacheKeyTrueAnswers);
        } else {
            $trueAnswersCount = $question->answers()->whereHasMorph('answerable', BoolAnswer::class, function ($query) {
                $query->where('answer_value', true);
            })->count();
            \Cache::add($cacheKeyTrueAnswers, $trueAnswersCount, $cacheTime);
        }
        if (\Cache::has($cacheKeyFalseAnswers)) {
            $falseAnswerCounts = \Cache::get($cacheKeyFalseAnswers);
        } else {
            $falseAnswerCounts = $question->answers()->whereHasMorph('answerable', BoolAnswer::class, function ($query) {
                $query->where('answer_value', false);
            })->count();
            \Cache::add($cacheKeyFalseAnswers, $falseAnswerCounts, $cacheTime);
        }

        $questionAnswerCount = $question->answers->count();
        $footerText = 'Es wurden '.$questionAnswerCount.' Antworten abgegeben.';
        if ($question->questionType->component === 'checkbox-list') {
            $footerText .= ' (Mehrfachauswahl möglich)';
        }

        $answerData = [
            'heading' => $question->title,
            'chartId' => 'chart-'.$question->id,
            'questionId' => $question->getKey(),
            'poll' => $question->poll,
            'footerText' => $footerText,
            'chartOptions' => [
                'chart' => [
                    'fontFamily' => 'var(--font-family),ui-sans-serif,system-ui,sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji"',
                    'type' => 'pie',
                    'height' => 450,
                ],
                'series' => [$trueAnswersCount, $falseAnswerCounts],
                'labels' => ['Ja', 'Nein'],
                'legend' => [
                    'labels' => [
                        'colors' => '#f2f5f4',
                        'fontWeight' => 600,
                        'fontFamily' => 'var(--font-family),ui-sans-serif,system-ui,sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji"',
                    ],
                ],
                'colors' => ['#5cb85c', $this->color],
            ],
        ];

        return ApexAnswerChart::make(['answerData' => $answerData]);
    }

    private function getBarChartWidget(Question $question): WidgetConfiguration
    {
        $options = collect($question->options)->map(function ($option) {
            return $option['title'];
        });

        $questionAnswerCount = $question->answers->count();
        $footerText = 'Es wurden '.$questionAnswerCount.' Antworten abgegeben.';
        if ($question->questionType->component === 'checkbox-list') {
            $footerText .= ' (Mehrfachauswahl möglich)';
        }

        $answerData = [
            'heading' => $question->title,
            'chartId' => 'chart-'.$question->id,
            'questionId' => $question->getKey(),
            'poll' => $question->poll,
            'footerText' => $footerText,
            'chartOptions' => [
                'chart' => [
                    'fontFamily' => 'var(--font-family),ui-sans-serif,system-ui,sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji"',
                    'type' => 'bar',
                    'height' => 450,
                    'toolbar' => [
                        'show' => false,
                    ],

                ],
                'series' => [
                    [
                        'name' => 'Antworten',
                        'data' => $this->getOptionsAnswerCounts($question, $options, get_class($question->answerType()))->values()->toArray(),
                    ],
                ],
                'grid' => [
                    'yaxis' => [
                        'lines' => [
                            'show' => false,
                        ],
                    ],
                ],
                'xaxis' => [
                    'categories' => $options->toArray(),
                    'labels' => [
                        'style' => [
                            'colors' => '#f2f5f4',
                            'fontWeight' => 600,
                            'fontFamily' => 'var(--font-family),ui-sans-serif,system-ui,sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji"',
                        ],
                    ],
                ],
                'yaxis' => [
                    'labels' => [
                        'style' => [
                            'colors' => '#f2f5f4',
                            'fontWeight' => 600,
                            'fontFamily' => 'var(--font-family),ui-sans-serif,system-ui,sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji"',
                        ],
                    ],
                ],
                'colors' => [$this->color],
            ],
        ];

        return ApexAnswerChart::make(['answerData' => $answerData]);
    }

    private function getOptionsAnswerCounts(Question $question, Collection $options, string $answerType): Collection
    {
        $cacheKey = 'poll-'.$question->poll_id.$question->id.'-options-answer-counts';
        $cacheTime = $question->poll->resultsArePublic() ? now()->addDay() : now()->addMinutes(5);

        if (\Cache::has($cacheKey)) {
            return \Cache::get($cacheKey);
        }

        $optionsAnswerCounts = [];
        $options->each(function ($option) use ($question, &$optionsAnswerCounts, $answerType) {
            $optionAnswerCount = $question->answers()->whereHasMorph('answerable', $answerType, function ($query) use ($option) {
                $query->where('answer_value', $option);
            })->count();
            $optionsAnswerCounts[$option] = $optionAnswerCount;
        });

        \Cache::add($cacheKey, collect($optionsAnswerCounts), $cacheTime);

        return collect($optionsAnswerCounts);
    }
}
