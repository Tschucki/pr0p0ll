<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\ClosesAfter;
use App\Enums\Gender;
use App\Enums\Nationality;
use App\Filament\Resources\MyPollResource\Pages\CreateMyPoll;
use App\Filament\Resources\MyPollResource\Pages\EditMyPoll;
use App\Filament\Resources\MyPollResource\Pages\ListMyPolls;
use App\Filament\Resources\MyPollResource\Pages\ViewMyPoll;
use App\Models\Category;
use App\Models\Polls\MyPoll;
use App\Models\QuestionType;
use App\Services\TargetGroupService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Str;
use Throwable;

class MyPollResource extends Resource
{
    protected static ?string $model = MyPoll::class;

    protected static ?string $label = 'Meine Umfrage';

    protected static string|\UnitEnum|null $navigationGroup = 'Umfragen';

    protected static ?string $pluralLabel = 'Meine Umfragen';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    public static function form(Schema $schema): Schema
    {

        return $schema
            ->components([
                Tabs::make()->tabs([
                    Tab::make('Allgemein')->schema([
                        Section::make('Umfrage-Details')
                            ->description('Titel, Kategorie und Laufzeit der Umfrage.')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Toggle::make('not_anonymous')
                                    ->label('Möchtest du die Umfrage so veröffentlichen, dass dein Name sichtbar ist?')
                                    ->inline(false)
                                    ->required()
                                    ->default(true)
                                    ->helperText('Es geht nur darum, ob dein Name bei der Umfrage angezeigt wird. Das pr0p0ll-Team sieht natürlich, dass du diese Umfrage erstellt hast. Das soll dafür sorgen, dass Teilnehmer nicht beeinflusst werden.'),
                                Grid::make(['sm' => 1, 'md' => 2])->schema([
                                    TextInput::make('title')->label('Titel')->maxLength(255)->required()->columnSpanFull(),
                                    Select::make('category_id')
                                        ->label('Kategorie')
                                        ->searchable()
                                        ->options(fn () => Category::where('enabled', true)->pluck('title', 'id'))
                                        ->nullable()
                                        ->native(false),
                                    Select::make('closes_after')
                                        ->label('Ende der Umfrage')
                                        ->options(ClosesAfter::class)
                                        ->default('+3 weeks')
                                        ->required()
                                        ->helperText('Zeitraum beginnt nachdem die Umfrage genehmigt wurde. Du kannst sie nicht frühzeitig beenden.'),
                                ]),
                                Textarea::make('description')->label('Beschreibung')->nullable()->rows(4)->columnSpanFull(),
                            ]),
                    ]),
                    Tab::make('Zielgruppe')->schema([
                        Fieldset::make('target_group_count')->label('Potentielle Teilnehmerzahl')
                            ->schema([Placeholder::make('participants_count')->label('')->content(function (Get $get) {
                                try {
                                    return TargetGroupService::calculateTargetGroupFromBuilder($get('target_group')).' Teilnehmer';
                                } catch (Throwable $throwable) {
                                    Log::info($throwable->getMessage());
                                    Notification::make('target_group_error')->title('Fehler')->body('Beim berechnen der Zielgruppe ist ein Fehler aufgetreten.')->danger()->actions([
                                        Action::make('target_group_error_action')->label('Melden')->button()->url('https://github.com/pr0p0ll/pr0p0ll/issues/new', true),
                                    ])->send();
                                }

                                return '';
                            })]),
                        Placeholder::make('target_group_info1')->label('')->content('Hier kannst du die Zielgruppe definieren, die an der Umfrage teilnehmen darf.')->columnSpanFull(),
                        Builder::make('target_group')->label('Zielgruppen Builder')->blocks([
                            Block::make('gender')->label('Geschlecht')->schema([
                                Select::make('gender')->label('')->options(Gender::class)->native(false),
                            ])->reactive()->maxItems(1)->reactive()->icon('icon-gender')->label('Geschlecht'),
                            Block::make('min_age')->label('Mindestalter')->schema([
                                TextInput::make('min_age')->label('')->type('number')->default(0)->minValue(0)->maxValue(99)->required()->default(0),
                            ])->maxItems(1)->reactive()->icon('icon-crib')->label('Mindestalter'),
                            Block::make('max_age')->label('Mindestalter')->schema([
                                TextInput::make('max_age')->label('')->type('number')->default(0)->minValue(0)->maxValue(99)->required()->default(0),
                            ])->maxItems(1)->reactive()->icon('icon-elderly-woman')->label('Höchstalter'),
                            Block::make('nationality')->label('Nationalität')->schema([
                                Select::make('nationality')->multiple()->label('')->options(Nationality::class)->native(false),
                            ])->maxItems(1)->reactive()->icon('heroicon-o-flag')->label('Nationalität'),
                        ])->blockNumbers(false)->reactive()->reorderable(false),
                    ]),
                    Tab::make('Fragen')->schema([
                        Builder::make('questions')->afterStateHydrated(function (Builder $component) use ($schema) {
                            /**
                             * @var MyPoll $poll
                             * */
                            $poll = $schema->getRecord();
                            if ($poll) {
                                $component->state($poll->getBuilderData());
                            }
                        })->label('')->required()
                            ->blocks(function () {
                                return QuestionType::active()->get()->map(function (QuestionType $questionType) {
                                    return Block::make((string) $questionType->getKey())->label($questionType->title)->schema([
                                        Hidden::make('question_type_id')->default($questionType->getKey()),
                                        Hidden::make('uuid')->default(Str::uuid()->toString()),
                                        TextInput::make('title')->extraAttributes(['class' => $questionType->getKey().'Input'])->label('Titel')->maxLength(255)->required()->live(true),
                                        Textarea::make('description')->label('Beschreibung')->nullable(),
                                        Repeater::make('options')->label('Auswahlmöglichkeiten')->schema([
                                            TextInput::make('title')->required()->label('Titel')->maxLength(255),
                                            TextInput::make('helperText')->nullable()->label('Hilfetext')->maxLength(255),
                                        ])->minItems(2)->reorderable(false)->required()->visible(fn () => $questionType->hasOptions()),
                                    ])->reactive()->icon($questionType->icon)->label(function (?array $state) use ($questionType): string {
                                        if ($state === null) {
                                            return $questionType->title;
                                        }

                                        return $state['title'] ? $state['title'].' - '.$questionType->title : $questionType->title;
                                    });
                                })->toArray();
                            })->reorderable(false)->collapsible()->collapsed(fn (MyPoll $record): MyPoll => $record)->reactive()->live(true)->blockNumbers(false),
                    ]),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('status')->icon('heroicon-o-cog')->label('Status')->state(function (MyPoll $record) {
                    if ($record->hasEnded()) {
                        return 'Beendet';
                    }
                    if ($record->isVisibleForPublic()) {
                        return 'Öffentlich sichtbar';
                    }
                    if ($record->isApproved()) {
                        return 'Genehmigt';
                    }
                    if ($record->isInReview()) {
                        return 'In Überprüfung';
                    }

                    return 'Entwurf';
                })->icon(function (MyPoll $record) {
                    if ($record->hasEnded()) {
                        return 'heroicon-o-lock-closed';
                    }
                    if ($record->isVisibleForPublic()) {
                        return 'heroicon-o-eye';
                    }
                    if ($record->isApproved()) {
                        return 'heroicon-o-check-circle';
                    }
                    if ($record->isInReview()) {
                        return 'heroicon-o-scale';
                    }

                    return 'heroicon-o-pencil-square';
                })->iconColor(function (MyPoll $record) {
                    if ($record->hasEnded()) {
                        return 'success';
                    }
                    if ($record->isVisibleForPublic()) {
                        return 'success';
                    }
                    if ($record->isApproved()) {
                        return 'success';
                    }

                    return 'warning';
                }),
                TextColumn::make('closes_at')->label('Ende')->dateTime('d.m.Y H:i')->sortable()->toggleable(),
                TextColumn::make('title')->label('Titel')->sortable()->searchable(),
                TextColumn::make('answers_count')->counts('answers')->label('Anzahl Antworten')->sortable()->toggleable(),
                TextColumn::make('participants_count')->counts('participants')->label('Anzahl Teilnehmer')->sortable()->toggleable(),
                TextColumn::make('updated_at')->dateTime('d.m.Y H:i')->suffix(' Uhr')->label('Änderungsdatum')->sortable()->toggleable()->toggledHiddenByDefault(),
                TextColumn::make('created_at')->dateTime('d.m.Y H:i')->suffix(' Uhr')->label('Erstelldatum')->sortable()->toggleable()->toggledHiddenByDefault(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('results')
                    ->visible(fn (MyPoll $record) => $record->visible_to_public === true)
                    ->button()
                    ->label('Ergebnisse ansehen')
                    ->url(fn (MyPoll $record) => route('filament.pr0p0ll.resources.umfragen.results', ['record' => $record])),
                ViewAction::make()->iconButton(),
                EditAction::make()->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->paginated([10, 25, 50])
            ->query(MyPoll::query()->where('user_id', auth()->id()));
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Nachricht vom pr0p0ll-Team')
                ->description('Hinweise des Teams zu deiner Umfrage')
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->collapsible()
                ->columnSpanFull()
                ->schema([
                    TextEntry::make('admin_notes')->hiddenLabel()->markdown(),
                ])
                ->visible(fn (MyPoll $record): bool => filled($record->admin_notes)),

            Section::make(fn (MyPoll $record): string => $record->title)
                ->description(fn (MyPoll $record): ?string => $record->isInReview() ? 'Diese Umfrage befindet sich in der Überprüfung.' : null)
                ->icon('heroicon-o-document-text')
                ->columnSpanFull()
                ->schema([
                    TextEntry::make('description')
                        ->label('Beschreibung')
                        ->markdown()
                        ->columnSpanFull()
                        ->visible(fn (MyPoll $record): bool => filled($record->description)),

                    Grid::make(['sm' => 1, 'md' => 3])->schema([
                        TextEntry::make('not_anonymous')
                            ->label('Anonymität')
                            ->icon(fn (MyPoll $record): string => $record->not_anonymous ? 'heroicon-o-lock-open' : 'heroicon-o-lock-closed')
                            ->state(fn (MyPoll $record): string => $record->not_anonymous ? 'Sichtbar' : 'Anonym'),

                        TextEntry::make('closes_after')
                            ->label('Ende der Umfrage')
                            ->icon('heroicon-o-clock')
                            ->state(fn (MyPoll $record): string => $record->isVisibleForPublic()
                                ? (Carbon::make($record->closes_after)?->diffForHumans() ?? '–')
                                : ClosesAfter::from($record->closes_after)->getLabel()),

                        TextEntry::make('category.title')
                            ->label('Kategorie')
                            ->icon('heroicon-o-tag')
                            ->placeholder('Keine'),
                    ]),
                ]),

            Section::make('Statistiken')
                ->icon('heroicon-o-chart-bar')
                ->columnSpanFull()
                ->schema([
                    Grid::make(['sm' => 2, 'lg' => 4])->schema([
                        TextEntry::make('answers_count')
                            ->label('Antworten')
                            ->icon('heroicon-o-check-badge')
                            ->state(fn (MyPoll $record): int => $record->answers()->count()),

                        TextEntry::make('participants_count')
                            ->label('Teilnehmer')
                            ->icon('heroicon-o-users')
                            ->state(fn (MyPoll $record): int => $record->participants()->count()),

                        TextEntry::make('questions_count')
                            ->label('Fragen')
                            ->icon('heroicon-o-question-mark-circle')
                            ->state(fn (MyPoll $record): int => $record->questions()->count()),

                        IconEntry::make('approved')
                            ->label('Status')
                            ->icon(fn (MyPoll $record): string => match (true) {
                                $record->hasEnded() => 'heroicon-o-lock-closed',
                                $record->isVisibleForPublic() => 'heroicon-o-eye',
                                $record->isApproved() => 'heroicon-o-check-circle',
                                $record->isInReview() => 'heroicon-o-scale',
                                default => 'heroicon-o-pencil-square',
                            })
                            ->color(fn (MyPoll $record): string => match (true) {
                                $record->hasEnded(), $record->isVisibleForPublic(), $record->isApproved() => 'success',
                                default => 'warning',
                            }),
                    ]),
                ])
                ->visible(fn (MyPoll $record): bool => $record->isApproved()),
        ])->columns(1);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'create' => CreateMyPoll::route('/create'),
            'index' => ListMyPolls::route('/'),
            'view' => ViewMyPoll::route('/{record}'),
            'edit' => EditMyPoll::route('/{record}/edit'),
        ];
    }
}
