<?php

namespace App\Services;

use App\Filament\Resources\MyPollResource\Widgets\AnswerChart;
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

    public function __construct(MyPoll $poll)
    {
        $this->poll = $poll;
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
        $trueAnswersCount = $question->answers()->whereHasMorph('answerable', BoolAnswer::class, function ($query) {
            $query->where('answer_value', true);
        })->count();
        $falseAnswerCounts = $question->answers()->whereHasMorph('answerable', BoolAnswer::class, function ($query) {
            $query->where('answer_value', false);
        })->count();

        $answerData = [
            'type' => 'pie',
            'heading' => $question->title,
            'chartData' => [
                'datasets' => [
                    [
                        'label' => 'Antworten',
                        'data' => [$trueAnswersCount, $falseAnswerCounts],
                        'backgroundColor' => '#ee4d2e',
                        'borderColor' => '#fff',
                    ],
                ],
                'description' => 'Test',
                'labels' => ['Ja', 'Nein'],
                'options' => $this->getChartOptions(),
            ],
        ];

        return AnswerChart::make(['answerData' => $answerData]);
    }

    private function getBarChartWidget(Question $question): WidgetConfiguration
    {
        $options = collect($question->options)->map(function ($option) {
            return $option['title'];
        });

        $answerData = [
            'type' => 'bar',
            'heading' => $question->title,
            'chartData' => [
                'datasets' => [
                    [
                        'label' => 'Antworten',
                        'data' => $this->getOptionsAnswerCounts($question, $options, get_class($question->answerType())),
                        'backgroundColor' => '#ee4d2e',
                        'borderColor' => '#ee4d2e',
                    ],
                ],
            ],
            'labels' => $options->toArray(),
            'options' => $this->getChartOptions(),
        ];

        return AnswerChart::make(['answerData' => $answerData]);
    }

    private function getOptionsAnswerCounts(Question $question, Collection $options, string $answerType): array
    {
        $optionsAnswerCounts = [];
        $options->each(function ($option) use ($question, &$optionsAnswerCounts, $answerType) {
            $optionAnswerCount = $question->answers()->whereHasMorph('answerable', $answerType, function ($query) use ($option, &$optionsAnswerCounts) {
                $query->where('answer_value', $option);
            })->count();
            $optionsAnswerCounts[$option] = $optionAnswerCount;
        });

        return $optionsAnswerCounts;
    }

    private function getChartOptions(): array
    {
        return [
            'plugins' => [
                'labels' => [
                    'precision' => 2,
                ],
            ],
            'legend' => [
                'labels' => [
                    'color' => '#fff',

                ],
                'title' => 'Test',
            ],
            'scales' => [
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
                'y' => [
                    'grid' => [
                        'display' => true,
                    ],
                ],
            ],
            'maintainAspectRatio' => true,
            'responsive' => true,
        ];
    }
}
