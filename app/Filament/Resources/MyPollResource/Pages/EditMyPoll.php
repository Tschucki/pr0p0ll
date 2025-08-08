<?php

declare(strict_types=1);

namespace App\Filament\Resources\MyPollResource\Pages;

use App\Filament\Actions\AddOriginalContentLinkAction;
use App\Filament\Actions\PollPreviewAction;
use App\Filament\Actions\SubmitForReviewAction;
use App\Filament\Resources\MyPollResource;
use App\Models\Question;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMyPoll extends EditRecord
{
    protected static string $resource = MyPollResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $questions = $data['questions'];
        $record = $this->getRecord();

        // Delete removed questions (if any)
        $questionIds = collect($questions)->filter(fn (array $question) => isset($question['id']))->map(fn (array $question) => $question['id']);
        Question::where('poll_id', $record->getKey())->whereNotIn('id', $questionIds)->delete();

        // Update existing questions
        collect($questions)->filter(fn (array $question) => isset($question['id']))->each(function (array $question) {
            Question::where('id', $question['id'])->update([
                'title' => $question['data']['title'],
                'description' => $question['data']['description'],
                'question_type_id' => $question['data']['question_type_id'],
                'options' => collect($question['data']['options'] ?? [])->filter(function ($option) {
                    return $option['title'] !== null;
                })->map(function ($option) {
                    return [
                        'title' => $option['title'],
                        'helperText' => $option['helperText'],
                    ];
                })->toArray(),
            ]);
        });
        // Create new questions
        collect($questions)->filter(fn (array $question) => ! isset($question['id']))->each(function (array $question) use ($record) {
            Question::create([
                'poll_id' => $record->getKey(),
                'title' => $question['data']['title'],
                'description' => $question['data']['description'],
                'question_type_id' => $question['data']['question_type_id'],
                'options' => collect($question['data']['options'])->filter(function ($option) {
                    return $option['title'] !== null;
                })->map(function ($option) {
                    return [
                        'title' => $option['title'],
                        'helperText' => $option['helperText'],
                    ];
                })->toArray(),
            ]);
        });

        unset($data['questions']);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            AddOriginalContentLinkAction::make(),
            PollPreviewAction::make(),
            SubmitForReviewAction::make(),
            Actions\ActionGroup::make([
                Actions\DeleteAction::make(),
            ]),
        ];
    }
}
