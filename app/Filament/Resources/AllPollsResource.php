<?php

namespace App\Filament\Resources;

use App\Enums\ClosesAfter;
use App\Filament\Resources\AllPollsResource\Pages;
use App\Models\Polls\MyPoll;
use App\Models\Polls\Poll;
use App\Models\Question;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AllPollsResource extends Resource
{
    protected static ?string $model = Poll::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $label = 'Umfragen';

    protected static ?string $pluralLabel = 'Umfragen';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('approved')->label('Genehmigt')->boolean()->sortable()->toggleable(),
                Tables\Columns\IconColumn::make('visible_to_public')->label('Sichtbar für Öffentlichkeit')->boolean()->sortable()->toggleable(),
                Tables\Columns\IconColumn::make('in_review')->label('Prüfung nötig')->boolean()->sortable()->toggleable(),
                Tables\Columns\IconColumn::make('published_at')->label('Veröffentlicht am')->boolean()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('title')->label('Titel')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('user.name')->label('Ersteller')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('questions_count')->counts('questions')->label('Anzahl Fragen')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('answers_count')->counts('answers')->label('Anzahl Antworten')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime('d.m.Y H:i')->suffix(' Uhr')->label('Änderungsdatum')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d.m.Y H:i')->suffix(' Uhr')->label('Erstelldatum')->sortable()->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make($infolist->getRecord()->title)->schema([
                TextEntry::make('description')->columnSpanFull()->label('Beschreibung')->markdown(),
                TextEntry::make('not_anonymous')->label('Anonymität')->icon(fn (MyPoll $poll) => ! $poll->not_anonymous ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open')->state(fn (MyPoll $poll) => $poll->not_anonymous ? 'Sein Name wird angezeigt' : 'Sein Name wird nicht angezeigt'),
                TextEntry::make('closes_after')->label('Ende der Umfrage')->icon('heroicon-o-clock')->state(fn (Poll $poll) => ClosesAfter::from($poll->closes_after)->getLabel()),
                RepeatableEntry::make('questions')->label('Fragen')->schema([
                    TextEntry::make('title')->label('Frage'),
                    TextEntry::make('description')->visible(fn (Question $question) => $question->description)->label('Beschreibung'),
                    TextEntry::make('questionType.title')->label('Typ'),
                ])->columnSpanFull(),
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
            'index' => Pages\ListAllPolls::route('/'),
            'view' => Pages\ViewAllPolls::route('/{record}'),
        ];
    }
}
