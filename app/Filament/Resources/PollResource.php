<?php

namespace App\Filament\Resources;

use App\Enums\AnswerType;
use App\Filament\Resources\PollResource\Pages;
use App\Filament\Resources\PollResource\RelationManagers;
use App\Models\Poll;
use App\Models\QuestionType;
use Filament\Forms;
use Filament\Forms\Components;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Components\Tab;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Hamcrest\Core\Set;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PollResource extends Resource
{
    protected static ?string $model = Poll::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {

        return $form
            ->schema([
                Components\Tabs::make()->tabs([
                    Components\Tabs\Tab::make('Allgemein')->schema([
                        Components\Toggle::make('anonymity')->label('Möchtest du die Umfrage so veröffentlichen, dass dein Name sichtbar ist?')->inline(false)->required()->default(true)->helperText('Es geht nur darum ob dein Name bei der Umfrage angezeigt wird. Das pr0p0ll-Team sieht natürlich, dass du diese Umfrage erstellt hast. Das soll dafür sorgen, dass Teilnehmer nicht beeinflusst werden.'),
                        TextInput::make('title')->label('Titel')->maxLength(255)->required(),
                        Components\MarkdownEditor::make('description')   ->toolbarButtons([
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
                        Select::make('closes_after')->label('Ende der Umfrage')->hint('Nachdem die Umfrage genehmigt wurde')->options([
                            '+3 days' => 'Nach 3 Tagen',
                            '+1 week' => 'Nach 1 Woche',
                            '+2 weeks' => 'Nach 2 Wochen',
                            '+3 weeks' => 'Nach 3 Wochen',
                            '+1 month' => 'Nach 1 Monat',
                            '+2 months' => 'Nach 2 Monaten',
                            '+3 months' => 'Nach 3 Monaten',
                            '+6 months' => 'Nach 6 Monaten',
                            '+1 year' => 'Nach 1 Jahr',
                        ])->default('+3 weeks')->required()->helperText('Es wird dir nicht möglich sein, die Umfrage frühzeitig zu beenden.'),
                    ]),
                    Components\Tabs\Tab::make('Fragen')->schema([
                        Forms\Components\Builder::make('questions')->afterStateHydrated(function (Components\Builder $component) use($form) {
                            /**
                             * @var Poll $poll
                             * */
                            $poll = $form->getRecord();
                            if($poll) {
                                $component->state($poll->getBuilderData());
                            }
                        })->label('')->required()
                            ->blocks(function () {
                                return QuestionType::active()->get()->map(function (QuestionType $questionType) {
                                    return Block::make($questionType->getKey())->label($questionType->title)->schema([
                                        Components\Hidden::make('question_type_id')->default($questionType->getKey()),
                                        TextInput::make('title')->label('Titel')->maxLength(255)->required()->live(),
                                        Textarea::make('hint')->label('Hilfe für Nutzer')->nullable(),
                                        Components\Repeater::make('options')->label('Auswahlmöglichkeiten')->schema([
                                            TextInput::make('title')->required()->label('Titel')->maxLength(255),
                                            TextInput::make('helperText')->nullable()->label('Hilfetext')->maxLength(255),
                                        ])->required()->visible(fn() => $questionType->hasOptions()),
                                    ])->icon($questionType->icon)->label(function (?array $state) use ($questionType): string {
                                        if ($state === null) {
                                            return $questionType->title ;
                                        }

                                        return $state['title'] ? $state['title'] . ' - ' . $questionType->title : 'Neue Frage';
                                    });
                                })->toArray();
                            })->collapsible()->collapsed(fn(Poll $poll) => $poll)->reactive()->live()->blockNumbers(false)
                    ]),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Titel')->sortable()->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListPolls::route('/'),
            'create' => Pages\CreatePoll::route('/create'),
            'edit' => Pages\EditPoll::route('/{record}/edit'),
        ];
    }
}
