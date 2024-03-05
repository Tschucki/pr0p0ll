<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\QuestionType;
use App\Models\Abstracts\Poll;
use App\Models\Question;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

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
                'type' => (string) ($type->getKey()),
                'data' => [
                    'question_type_id' => $type->getKey(),
                    'title' => $question->title,
                    'description' => $question->description,
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
        $component = $component->helperText($question->description)->label($question->title);
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

        $questionKey = $question->getKey();

        return match ($component) {
            QuestionType::SINGLE->value => Radio::make((string) $questionKey),
            QuestionType::MULTIPLE->value => CheckboxList::make((string) $questionKey),
            QuestionType::TEXT->value => Textarea::make((string) $questionKey)->hint('NUR anonym gegenüber anderen Benutzern - Max. 255 Zeichen')->maxLength(255),
            QuestionType::TOGGLE->value => Radio::make((string) $questionKey)->options([true => 'Ja', false => 'Nein']),
            QuestionType::DATE->value => DatePicker::make((string) $questionKey),
            QuestionType::TIME->value => DateTimePicker::make((string) $questionKey)->seconds(false)->date(false)->time()->displayFormat('HH:mm'),
            QuestionType::DATETIME->value => DateTimePicker::make((string) $questionKey)->seconds(false)->displayFormat('DD.MM.YYYY HH:mm'),
            QuestionType::COLOR->value => ColorPicker::make((string) $questionKey),
            QuestionType::NUMBER->value => TextInput::make((string) $questionKey)->numeric(),
            default => throw new \InvalidArgumentException('Unknown question type'),
        };
    }

    private function getOptions(Question $question): array
    {

        return collect($question->options)->mapWithKeys(function ($option) {
            return [
                $option['title'] => $option['title'],
            ];
        })->toArray();
    }

    private function getOptionsDescriptions(Question $question): array
    {
        return collect($question->options)->mapWithKeys(function ($option) {
            if (isset($option['helperText'])) {
                return [
                    $option['title'] => $option['helperText'],
                ];
            }

            return [];
        })->toArray();
    }
}
