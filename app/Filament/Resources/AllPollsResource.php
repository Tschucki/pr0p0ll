<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\ClosesAfter;
use App\Filament\Resources\AllPollsResource\Pages\ListAllPolls;
use App\Filament\Resources\AllPollsResource\Pages\ViewAllPolls;
use App\Models\Polls\Poll;
use Auth;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AllPollsResource extends Resource
{
    protected static ?string $model = Poll::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

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
                TextColumn::make('title')
                    ->label('Titel')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->sortable()
                    ->searchable()
                    ->wrap(),
                TextColumn::make('user.name')
                    ->label('Ersteller')
                    ->icon('heroicon-o-user')
                    ->sortable()
                    ->searchable(),
                IconColumn::make('approved')
                    ->label('Genehmigt')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('visible_to_public')
                    ->label('Öffentlich')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('in_review')
                    ->label('In Prüfung')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('questions_count')
                    ->label('Fragen')
                    ->counts('questions')
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('answers_count')
                    ->label('Antworten')
                    ->counts('answers')
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('published_at')
                    ->label('Veröffentlicht')
                    ->dateTime('d.m.Y H:i')
                    ->suffix(' Uhr')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Erstellt')
                    ->dateTime('d.m.Y H:i')
                    ->suffix(' Uhr')
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                TextColumn::make('updated_at')
                    ->label('Geändert')
                    ->dateTime('d.m.Y H:i')
                    ->suffix(' Uhr')
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Keine Umfragen vorhanden')
            ->emptyStateIcon('heroicon-o-clipboard-document-list')
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
            Section::make(fn (Poll $record): string => $record->title)
                ->icon('heroicon-o-document-text')
                ->schema([
                    TextEntry::make('description')
                        ->label('Beschreibung')
                        ->markdown()
                        ->columnSpanFull()
                        ->visible(fn (Poll $record): bool => filled($record->description)),

                    Grid::make(['sm' => 1, 'md' => 3])->schema([
                        TextEntry::make('user.name')
                            ->label('Ersteller')
                            ->icon('heroicon-o-user'),

                        TextEntry::make('not_anonymous')
                            ->label('Anonymität')
                            ->icon(fn (Poll $record): string => $record->not_anonymous ? 'heroicon-o-lock-open' : 'heroicon-o-lock-closed')
                            ->state(fn (Poll $record): string => $record->not_anonymous ? 'Sein Name wird angezeigt' : 'Sein Name wird nicht angezeigt'),

                        TextEntry::make('closes_after')
                            ->label('Ende der Umfrage')
                            ->icon('heroicon-o-clock')
                            ->state(fn (Poll $record): string => ClosesAfter::from($record->closes_after)->getLabel()),
                    ]),
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
