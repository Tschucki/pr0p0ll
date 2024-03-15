<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Widiu7omo\FilamentBandel\Actions\BanAction;
use Widiu7omo\FilamentBandel\Actions\UnbanAction;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $slug = 'benutzer';

    protected static ?string $label = 'Benutzer';
    protected static ?string $pluralLabel = 'Benutzer';


    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function canAccess(): bool
    {
        return \Auth::user()->isAdmin();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->searchable()->sortable()->toggleable()->label('ID'),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable()->toggleable()->label('Name'),
                Tables\Columns\TextColumn::make('polls_count')->label('Anzahl Umfragen')->sortable()->toggleable()->counts('polls'),
                Tables\Columns\TextColumn::make('participations_count')->sortable()->toggleable()->label('Anzahl Teilnahmen')->counts('participations'),
                Tables\Columns\IconColumn::make('email_verified_at')->boolean()->sortable()->toggleable()->label('E-Mail verifiziert'),
                Tables\Columns\IconColumn::make('admin')->boolean()->sortable()->toggleable()->label('Ist Admin'),
                Tables\Columns\TextColumn::make('created_at')->sortable()->toggleable()->label('Registriert am')->dateTime('d.m.Y H:i')->suffix(' Uhr'),
            ])
            ->filters([
                //
            ])
            ->actions([
                BanAction::make()->visible(fn ($record) => ! $record->isBanned()),
                UnbanAction::make()->visible(fn ($record) => $record->isBanned()),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
        ];
    }
}
