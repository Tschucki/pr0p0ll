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

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-table-cells';

    protected string $view = 'filament.pages.leaderboard';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Rangliste')
            ->description(function (): string {
                $users = User::withCount(['participations', 'approvedPolls'])
                    ->orderByDesc('participations_count')
                    ->orderByDesc('approved_polls_count')
                    ->get(['id', 'name']);

                $position = $users->search(static fn (User $user): bool => $user->id === auth()->id());

                $position = $position !== false ? $position + 1 : 'N/A';

                return "Du bist auf Platz {$position} von {$users->count()} Nutzern.";
            })
            ->query(User::query()->withCount(['participations', 'approvedPolls'])
                ->orderByDesc('participations_count')
                ->orderByDesc('approved_polls_count'))
            ->defaultSort('participations_count', 'desc')
            ->columns([
                TextColumn::make('position')
                    ->label('Platz')
                    ->badge()
                    ->prefix('#')
                    ->color(static fn (HasTable $livewire, stdClass $rowLoop): string => match ($rowLoop->iteration + ($livewire->getTableRecordsPerPage() * ($livewire->getTablePage() - 1))) {
                        1 => 'warning',
                        2, 3 => 'info',
                        default => 'gray',
                    })
                    ->state(static fn (HasTable $livewire, stdClass $rowLoop): string => (string) (
                        $rowLoop->iteration +
                        ($livewire->getTableRecordsPerPage() * ($livewire->getTablePage() - 1))
                    )),
                TextColumn::make('name')
                    ->label('Name')
                    ->icon('heroicon-o-user'),
                TextColumn::make('participations_count')
                    ->label('Teilnahmen')
                    ->counts('participations')
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('approved_polls_count')
                    ->label('Eigene Umfragen')
                    ->counts('approvedPolls')
                    ->alignCenter()
                    ->sortable(),
            ])
            ->emptyStateHeading('Noch keine Einträge')
            ->emptyStateIcon('heroicon-o-trophy')
            ->paginated([10, 25, 50]);
    }
}
