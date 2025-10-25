<?php

declare(strict_types=1);

namespace App\Filament\Resources\AllPolls;

use Auth;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use App\Filament\Resources\AllPolls\Pages\ListAllPolls;
use App\Filament\Resources\AllPolls\Pages\ViewAllPolls;
use App\Enums\ClosesAfter;
use App\Filament\Resources\AllPollsResource\Pages;
use App\Models\Polls\MyPoll;
use App\Models\Polls\Poll;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AllPollsResource extends Resource
{
    protected static ?string $model = Poll::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string | \UnitEnum | null $navigationGroup = 'Administration';

    protected static ?string $label = 'Umfragen';

    protected static ?string $pluralLabel = 'Umfragen';

    public static function canAccess(): bool
    {
        return Auth::user()->isAdmin();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('approved')->label('Genehmigt')->boolean()->sortable()->toggleable(),
                IconColumn::make('visible_to_public')->label('Sichtbar für Öffentlichkeit')->boolean()->sortable()->toggleable(),
                IconColumn::make('in_review')->label('Prüfung nötig')->boolean()->sortable()->toggleable(),
                TextColumn::make('published_at')->label('Veröffentlicht am')->dateTime('d.m.Y H:i')->sortable()->toggleable(),
                TextColumn::make('title')->label('Titel')->sortable()->searchable(),
                TextColumn::make('user.name')->label('Ersteller')->sortable()->searchable(),
                TextColumn::make('questions_count')->counts('questions')->label('Anzahl Fragen')->sortable()->toggleable(),
                TextColumn::make('answers_count')->counts('answers')->label('Anzahl Antworten')->sortable()->toggleable(),
                TextColumn::make('updated_at')->dateTime('d.m.Y H:i')->suffix(' Uhr')->label('Änderungsdatum')->sortable()->toggleable(),
                TextColumn::make('created_at')->dateTime('d.m.Y H:i')->suffix(' Uhr')->label('Erstelldatum')->sortable()->toggleable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->paginated([10, 25, 50]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make($schema->getRecord()->title)->schema([
                TextEntry::make('description')->columnSpanFull()->label('Beschreibung')->markdown(),
                TextEntry::make('user.name')->label('Benutzer'),
                TextEntry::make('not_anonymous')->label('Anonymität')->icon(fn (MyPoll $poll) => ! $poll->not_anonymous ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open')->state(fn (MyPoll $poll) => $poll->not_anonymous ? 'Sein Name wird angezeigt' : 'Sein Name wird nicht angezeigt'),
                TextEntry::make('closes_after')->label('Ende der Umfrage')->icon('heroicon-o-clock')->state(fn (Poll $poll) => ClosesAfter::from($poll->closes_after)->getLabel()),
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
            'index' => ListAllPolls::route('/'),
            'view' => ViewAllPolls::route('/{record}'),
        ];
    }
}
