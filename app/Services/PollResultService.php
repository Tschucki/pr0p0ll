<?php

namespace App\Services;

use App\Filament\Resources\MyPollResource\Widgets\BooleanAnswerChart;
use App\Filament\Resources\MyPollResource\Widgets\SingleOptionAnswerChart;
use App\Models\AnswerTypes\BoolAnswer;
use App\Models\AnswerTypes\SingleOptionAnswer;
use App\Models\Polls\MyPoll;
use App\Models\Question;
use Filament\Widgets\WidgetConfiguration;

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
        $this->poll->questions->each(function (Question $question) use ($widgets) {
            $widgets[] = $this->createResultWidget($question);
        });
        dd($widgets->toArray());

        return $widgets->toArray();
    }

    private function getBooleanChartWidget(Question $question): WidgetConfiguration
    {
        // Get sum of all true answers and false answers via answerable morph to relation
        $trueAnswersCount = $question->answers()->whereHasMorph('answerable', BoolAnswer::class, function ($query) {
            $query->where('answer_value', true);
        })->count();
        $falseAnswerCounts = $question->answers()->whereHasMorph('answerable', BoolAnswer::class, function ($query) {
            $query->where('answer_value', false);
        })->count();

        $answerData = [
            'heading' => $question->title,
            'chartData' => [
                'datasets' => [
                    [
                        'label' => 'Antworten',
                        'data' => [$trueAnswersCount, $falseAnswerCounts],
                    ],
                ],
                'labels' => ['Ja', 'Nein'],
            ],
        ];

        return BooleanAnswerChart::make(['answerData' => $answerData]);
    }

    private function getSingleOptionChartWidget(Question $question): WidgetConfiguration
    {
        $options = collect($question->options)->map(function ($option) {
            return $option['title'];
        });
        $singleOptionAnswerCounts = [];
        $options->each(function ($option) use ($question, &$singleOptionAnswerCounts) {
            $optionAnswerCount = $question->answers()->whereHasMorph('answerable', SingleOptionAnswer::class, function ($query) use ($option, &$singleOptionAnswerCounts) {
                $query->where('answer_value', $option);
            })->count();
            $singleOptionAnswerCounts[$option] = $optionAnswerCount;
        });

        $answerData = [
            'heading' => $question->title,
            'chartData' => [
                'datasets' => [
                    [
                        'label' => 'Antworten',
                        'data' => $singleOptionAnswerCounts,
                    ],
                ],
                'labels' => $options->toArray(),
            ],
        ];

        return SingleOptionAnswerChart::make(['answerData' => $answerData]);
    }
}
