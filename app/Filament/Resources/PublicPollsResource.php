<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\Gender;
use App\Enums\Nationality;
use App\Filament\Pages\PollResults;
use App\Filament\Pages\Pr0PostCreator;
use App\Filament\Resources\PublicPollsResource\Pages\ListPublicPolls;
use App\Filament\Resources\PublicPollsResource\Pages\PollParticipation;
use App\Models\Polls\PublicPoll;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;

class PublicPollsResource extends Resource
{
    protected static ?string $model = PublicPoll::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Umfragen';

    protected static ?string $label = 'Öffentliche Umfrage';

    protected static ?string $slug = 'umfragen';

    protected static ?string $pluralLabel = 'Öffentliche Umfragen';

    protected static ?string $icon = 'heroicon-o-clipboard-list';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Stack::make([
                    TextColumn::make('title')
                        ->label('Titel')
                        ->weight(FontWeight::ExtraBold)
                        ->size(TextSize::Large)
                        ->searchable(),
                    TextColumn::make('description')
                        ->label('Beschreibung')
                        ->color('gray')
                        ->limit(180)
                        ->searchable(),

                    Split::make([
                        TextColumn::make('user.name')
                            ->label('Ersteller')
                            ->icon('heroicon-o-user')
                            ->color('gray')
                            ->grow(false)
                            ->state(fn (PublicPoll $record): string => $record->not_anonymous ? $record->user->name : 'Anonym'),
                        TextColumn::make('category.title')
                            ->label('Kategorie')
                            ->icon('heroicon-o-tag')
                            ->color('gray')
                            ->grow(false)
                            ->placeholder('Allgemein')
                            ->searchable(),
                        TextColumn::make('participants_count')
                            ->label('Teilnehmer')
                            ->icon('heroicon-o-users')
                            ->color('gray')
                            ->grow(false)
                            ->counts('participants'),
                        TextColumn::make('closes_after')
                            ->label('Endet')
                            ->icon('heroicon-o-clock')
                            ->color('gray')
                            ->state(fn (PublicPoll $record): string => $record->hasEnded()
                                ? 'Geschlossen'
                                : Carbon::make($record->published_at)?->add($record->closes_after)->diffForHumans()),
                    ]),

                    Split::make([
                        TextColumn::make('within_target_group')
                            ->label('Zielgruppe')
                            ->badge()
                            ->grow(false)
                            ->icon(fn (PublicPoll $record): string => $record->userIsWithinTargetGroup(Auth::user()) ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                            ->color(fn (PublicPoll $record): string => $record->userIsWithinTargetGroup(Auth::user()) ? 'success' : 'danger')
                            ->state(fn (PublicPoll $record): string => $record->userIsWithinTargetGroup(Auth::user()) ? 'In Zielgruppe' : 'Außerhalb Zielgruppe'),
                        TextColumn::make('participated')
                            ->label('Teilnahme')
                            ->badge()
                            ->grow(false)
                            ->icon(fn (PublicPoll $record): string => $record->userParticipated(Auth::user()) ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                            ->color(fn (PublicPoll $record): string => $record->userParticipated(Auth::user()) ? 'success' : 'gray')
                            ->state(fn (PublicPoll $record): string => $record->userParticipated(Auth::user()) ? 'Teilgenommen' : 'Noch nicht'),
                    ]),
                ])->space(2),
            ])
            ->groups([
                Group::make('category.title')->label('Kategorie'),
            ])
            ->emptyStateHeading('Keine öffentlichen Umfragen')
            ->emptyStateDescription('Es gibt aktuell keine Umfragen, die deine Kriterien erfüllen.')
            ->emptyStateIcon('heroicon-o-clipboard-document-check')
            ->recordActions([
                Action::make('target_group')->visible(fn (PublicPoll $record) => $record->target_group !== null)->schema(function (PublicPoll $record) {

                    return [
                        Grid::make()->schema([
                            TextEntry::make('min_age')->label('Mindestalter')->state($record->target_group[0]['data']['min_age'] ?? 'Egal'),
                            TextEntry::make('max_age')->label('Höchstalter')->state($record->target_group[0]['data']['max_age'] ?? 'Egal'),
                            TextEntry::make('nationality')->label('Nationalität')->state(isset($record->target_group[0]['data']['nationality']) ? collect($record->target_group[0]['data']['nationality'])->filter(fn ($n) => $n !== null)->map(fn (string $n) => Nationality::from($n)->getLabel())->implode(', ') : 'Egal'),
                            TextEntry::make('gender')->label('Geschlecht')->state(isset($record->target_group[0]['data']['gender']) ? Gender::from($record->target_group[0]['data']['gender'])->getLabel() : 'Egal'),
                        ]),
                    ];
                })->modalSubmitAction(false)->button()->label('Zielgruppe')->modalHeading('Zielgruppe'),
                Action::make('results')->button()->label('Ergebnisse ansehen')->url(fn (PublicPoll $record) => route('filament.pr0p0ll.resources.umfragen.results', ['record' => $record]))->visible(fn (PublicPoll $record) => $record->resultsArePublic() || Auth::user()?->isAdmin()),
                Action::make('participate')
                    ->icon('heroicon-o-plus-circle')
                    ->button()
                    ->label('Teilnehmen')
                    ->url(fn (PublicPoll $record): string => route('filament.pr0p0ll.resources.umfragen.teilnehmen', ['record' => $record]))
                    ->disabled(fn (PublicPoll $record) => $record->userParticipated(Auth::user()) || ! $record->userIsWithinTargetGroup(Auth::user()) || $record->hasEnded()),
            ])
            ->filters([
                SelectFilter::make('in_target_group')
                    ->label('Zielgruppe')
                    ->options([
                        true => 'Innerhalb Zielgruppe',
                        false => 'Außerhalb Zielgruppe',
                    ])
                    ->default(fn (Page $livewire) => $livewire->activeTab === 'offene-umfragen' ? '1' : null)
                    ->query(function (Builder $query, $data) {
                        if ($data['value'] === '1') {
                            $pollIdsWithinTargetGroup = PublicPoll::all()->filter(fn ($poll) => $poll->userIsWithinTargetGroup(Auth::user()) === true)->pluck('id');
                            $query->whereIn('id', $pollIdsWithinTargetGroup);
                        }
                        if ($data['value'] === '0') {
                            $pollIdsWithinTargetGroup = PublicPoll::all()->filter(fn ($poll) => $poll->userIsWithinTargetGroup(Auth::user()) === false)->pluck('id');
                            $query->whereIn('id', $pollIdsWithinTargetGroup);
                        }
                    }),
            ])
            ->toolbarActions([])
            ->query(
                PublicPoll::query()
                    ->where('visible_to_public', true)
                    ->where('approved', true)
                    ->where('in_review', false)
                    ->withoutGlobalScope(SoftDeletingScope::class)
            )
            ->paginated([10, 25, 50]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        // TODO: Add Cache
        $notParticipatedPolls = PublicPoll::whereDoesntHave('participants', static function (Builder $query) {
            $query->where('participant_id', Auth::id());
        })
            ->where('original_content_link', null)
            ->where('visible_to_public', true)
            ->where('approved', true)
            ->where('in_review', false)
            ->where('closes_at', '>', now())
            ->withoutGlobalScope(SoftDeletingScope::class)
            ->get();

        $inTargetGroup = $notParticipatedPolls->filter(fn (PublicPoll $record) => $record->userIsWithinTargetGroup(Auth::user()));

        return Number::abbreviate($inTargetGroup->count());
    }

    public static function canViewResults(PublicPoll $poll): bool
    {
        if (Auth::user()?->isAdmin()) {
            return true;
        }

        if ($poll->resultsArePublic()) {
            return true;
        }

        if (Auth::user()?->getKey() === $poll->user->getKey()) {
            return true;
        }

        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPublicPolls::route('/'),
            'teilnehmen' => PollParticipation::route('/{record}/teilnehmen'),
            'results' => PollResults::route('/{record}/auswertung'),
            'pr0post' => Pr0PostCreator::route('/{record}/pr0post'),
        ];
    }
}
