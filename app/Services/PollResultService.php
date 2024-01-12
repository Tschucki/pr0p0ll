<?php

namespace App\Services;

use App\Models\Polls\MyPoll;
use App\Models\Question;
use Filament\View\LegacyComponents\Widget;

class PollResultService
{
    private MyPoll $poll;

    public function __construct(MyPoll $poll)
    {
        $this->poll = $poll;
    }

    public function getAllWidgets(): array
    {
        $widgets = $this->poll->questions->map(function (Question $question) {
            return $this->createResultWidget($question);
        });

        return $widgets->toArray();
    }

    private function createResultWidget(Question $question)
    {
        $label = $question->title;
        $type = $question->questionType->getKey();
        $options = $question->options;
        $data = $question->data;
        $result = $question->result;

        $widget = new Widget();
    }
}
