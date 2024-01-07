<?php

namespace App\Filament\Resources\PollResource\Pages;

use App\Filament\Resources\PollResource;
use App\Models\Poll;
use App\Models\Question;
use App\Services\PollFormService;
use Filament\Actions;
use Filament\Forms\Components\Placeholder;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\HtmlString;

class EditPoll extends EditRecord
{
    protected static string $resource = PollResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $questions = $data['questions'];
        $record = $this->getRecord();

        // Delete removed questions (if any)
        $questionIds = collect($questions)->filter(fn(array $question) => isset($question['id']))->map(fn(array $question) => $question['id']);
        Question::where('poll_id', $record->getKey())->whereNotIn('id', $questionIds)->delete();

        // Update existing questions
        collect($questions)->filter(fn(array $question) => isset($question['id']))->each(function (array $question) {
            Question::where('id', $question['id'])->update([
                'title' => $question['data']['title'],
                'hint' => $question['data']['hint'],
                'question_type_id' => $question['data']['question_type_id'],
                'options' => collect($question['data']['options'])->filter(function ($option) {
                    return $option['title'] !== null;
                })->map(function ($option) {
                    return [
                        'title' => $option['title'],
                        'helperText' => $option['helperText'],
                    ];
                })->toArray()
            ]);
        });
        // Create new questions
        collect($questions)->filter(fn(array $question) => !isset($question['id']))->each(function (array $question) use ($record) {
            Question::create([
                'poll_id' => $record->getKey(),
                'title' => $question['data']['title'],
                'hint' => $question['data']['hint'],
                'question_type_id' => $question['data']['question_type_id'],
                'options' => collect($question['data']['options'])->filter(function ($option) {
                    return $option['title'] !== null;
                })->map(function ($option) {
                    return [
                        'title' => $option['title'],
                        'helperText' => $option['helperText'],
                    ];
                })->toArray()
            ]);
        });

        unset($data['questions']);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Vorschau anzeigen')->form(fn(Poll $poll) => [
                Placeholder::make('Titel')->content($poll->title),
                Placeholder::make('Beschreibung')->content(new HtmlString('<div class="prose dark:prose-invert">' . \Str::markdown($poll->description) . '</div>')),
                \Filament\Forms\Components\Section::make('Fragen')->schema(function() use($poll) {
                    return (new PollFormService($poll))->buildForm();
                }),
            ])->modalHeading('Vorschau')->modalSubmitAction(false),
            Actions\DeleteAction::make(),
        ];
    }
}
