<?php

declare(strict_types=1);

namespace App\Services;

use App\Filament\Resources\MyPollResource\Widgets\ApexAnswerChart;
use App\Filament\Resources\MyPollResource\Widgets\TextAnswersWidget;
use App\Models\Answer;
use App\Models\AnswerTypes\BoolAnswer;
use App\Models\AnswerTypes\MultipleChoiceAnswer;
use App\Models\AnswerTypes\SingleOptionAnswer;
use App\Models\AnswerTypes\TextAnswer;
use App\Models\Polls\PublicPoll;
use App\Models\Question;
use Filament\Widgets\WidgetConfiguration;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PollResultService
{
    private PublicPoll $poll;

    private string $color;

    private array $horizontalQuestions;

    private array $filters;

    public function __construct(PublicPoll $poll, string $color = '#ee4d2e', $filters = [], $horizontalQuestions = [])
    {
        $this->poll = $poll;
        $this->color = $color;
        $this->filters = $filters;
        $this->horizontalQuestions = $horizontalQuestions;
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
            $answerType instanceof TextAnswer => $this->getTextWidget($question),

            default => null,
        };
    }

    private function getTextWidget(Question $question): WidgetConfiguration
    {
        $answers = $question->answers()->filter($this->filters)->whereHasMorph('answerable', TextAnswer::class, function (Builder $query) {
            $query->whereNotNull('answer_value')->where('answer_value', '!=', '');
        })->get();

        // get all answer values with their id
        $answerValues = $answers->mapWithKeys(function (Answer $answer) {
            return [$answer->getKey() => $answer->answerable->answer_value];
        })->toArray();

        $answersCount = $answers->count();
        $footerText = 'Es wurden '.$answersCount.' Antworten abgegeben.';

        $answerData = [
            'heading' => $question->title,
            'chartId' => 'chart-'.$question->id,
            'questionId' => $question->getKey(),
            'poll' => $question->poll,
            'answers' => $answerValues,
            'footerText' => $footerText,
        ];

        return TextAnswersWidget::make([
            'answerData' => $answerData,
            'question' => $question,
        ]);
    }

    private function getBooleanChartWidget(Question $question): WidgetConfiguration
    {
        $trueAnswersCount = $question->answers()->filter($this->filters)->whereHasMorph('answerable', BoolAnswer::class, function ($query) {
            $query->where('answer_value', true);
        })->count();
        $falseAnswerCounts = $question->answers()->filter($this->filters)->whereHasMorph('answerable', BoolAnswer::class, function ($query) {
            $query->where('answer_value', false);
        })->count();

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
                'plotOptions' => [
                    'bar' => [
                        'horizontal' => $this->horizontalQuestions[$question->getKey()] ?? false,
                    ],
                ],
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
                    'categories' => $this->createOptionsLabels($options),
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

    private function createOptionsLabels(Collection $options)
    {
        // options consist of multiple strings
        // wrap each string in a separate array
        // inside of each array i want always wrap 2 strings in another array within the array
        // remove the keys and return the array
        return $options->map(function ($option) {
            return explode(' ', $option);
        })->map(function ($option) {
            return array_values($option);
        })->values()->toArray();
    }

    private function getOptionsAnswerCounts(Question $question, Collection $options, string $answerType): Collection
    {
        $optionsAnswerCounts = [];
        $options->each(function ($option) use ($question, &$optionsAnswerCounts, $answerType) {
            $optionAnswerCount = $question->answers()->filter($this->filters)->whereHasMorph('answerable', $answerType, function ($query) use ($option) {
                $query->where('answer_value', $option);
            })->count();
            $optionsAnswerCounts[$option] = $optionAnswerCount;
        });

        return collect($optionsAnswerCounts);
    }
}
