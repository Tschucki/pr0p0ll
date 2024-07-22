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
use Filament\Forms;
use Filament\Forms\Components;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class MyPollResource extends Resource
{
    protected static ?string $model = MyPoll::class;

    protected static ?string $label = 'Meine Umfrage';

    protected static ?string $navigationGroup = 'Umfragen';

    protected static ?string $pluralLabel = 'Meine Umfragen';

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    public static function form(Form $form): Form
    {

        return $form
            ->schema([
                Components\Tabs::make()->tabs([
                    Components\Tabs\Tab::make('Allgemein')->schema([
                        Components\Toggle::make('not_anonymous')->label('Möchtest du die Umfrage so veröffentlichen, dass dein Name sichtbar ist?')->inline(false)->required()->default(true)->helperText('Es geht nur darum ob dein Name bei der Umfrage angezeigt wird. Das pr0p0ll-Team sieht natürlich, dass du diese Umfrage erstellt hast. Das soll dafür sorgen, dass Teilnehmer nicht beeinflusst werden.'),
                        TextInput::make('title')->label('Titel')->maxLength(255)->required(),
                        Select::make('category_id')->searchable()->label('Kategorie')->options(fn () => Category::where('enabled', true)->pluck('title', 'id'))->nullable()->native(false),
                        Textarea::make('description')->label('Beschreibung')->nullable(),
                        Select::make('closes_after')->label('Ende der Umfrage')->hint('Zeitraum beginnt nachdem die Umfrage genehmigt wurde.')->options(ClosesAfter::class)->default('+3 weeks')->required()->helperText('Es wird dir nicht möglich sein, die Umfrage frühzeitig zu beenden.'),
                    ]),
                    Components\Tabs\Tab::make('Zielgruppe')->schema([
                        Components\Fieldset::make('target_group_count')->label('Potentielle Teilnehmerzahl')
                            ->schema([Components\Placeholder::make('participants_count')->label('')->content(function (Forms\Get $get) {
                                try {
                                    return TargetGroupService::calculateTargetGroupFromBuilder($get('target_group')).' Teilnehmer';
                                } catch (\Throwable $throwable) {
                                    Log::info($throwable->getMessage());
                                    Notification::make('target_group_error')->title('Fehler')->body('Beim berechnen der Zielgruppe ist ein Fehler aufgetreten.')->danger()->actions([
                                        Action::make('target_group_error_action')->label('Melden')->button()->url('https://github.com/pr0p0ll/pr0p0ll/issues/new', true),
                                    ])->send();
                                }

                                return '';
                            })]),
                        Components\Placeholder::make('target_group_info1')->label('')->content('Hier kannst du die Zielgruppe definieren, die an der Umfrage teilnehmen darf.')->columnSpanFull(),
                        Components\Builder::make('target_group')->label('Zielgruppen Builder')->blocks([
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
                    Components\Tabs\Tab::make('Fragen')->schema([
                        Forms\Components\Builder::make('questions')->afterStateHydrated(function (Components\Builder $component) use ($form) {
                            /**
                             * @var MyPoll $poll
                             * */
                            $poll = $form->getRecord();
                            if ($poll) {
                                $component->state($poll->getBuilderData());
                            }
                        })->label('')->required()
                            ->blocks(function () {
                                return QuestionType::active()->get()->map(function (QuestionType $questionType) {
                                    return Block::make((string) $questionType->getKey())->label($questionType->title)->schema([
                                        Components\Hidden::make('question_type_id')->default($questionType->getKey()),
                                        Components\Hidden::make('uuid')->default(\Str::uuid()->toString()),
                                        TextInput::make('title')->extraAttributes(['class' => $questionType->getKey().'Input'])->label('Titel')->maxLength(255)->required()->live(true),
                                        Textarea::make('description')->label('Beschreibung')->nullable(),
                                        Components\Repeater::make('options')->label('Auswahlmöglichkeiten')->schema([
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
                            })->reorderable(false)->collapsible()->collapsed(fn (MyPoll $poll) => $poll)->reactive()->live(true)->blockNumbers(false),
                    ]),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('status')->icon('heroicon-o-cog')->label('Status')->state(function (MyPoll $myPoll) {
                    if ($myPoll->hasEnded()) {
                        return 'Beendet';
                    }
                    if ($myPoll->isVisibleForPublic()) {
                        return 'Öffentlich sichtbar';
                    }
                    if ($myPoll->isApproved()) {
                        return 'Genehmigt';
                    }
                    if ($myPoll->isInReview()) {
                        return 'In Überprüfung';
                    }

                    return 'Entwurf';
                })->icon(function (MyPoll $myPoll) {
                    if ($myPoll->hasEnded()) {
                        return 'heroicon-o-lock-closed';
                    }
                    if ($myPoll->isVisibleForPublic()) {
                        return 'heroicon-o-eye';
                    }
                    if ($myPoll->isApproved()) {
                        return 'heroicon-o-check-circle';
                    }
                    if ($myPoll->isInReview()) {
                        return 'heroicon-o-scale';
                    }

                    return 'heroicon-o-pencil-square';
                })->iconColor(function (MyPoll $myPoll) {
                    if ($myPoll->hasEnded()) {
                        return 'success';
                    }
                    if ($myPoll->isVisibleForPublic()) {
                        return 'success';
                    }
                    if ($myPoll->isApproved()) {
                        return 'success';
                    }

                    return 'warning';
                }),
                Tables\Columns\TextColumn::make('closes_at')->label('Ende')->dateTime('d.m.Y H:i')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('title')->label('Titel')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('answers_count')->counts('answers')->label('Anzahl Antworten')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('participants_count')->counts('participants')->label('Anzahl Teilnehmer')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime('d.m.Y H:i')->suffix(' Uhr')->label('Änderungsdatum')->sortable()->toggleable()->toggledHiddenByDefault(),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d.m.Y H:i')->suffix(' Uhr')->label('Erstelldatum')->sortable()->toggleable()->toggledHiddenByDefault(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('results')->visible(fn (MyPoll $poll) => $poll->visible_to_public === true)->button()->label('Ergebnisse ansehen')->url(fn (MyPoll $poll) => route('filament.pr0p0ll.resources.umfragen.results', ['record' => $poll])),
                Tables\Actions\ViewAction::make()->iconButton(),
                Tables\Actions\EditAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->paginated([10, 25, 50])
            ->query(MyPoll::query()->where('user_id', auth()->id()));
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Nachricht von Admin')->schema([
                TextEntry::make('admin_notes')->label(''),
            ])->visible(fn (MyPoll $myPoll) => $myPoll->admin_notes),
            Section::make($infolist->getRecord()->title)->schema([
                TextEntry::make('description')->visible(fn (MyPoll $myPoll) => $myPoll->description !== null)->columnSpanFull()->label('Beschreibung')->markdown(),
                TextEntry::make('not_anonymous')->label('Anonymität')->icon(fn (MyPoll $poll) => ! $poll->not_anonymous ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open')->state(fn (MyPoll $poll) => $poll->not_anonymous ? 'Dein Name wird angezeigt' : 'Dein Name wird nicht angezeigt'),
                TextEntry::make('closes_after')->label('Ende der Umfrage')->icon('heroicon-o-clock')->state(fn (MyPoll $poll) => $poll->isVisibleForPublic() ? Carbon::make($poll->closes_after)?->diffForHumans() : ClosesAfter::from($poll->closes_after)->getLabel()),
                /*Section::make('Statistiken')->schema([
                    // TODO: Add statistics (participation rate, etc.)
                ])->columns([
                    'sm' => 1,
                    'md' => 2,
                ]),*/
            ])->columns([
                'sm' => 1,
                'md' => 2,
            ]),
        ]);
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
