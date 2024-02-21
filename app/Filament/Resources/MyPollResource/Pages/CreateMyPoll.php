<?php

declare(strict_types=1);

namespace App\Filament\Resources\MyPollResource\Pages;

use App\Filament\Resources\MyPollResource;
use App\Models\Polls\MyPoll;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateMyPoll extends CreateRecord
{
    protected static string $resource = MyPollResource::class;

    protected static ?string $title = 'Eigene Umfrage erstellen';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $poll = [];
        $questions = $data['questions'];
        $questions = collect($questions)->map(function (array $question) {
            return [
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
            ];
        });

        try {
            $validatedQuestions = \Illuminate\Support\Facades\Validator::make($questions->toArray(), [
                '*.title' => 'required|string',
                '*.description' => 'nullable|string',
                '*.question_type_id' => 'required|exists:question_types,id',
                '*.options' => 'array|present',
                '*.options.*.title' => 'required',
            ])->validated();

            unset($data['questions']);

            /**
             * @var MyPoll $poll
             * */
            $poll = static::getModel()::create($data);

            $poll->questions()->createMany($validatedQuestions);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Notification::make()
                ->title('Komisch. Beim Validieren deiner Fragen ist ein Fehler aufgetreten.')
                ->danger()
                ->send();
        }

        return $poll;
    }
}
