<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use stdClass;

class Leaderboard extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    protected static string $view = 'filament.pages.leaderboard';

    public function table(Table $table): Table
    {
        return $table
            ->description(function (HasTable $livewire) {
                // go through all the records and find the current auth user and show the position
                $page = 1;
                $users = User::withCount(['participations', 'approvedPolls'])
                    ->orderByDesc('participations_count')
                    ->orderByDesc('approved_polls_count')
                    ->get(['id', 'name']);

                // get the position of the current user
                $position = $users->search(static function ($user) {
                    return $user->id === auth()->id();
                });


                if ($position !== false) {
                    $position++;
                } else {
                    $position = 'N/A';
                }

                return "Du bist auf Platz {$position} von {$users->count()} Nutzern.";
            })
            ->query(User::query()->withCount(['participations', 'approvedPolls'])
                ->orderByDesc('participations_count')
                ->orderByDesc('approved_polls_count'))
            ->defaultSort('participations_count', 'desc')
            ->columns([
                TextColumn::make('position')->badge()->label('Position')->prefix('#')->state(
                    static function (HasTable $livewire, stdClass $rowLoop): string {
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
