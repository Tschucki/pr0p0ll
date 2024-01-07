<?php

namespace App\Filament\Resources\PollResource\Pages;

use App\Filament\Resources\PollResource;
use App\Models\Poll;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CreatePoll extends CreateRecord
{
    protected static string $resource = PollResource::class;

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
            ];
        });

        try {
            $validatedQuestions = \Illuminate\Support\Facades\Validator::make($questions->toArray(), [
                '*.title' => 'required',
                '*.question_type_id' => 'required|exists:question_types,id',
                '*.options' => 'array|present',
                '*.options.*.title' => 'required',
            ])->validated();

            unset($data['questions']);

            /**
             * @var Poll $poll
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
