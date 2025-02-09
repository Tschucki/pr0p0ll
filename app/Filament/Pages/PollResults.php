<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\Gender;
use App\Enums\Nationality;
use App\Enums\Region;
use App\Filament\Exports\AnswerExporter;
use App\Filament\Resources\PublicPollsResource;
use App\Models\Polls\PublicPoll;
use App\Services\PollResultService;
use Auth;
use Filament\Actions\Action;
use Filament\Actions\ExportAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Widgets\Widget;
use Filament\Widgets\WidgetConfiguration;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

class PollResults extends Page
{
    use InteractsWithForms;
    use InteractsWithRecord;

    #[Url(as: 'gender')]
    public ?string $gender = '';

    #[Url(as: 'region')]
    public ?array $region = [];

    #[Url(as: 'nationality')]
    public ?array $nationality = [];

    #[Url(as: 'min_age')]
    public ?string $min_age = null;

    #[Url(as: 'max_age')]
    public ?string $max_age = null;

    protected static ?string $slug = 'ergebnisse';

    protected $listeners = ['updatedFilter' => '$refresh'];

    protected static string $resource = PublicPollsResource::class;

    protected static string $view = 'filament.pages.my-poll-results';

    protected static ?string $title = 'Ergebnisse';

    public ?array $data = [];

    protected array $widgets = [];

    public function mount(int|string $record): void
    {

        $this->record = $this->resolveRecord($record);
        static::authorizeResourceAccess();
        abort_unless(static::getResource()::canViewResults($this->getRecord()), 403);
        $this->fillForm();
    }

    public function fillForm(): void
    {
        $data = $this->data;
        $data['gender'] = $this->gender;
        $data['region'] = $this->region;
        $data['nationality'] = $this->nationality;
        $data['min_age'] = $this->min_age;
        $data['max_age'] = $this->max_age;

        $this->form->fill($data);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_post')->label('Pr0-Post erstellen')->button()->url(route('filament.pr0p0ll.resources.umfragen.pr0post', [
                'record' => $this->getRecord(),
            ]))->visible(fn (PublicPoll $poll) => $poll->hasEnded()),
            ExportAction::make('export')
                ->label('Exportieren')
                ->button()
                ->exporter(AnswerExporter::class)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('poll_id', $this->getRecord()->id))
                ->visible(fn (PublicPoll $poll) => $poll->hasEnded() || Auth::user()?->isAdmin() || $poll->user_id === Auth::user()->id),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->live(true)
            ->schema([
                Select::make('gender')->afterStateUpdated(function (Get $get) {
                    $this->gender = $get('gender');
                    $this->update();
                }
                )->label('Geschlecht')->options(Gender::class)->columnSpan(1),
                TextInput::make('min_age')->afterStateUpdated(function (Get $get) {
                    $this->min_age = $get('min_age');
                    $this->update();
                })->label('Mindestalter')->type('number')->columnSpan(1)->default(0)->minValue(0)->maxValue(99)->default(0),
                TextInput::make('max_age')->afterStateUpdated(function (Get $get) {
                    $this->max_age = $get('max_age');
                    $this->update();
                })->label('Maximalalter')->type('number')->columnSpan(1)->default(0)->minValue(0)->maxValue(99)->default(0),
                Select::make('nationality')->afterStateUpdated(function (Get $get) {
                    $this->nationality = $get('nationality');
                    $this->update();
                })->columnSpan(1)->multiple()->label('Nationalität')->options(Nationality::class)->native(false),
                Select::make('region')->afterStateUpdated(function (Get $get) {
                    $this->region = $get('region');
                    $this->update();
                })->columnSpan(1)->multiple()->label('Region')->options(Region::class)->native(false),
            ])->columns(2)
            ->statePath('data');
    }

    public function update(): void
    {
        $this->redirectRoute('filament.pr0p0ll.resources.umfragen.results', [
            'record' => $this->getRecord(),
            'gender' => $this->data['gender'] ?? null,
            'nationality' => $this->data['nationality'] ?? null,
            'min_age' => $this->data['min_age'] ?? null,
            'max_age' => $this->data['max_age'] ?? null,
            'region' => $this->data['region'] ?? null,
        ]);
    }

    public function getWidgetData(): array
    {
        return [
            'poll' => $this->record,
        ];
    }

    public function getColumns(): int
    {
        return 1;
    }

    /**
     * @return array<class-string<Widget> | WidgetConfiguration>
     */
    public function getWidgets(): array
    {
        return (new PollResultService($this->record, filters: $this->data))->getAllWidgets();
    }

    /**
     * @return array<class-string<Widget> | WidgetConfiguration>
     */
    public function getVisibleWidgets(): array
    {
        return $this->filterVisibleWidgets($this->getWidgets());
    }
}
