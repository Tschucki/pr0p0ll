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
use App\Support\ResultPostConfig;
use Auth;
use Filament\Actions\Action;
use Filament\Actions\ExportAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
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

    protected string $view = 'filament.pages.my-poll-results';

    protected static ?string $title = 'Ergebnisse';

    public ?array $data = [];

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
            ]))->visible(fn (PublicPoll $record) => $record->hasEnded()),
            ExportAction::make('export')
                ->label('Exportieren')
                ->button()
                ->exporter(AnswerExporter::class)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('poll_id', $this->getRecord()->id))
                ->visible(fn (PublicPoll $record) => $record->hasEnded() || Auth::user()?->isAdmin() || $record->user_id === Auth::user()->id),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->live(true)
            ->schema([
                Select::make('gender')->afterStateUpdated(function (Get $get) {
                    $this->gender = $this->toScalar($get('gender'));
                    $this->update();
                }
                )->label('Geschlecht')->options(Gender::class)->columnSpan(1),
                TextInput::make('min_age')->afterStateUpdated(function (Get $get) {
                    $this->min_age = $this->toScalar($get('min_age'));
                    $this->update();
                })->label('Mindestalter')->type('number')->columnSpan(1)->default(0)->minValue(0)->maxValue(99)->default(0),
                TextInput::make('max_age')->afterStateUpdated(function (Get $get) {
                    $this->max_age = $this->toScalar($get('max_age'));
                    $this->update();
                })->label('Maximalalter')->type('number')->columnSpan(1)->default(0)->minValue(0)->maxValue(99)->default(0),
                Select::make('nationality')->afterStateUpdated(function (Get $get) {
                    $this->nationality = $this->toScalarArray($get('nationality'));
                    $this->update();
                })->columnSpan(1)->multiple()->label('Nationalität')->options(Nationality::class)->native(false),
                Select::make('region')->afterStateUpdated(function (Get $get) {
                    $this->region = $this->toScalarArray($get('region'));
                    $this->update();
                })->columnSpan(1)->multiple()->label('Region')->options(Region::class)->native(false),
            ])->columns(2)
            ->statePath('data');
    }

    public function update(): void
    {
        $this->redirectRoute('filament.pr0p0ll.resources.umfragen.results', [
            'record' => $this->getRecord(),
            'gender' => $this->gender ?: null,
            'nationality' => $this->nationality ?: null,
            'min_age' => $this->min_age ?: null,
            'max_age' => $this->max_age ?: null,
            'region' => $this->region ?: null,
        ]);
    }

    // Render-Model mit Default-Config + aktiven Demografie-Filtern.
    public function getEvaluation(): array
    {
        $aFilters = [
            'gender' => $this->gender ?: null,
            'nationality' => $this->nationality ?: null,
            'region' => $this->region ?: null,
            'min_age' => $this->min_age ?: null,
            'max_age' => $this->max_age ?: null,
        ];

        return (new PollResultService($this->record, $aFilters))
            ->buildEvaluation(ResultPostConfig::default($this->record));
    }

    private function toScalar(mixed $value): ?string
    {
        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }

        return $value === null || $value === '' ? null : (string) $value;
    }

    private function toScalarArray(mixed $value): array
    {
        return collect($value)->map(fn ($item) => $this->toScalar($item))->filter()->values()->all();
    }
}
