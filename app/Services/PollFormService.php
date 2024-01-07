<?php

namespace App\Services;

use App\Enums\QuestionType;
use App\Models\Poll;
use App\Models\Question;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;

class PollFormService
{

    private Poll $poll;

    // TODO: Create Facade
    public function __construct(Poll $poll)
    {
        $this->poll = $poll;
    }

    public function getBuilderData(): array
    {
        return $this->poll->questions->map(function (Question $question) {
            $type = $question->questionType;
            return [
                'id' => $question->getKey(),
                'type' => (string)($type->getKey()),
                'data' => [
                    'question_type_id' => $type->getKey(),
                    'title' => $question->title,
                    'hint' => $question->hint,
                    'options' => $question->options,
                ],
            ];
        })->toArray();
    }


    public function buildForm(): array
    {
        $form = [];
        $this->poll->questions->each(function (Question $question) use (&$form) {
            $form[] = $this->createField($question);
        });
        return $form;
    }

    private function createField(Question $question): Field
    {
        $component = $this->getComponent($question->questionType, $question);
        $component = $component->helperText($question->hint)->label($question->title);
        if ($question->questionType->hasOptions()) {
            $component->options($this->getOptions($question))->descriptions($this->getOptionsDescriptions($question));
        }
        return $component;
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function getComponent(\App\Models\QuestionType $questionType, Question $question): Field
    {
        $component = $questionType->component;

        return match ($component) {
            QuestionType::SINGLE->value => Radio::make($question->getKey()),
            QuestionType::MULTIPLE->value => CheckboxList::make($question->getKey()),
            QuestionType::TEXT->value => Textarea::make($question->getKey())->hint('Nicht anonym - Max. 255 Zeichen')->maxLength(255),
            QuestionType::TOGGLE->value => Toggle::make($question->getKey())->inline(false),
            QuestionType::DATE->value => DatePicker::make($question->getKey()),
            QuestionType::TIME->value => DateTimePicker::make($question->getKey())->seconds(false)->date(false)->time()->displayFormat('HH:mm'),
            QuestionType::DATETIME->value => DateTimePicker::make($question->getKey())->seconds(false)->displayFormat('DD.MM.YYYY HH:mm'),
            QuestionType::COLOR->value => ColorPicker::make($question->getKey()),
            default => throw new \InvalidArgumentException('Unknown question type'),
        };
    }

    private function getOptions(Question $question): array
    {
        return collect($question->options)->map(function ($option) {
            return [
                $option['title'] => $option['title'],
            ];
        })->flatten()->toArray();
    }

    private function getOptionsDescriptions(Question $question): array
    {
        return collect($question->options)->map(function ($option) {
            return [
                $option['title'] => $option['helperText'],
            ];
        })->flatten()->toArray();
    }
}
