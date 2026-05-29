<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Resources\MyPollResource;
use App\Filament\Resources\PublicPollsResource;
use App\Jobs\GenerateResultPostScreenshot;
use App\Jobs\PostPollResultToPr0gramm;
use App\Models\AnswerTypes\BoolAnswer;
use App\Models\AnswerTypes\MultipleChoiceAnswer;
use App\Models\AnswerTypes\SingleOptionAnswer;
use App\Models\AnswerTypes\TextAnswer;
use App\Models\Question;
use App\Services\PollResultService;
use App\Support\ResultPostConfig;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class Pr0PostCreator extends Page
{
    use InteractsWithRecord;

    protected static ?string $title = 'Pr0-Post erstellen';

    protected string $view = 'filament.pages.pr0-post-creator';

    public ?array $data = [];

    public int $participants = 0;

    protected static string $resource = PublicPollsResource::class;

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
            Action::make('save')
                ->label('Konfiguration speichern')
                ->icon('heroicon-o-check')
                ->action(function (): void {
                    $config = ResultPostConfig::fromFlatForm($this->data, $this->record);
                    $this->record->update(['result_post_config' => $config->toArray()]);
                    Notification::make('config_saved')->success()->title('Gespeichert')->body('Deine Auswertungs-Konfiguration wurde gespeichert.')->send();
                }),
            Action::make('download')
                ->label('Bild generieren')
                ->icon('heroicon-o-photo')
                ->action(function (): void {
                    $config = ResultPostConfig::fromFlatForm($this->data, $this->record);
                    GenerateResultPostScreenshot::dispatch($this->record, Auth::user(), $config->toArray());

                    Notification::make('screenshot_queued')
                        ->info()
                        ->title('Bild wird erstellt')
                        ->body('Dein Auswertungs-Bild wird im Hintergrund erzeugt. Du erhältst eine Benachrichtigung mit dem Download-Link, sobald es fertig ist.')
                        ->send();
                }),
            Action::make('postToPr0gramm')
                ->label('Jetzt auf pr0gramm posten')
                ->icon('heroicon-o-paper-airplane')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Auswertung auf pr0gramm posten?')
                ->modalDescription('Die Auswertung wird als öffentlicher Beitrag auf pr0gramm veröffentlicht. Das kann nicht rückgängig gemacht werden.')
                ->visible(fn (): bool => Auth::user()?->isAdmin() && $this->record->isEligibleForResultPost())
                ->action(fn () => $this->postToPr0gramm()),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
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
            ->columns(3)
            ->schema([
                Grid::make(1)
                    ->columnSpan(1)
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
                        Toggle::make('show_demographics')->label('Teilnehmer-Informationen anzeigen')->default(true),
                        TextInput::make('tags')
                            ->label('Tags (kommagetrennt)')
                            ->placeholder(ResultPostConfig::defaultTags($this->record))
                            ->helperText('Leer lassen für automatische Tags.'),
                        Textarea::make('comment')
                            ->label('Kommentar')
                            ->placeholder(ResultPostConfig::defaultComment($this->record))
                            ->helperText('Leer lassen für einen automatischen Kommentar mit Link zur Auswertung.')
                            ->nullable(),
                        ...$this->getQuestionConfigFields(),
                    ]),
                Grid::make()
                    ->columnSpan(2)
                    ->schema([
                        ViewField::make('preview')
                            ->columnSpanFull()
                            ->hiddenLabel()
                            ->view('filament.pr0post.creator.layouts.default'),
                    ]),
            ]);
    }

    protected function getQuestionConfigFields(): array
    {
        return collect($this->record->questions)->map(function (Question $question) {
            $key = $question->getKey();
            $type = $question->answerType();

            $aFields = [
                Toggle::make('display_'.$key)->label('Anzeigen')->default(true),
                TextInput::make('title_'.$key)->label('Titel'),
                Textarea::make('description_'.$key)->label('Beschreibung')->nullable(),
            ];

            if ($type instanceof SingleOptionAnswer || $type instanceof MultipleChoiceAnswer || $type instanceof BoolAnswer) {
                $aFields[] = Select::make('chart_'.$key)->label('Diagramm')->options([
                    ResultPostConfig::CHART_BAR => 'Balken',
                    ResultPostConfig::CHART_DONUT => 'Donut',
                ]);
            }

            if ($type instanceof TextAnswer) {
                foreach ($question->answers as $answer) {
                    if (filled($answer->answerable?->answer_value)) {
                        $aFields[] = Checkbox::make('answer_'.$answer->getKey())->label($answer->answerable->answer_value);
                    }
                }
            }

            return Section::make($question->title)->schema($aFields)->collapsible()->collapsed();
        })->toArray();
    }

    public function postToPr0gramm(): void
    {
        $config = ResultPostConfig::fromFlatForm($this->data, $this->record);
        $this->record->update(['result_post_config' => $config->toArray()]);

        PostPollResultToPr0gramm::dispatch($this->record, $config->toArray(), Auth::id());

        Notification::make('post_queued')
            ->success()
            ->title('Wird auf pr0gramm gepostet')
            ->body('Die Auswertung wird im Hintergrund veröffentlicht. Der Post-Link wird danach automatisch bei der Umfrage hinterlegt.')
            ->send();
    }

    // Render-Model für die Live-Vorschau aus dem aktuellen Form-State.
    public function getEvaluation(): array
    {
        $config = ResultPostConfig::fromFlatForm($this->data, $this->record);

        return (new PollResultService($this->record))->buildEvaluation($config);
    }

    public function fillForm(): void
    {
        $config = ResultPostConfig::fromArray($this->record->result_post_config, $this->record);
        $this->data = $config->toFlatForm();
        $this->form->fill($this->data);
    }
}
