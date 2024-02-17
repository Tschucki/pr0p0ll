<?php

namespace App\Filament\Resources\MyPollResource\Pages;

use App\Filament\Resources\MyPollResource;
use App\Models\Question;
use App\Services\PollResultService;
use Filament\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Form;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class Pr0PostCreator extends Page
{
    use InteractsWithRecord;

    protected static string $resource = MyPollResource::class;

    protected static ?string $title = 'Pr0-Post erstellen';

    protected static string $view = 'filament.resources.my-poll-resource.pages.pr0-post-creator';

    public ?array $data = [];

    public function getTitle(): string
    {
        return static::$title;
    }

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->authorizeAccess();
        $this->fillForm();
    }

    protected function authorizeAccess(): void
    {
        static::authorizeResourceAccess();

        abort_unless(static::getResource()::canView($this->getRecord()), 403);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download')->label('Herunterladen'),
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
                        TextInput::make('title')->label('Titel')->required(),
                        Textarea::make('description')->label('Beschreibung')->nullable(),
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
        return (new PollResultService($this->record))->getAllWidgets();
    }

    public function getQuestionAnswerCount(string $questionId)
    {
        $question = Question::find($questionId);
        if ($question) {
            return $question->answers()->count();
        }

        return 'Unbekannt';
    }

    public function fillForm(): void
    {
        $this->data = [
            'title' => $this->record->title,
        ];
        $this->form->fill($this->data);
    }
}
