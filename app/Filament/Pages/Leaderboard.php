<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class Leaderboard extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    protected static string $view = 'filament.pages.leaderboard';

    public function table(Table $table): Table
    {
        return $table
            ->query(User::query()->select('id', 'name'))
            ->defaultSort('participations_count', 'desc')
            ->columns([
                TextColumn::make('position')->badge()->label('Position')->prefix('#')->state(
                    static function (HasTable $livewire, \stdClass $rowLoop): string {
                        return (string) (
                            $rowLoop->iteration +
                            ($livewire->getTableRecordsPerPage() * (
                                $livewire->getTablePage() - 1
                            ))
                        );
                    }
                ),
                TextColumn::make('name')->label('Name'),
                TextColumn::make('participations_count')->label('Anzahl Teilnahmen')->counts('participations'),
                TextColumn::make('approved_polls_count')->label('Anzahl Umfragen')->counts('approvedPolls'),
            ])
            ->paginated([10, 25, 50]);
    }
}
