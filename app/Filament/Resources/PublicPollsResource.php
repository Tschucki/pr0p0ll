<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PublicPollsResource\Pages;
use App\Models\Polls\MyPoll;
use App\Models\Polls\PublicPoll;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PublicPollsResource extends Resource
{
    protected static ?string $model = PublicPoll::class;

    protected static ?string $navigationGroup = 'Umfragen';

    protected static ?string $label = 'Öffentliche Umfrage';

    protected static ?string $pluralLabel = 'Öffentliche Umfragen';

    protected static ?string $icon = 'heroicon-o-clipboard-list';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Stack::make([
                    Tables\Columns\TextColumn::make('title')->label('Titel')->searchable()->sortable(),
                ]),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('participate')->label('Teilnehmen'),
            ])
            ->bulkActions([])
            ->query(MyPoll::query()
                ->where('visible_to_public', true)
                ->where('approved', true)
                ->where('in_review', false)
                ->withoutGlobalScope(SoftDeletingScope::class)
            );
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
            'index' => Pages\ListPublicPolls::route('/'),
        ];
    }
}
