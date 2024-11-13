<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Resources\MyPollResource;
use App\Filament\Resources\PublicPollsResource;
use App\Models\AnswerTypes\MultipleChoiceAnswer;
use App\Models\AnswerTypes\SingleOptionAnswer;
use App\Models\AnswerTypes\TextAnswer;
use App\Models\Question;
use App\Services\PollResultService;
use Filament\Actions\Action;
use Filament\Actions\StaticAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\HtmlString;

class Pr0PostCreator extends Page
{
    use InteractsWithRecord;

    protected static string $resource = PublicPollsResource::class;

    protected static ?string $title = 'Pr0-Post erstellen';

    protected static string $view = 'filament.pages.pr0-post-creator';

    public ?array $data = [];

    public int $participants = 0;

    public function getTitle(): string
    {
        return static::$title;
    }

    public function mount(int|string $record)
    {
        $this->record = $this->resolveRecord($record);

        if (! $this->record->hasEnded()) {
            Notification::make('poll_not_ended')->danger()->title('Umfrage noch nicht beendet')->body('Die Umfrage ist noch nicht beendet.')->send();

            return redirect(MyPollResource::getUrl('view', ['record' => $this->record]));
        }
        $this->participants = $this->record->participants()->count();
        $this->authorizeAccess();
        $this->fillForm();
    }

    protected function authorizeAccess(): void
    {
        static::authorizeResourceAccess();

        abort_unless(static::getResource()::canViewResults($this->getRecord()), 403);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('note_action_bars')
                ->label('Kleiner Hinweis')
                ->modalWidth('xl')
                ->modalSubmitAction(false)
                ->modalCancelAction(fn (StaticAction $action) => $action->label('Ja okay'))
                ->modalDescription('Sollten einige Antworten nicht ordentlich lesbar sein, dann versuche es mit einem Balkendiagramm.')
                ->modalHeading('Test'),
            Action::make('download')->label('Herunterladen')->extraAttributes([
                'onclick' => new HtmlString('downloadImage()'),
            ])->action(fn () => Notification::make('converting_started')->warning()->title('Post wird erstellt')->body('Dein Post wird erstellt. Dies kann einige Sekunden dauern.')->send()),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->live(true)
            ->schema([
                $this->getPr0PostCreator(),
            ])
            ->model($this->record)
            ->statePath('data');
    }

    protected function getPr0PostCreator(): Component
    {
        return Section::make()
            ->schema([
                Grid::make(1)
                    ->schema([
                        Select::make('color')->options([
                            '#ee4d2e' => 'Bewährtes Orange',
                            '#1db992' => 'angenehmes Grün',
                            '#bfbc06' => 'Olivgrün des Friedens',
                            '#008fff' => 'mega episches Blau | Moderator',
                            '#ff0082' => 'altes Pink',
                            '#444444' => 'Gebannt',
                            '#6c432b' => 'Fliesentisch',
                            '#e108e9' => 'Neuschwuchtel',
                            '#ffffff' => 'Schwuchtel',
                            '#5bb91c' => 'Altschwuchtel',
                            '#addc8d' => 'Mittelaltschwuchtel',
                            '#7fc7ff' => 'Alt-Moderator',
                            '#ff9900' => 'Administrator',
                            '#ffc166' => 'System-Bot',
                            '#10366f' => 'Nutzer-Bot',
                            '#1cb992' => 'Lebende Legende | Edler Spender',
                            '#c52b2f' => 'Community-Helfer | Wichtel',
                            '#ea9fa1' => 'Alt-Helfer',
                        ])->default('#ee4d2e')->prefixIcon('heroicon-o-swatch')->label('Farbe'),
                        TextInput::make('title')->label('Titel')->required(),
                        Textarea::make('description')->label('Beschreibung')->nullable(),
                        ...collect($this->record->questions)->map(function (Question $question) {
                            $formFields = [
                                Toggle::make('display_'.$question->getKey())->label('Anzeigen')->inline(),
                                Textarea::make('description_'.$question->getKey())->label('Beschreibung')->nullable(),
                            ];
                            if ($question->answerType() instanceof TextAnswer) {
                                $question->answers()->each(function ($answer) use (&$formFields) {
                                    $formFields[] = Checkbox::make('display_answer_'.$answer->getKey())->label($answer->answerable->answer_value)->inline();
                                });
                            }

                            if ($question->answerType() instanceof SingleOptionAnswer || $question->answerType() instanceof MultipleChoiceAnswer) {
                                $formFields[] = Toggle::make('horizontal_'.$question->getKey())->label('Als Balkendiagramm')->inline();
                            }

                            return Section::make($question->title)->schema($formFields);
                        })->toArray(),
                    ])->columnSpan(1),
                Grid::make()
                    ->schema([
                        ViewField::make('preview.default')
                            ->columnSpan(2)
                            ->hiddenLabel()
                            ->view('filament.pr0post.creator.layouts.default'),
                    ])->columnSpan(2),
            ])->columns(3);
    }

    public function getResults(): array
    {
        return (new PollResultService($this->record, $this->data['color'], horizontalQuestions: $this->getHorizontalQuestions()))->getAllWidgets();
    }

    private function getHorizontalQuestions()
    {
        $form = $this->data;

        return collect($this->record->questions)->filter(function (Question $question) use ($form) {
            return $form['horizontal_'.$question->getKey()];
        })->mapWithKeys(function (Question $question) {
            return [$question->getKey() => true];
        })->toArray();
    }

    public function getQuestionAnswerCount(string $questionId): int|string
    {
        $question = Question::find($questionId);
        if ($question) {
            return $question->answers()->count();
        }

        return '¯\_(ツ)_/¯';
    }

    public function fillForm(): void
    {
        $this->data = [
            'color' => '#ee4d2e',
            'title' => $this->record->title,
            ...collect($this->record->questions)->mapWithKeys(fn (Question $question) => ['question_title_'.$question->getKey() => $question->title])->toArray(),
            ...collect($this->record->questions)->mapWithKeys(fn (Question $question) => ['display_'.$question->getKey() => true, 'horizontal_'.$question->getKey() => false])->toArray(),
            ...collect($this->record->questions)->mapWithKeys(fn (Question $question) => ['description_'.$question->getKey() => $question->description])->toArray(),
            ...collect($this->record->questions)->filter(fn (Question $question) => $question->answerType() instanceof TextAnswer)->mapWithKeys(fn (Question $question) => $question->answers->mapWithKeys(fn ($answer) => ['display_answer_'.$answer->getKey() => true])->toArray())->toArray(),
        ];
        $this->form->fill($this->data);
    }
}
