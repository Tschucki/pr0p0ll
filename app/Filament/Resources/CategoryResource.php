<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages\CreateCategory;
use App\Filament\Resources\CategoryResource\Pages\EditCategory;
use App\Filament\Resources\CategoryResource\Pages\ListCategories;
use App\Models\Category;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $pluralLabel = 'Kategorien';

    protected static ?string $label = 'Kategorie';

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kategorie-Details')
                    ->description('Eine Kategorie hilft Teilnehmern, Umfragen schneller zu finden.')
                    ->icon('heroicon-o-tag')
                    ->schema([
                        Grid::make(['sm' => 1, 'md' => 2])->schema([
                            TextInput::make('title')
                                ->label('Titel')
                                ->autofocus()
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(['md' => 2]),
                            Toggle::make('enabled')
                                ->label('Aktiv')
                                ->helperText('Inaktive Kategorien werden Teilnehmern nicht zur Auswahl angeboten.')
                                ->default(true)
                                ->inline(false)
                                ->columnSpan(['md' => 2]),
                        ]),
                        Textarea::make('description')
                            ->label('Beschreibung')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Titel')
                    ->icon('heroicon-o-tag')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Beschreibung')
                    ->limit(60)
                    ->toggleable()
                    ->wrap(),
                IconColumn::make('enabled')
                    ->label('Aktiv')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Erstellt am')
                    ->dateTime('d.m.Y H:i')
                    ->suffix(' Uhr')
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->defaultSort('title')
            ->emptyStateHeading('Noch keine Kategorien')
            ->emptyStateDescription('Lege die erste Kategorie an, damit Umfragen einsortiert werden können.')
            ->emptyStateIcon('heroicon-o-tag')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->paginated([10, 25, 50]);
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
            'index' => ListCategories::route('/'),
            'create' => CreateCategory::route('/create'),
            'edit' => EditCategory::route('/{record}/edit'),
        ];
    }
}
