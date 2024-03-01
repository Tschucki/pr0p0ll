<?php

declare(strict_types=1);

namespace App\Filament\Resources\MyPollResource\Widgets;

use App\Models\Question;
use Filament\Widgets\Widget;

class TextAnswersWidget extends Widget
{
    protected static string $view = 'filament.widgets.text-answers-widget';

    public array $answerData;

    public Question $question;

    public ?string $color = '#ee4d2e';

    public function mount(): void
    {
        $this->question = Question::find($this->answerData['questionId']);
    }

    public function getQuestionTitle(): string
    {
        return $this->question->title ?? '';
    }

    public function getQuestionDescription(): ?string
    {
        return $this->question->description ?? null;
    }

    public function getAnswers(): array
    {
        return $this->answerData['answers'];
    }

    public function getFooterText(): ?string
    {
        return $this->answerData['footerText'] ?? null;
    }
}
