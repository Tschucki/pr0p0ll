<?php

namespace App\Filament\Resources;

use App\Enums\ClosesAfter;
use App\Filament\Resources\MyPollResource\Pages\CreateMyPoll;
use App\Filament\Resources\MyPollResource\Pages\EditMyPoll;
use App\Filament\Resources\MyPollResource\Pages\ListMyPolls;
use App\Filament\Resources\MyPollResource\Pages\MyPollResults;
use App\Filament\Resources\MyPollResource\Pages\ViewMyPoll;
use App\Models\Polls\MyPoll;
use App\Models\Question;
use App\Models\QuestionType;
use Filament\Forms;
use Filament\Forms\Components;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Yepsua\Filament\Tables\Components\RatingColumn;

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
                        Components\MarkdownEditor::make('description')->toolbarButtons([
                            'blockquote',
                            'bold',
                            'bulletList',
                            'heading',
                            'italic',
                            'link',
                            'orderedList',
                            'redo',
                            'strike',
                            'table',
                            'undo',
                        ])->label('Beschreibung')->required(),
                        Select::make('closes_after')->label('Ende der Umfrage')->hint('Nachdem die Umfrage genehmigt wurde')->options(ClosesAfter::class)->default('+3 weeks')->required()->helperText('Es wird dir nicht möglich sein, die Umfrage frühzeitig zu beenden.'),
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
                                    return Block::make($questionType->getKey())->label($questionType->title)->schema([
                                        Components\Hidden::make('question_type_id')->default($questionType->getKey()),
                                        Components\Hidden::make('uuid')->default(\Str::uuid()->toString()),
                                        TextInput::make('title')->label('Titel')->maxLength(255)->required()->live(),
                                        Textarea::make('description')->label('Beschreibung')->nullable(),
                                        Components\Repeater::make('options')->label('Auswahlmöglichkeiten')->schema([
                                            TextInput::make('title')->required()->label('Titel')->maxLength(255),
                                            TextInput::make('helperText')->nullable()->label('Hilfetext')->maxLength(255),
                                        ])->required()->visible(fn () => $questionType->hasOptions()),
                                    ])->reactive()->icon($questionType->icon)->label(function (?array $state) use ($questionType): string {
                                        if ($state === null) {
                                            return $questionType->title;
                                        }

                                        return $state['title'] ? $state['title'].' - '.$questionType->title : $questionType->title;
                                    });
                                })->toArray();
                            })->collapsible()->collapsed(fn (MyPoll $poll) => $poll)->reactive()->live()->blockNumbers(false),
                    ]),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Titel')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('questions_count')->counts('questions')->label('Anzahl Fragen')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('answers_count')->counts('answers')->label('Anzahl Antworten')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('participants_count')->counts('participants')->label('Anzahl Teilnehmer')->sortable()->toggleable(),
                RatingColumn::make('rating')->state(fn (MyPoll $myPoll) => $myPoll->participants()->avg('rating'))->label('Bewertung'),
                Tables\Columns\TextColumn::make('updated_at')->dateTime('d.m.Y H:i')->suffix(' Uhr')->label('Änderungsdatum')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d.m.Y H:i')->suffix(' Uhr')->label('Erstelldatum')->sortable()->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('results')->button()->label('Ergebnisse ansehen')->url(fn (MyPoll $poll) => route('filament.pr0p0ll.resources.my-polls.results', ['record' => $poll])),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])->query(MyPoll::query()->where('user_id', auth()->id()));
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Nachricht von Admin')->schema([
                TextEntry::make('admin_notes')->label(false),
            ])->visible(fn (MyPoll $myPoll) => $myPoll->admin_notes),
            Section::make($infolist->getRecord()->title)->schema([
                TextEntry::make('description')->columnSpanFull()->label('Beschreibung')->markdown(),
                TextEntry::make('not_anonymous')->label('Anonymität')->icon(fn (MyPoll $poll) => ! $poll->not_anonymous ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open')->state(fn (MyPoll $poll) => $poll->not_anonymous ? 'Dein Name wird angezeigt' : 'Dein Name wird nicht angezeigt'),
                TextEntry::make('closes_after')->label('Ende der Umfrage')->icon('heroicon-o-clock')->state(fn (MyPoll $poll) => ClosesAfter::from($poll->closes_after)->getLabel()),
                RepeatableEntry::make('questions')->label('Fragen')->schema([
                    TextEntry::make('title')->label('Frage'),
                    TextEntry::make('description')->visible(fn (Question $question) => $question->description)->label('Beschreibung'),
                    TextEntry::make('questionType.title')->label('Typ'),
                ])->columnSpanFull(),
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
            'results' => MyPollResults::route('/{record}/auswertung'),
            'edit' => EditMyPoll::route('/{record}/edit'),
        ];
    }
}
