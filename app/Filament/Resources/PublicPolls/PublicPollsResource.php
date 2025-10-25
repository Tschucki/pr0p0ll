<?php

declare(strict_types=1);

namespace App\Filament\Resources\PublicPolls;

use Filament\Schemas\Schema;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Actions\Action;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\PublicPolls\Pages\ListPublicPolls;
use App\Filament\Resources\PublicPolls\Pages\PollParticipation;
use App\Enums\Gender;
use App\Enums\Nationality;
use App\Filament\Pages\PollResults;
use App\Filament\Pages\Pr0PostCreator;
use App\Filament\Resources\PublicPollsResource\Pages;
use App\Models\Polls\PublicPoll;
use Carbon\Carbon;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;

class PublicPollsResource extends Resource
{
    protected static ?string $model = PublicPoll::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Umfragen';

    protected static ?string $label = 'Öffentliche Umfrage';

    protected static ?string $slug = 'umfragen';

    protected static ?string $pluralLabel = 'Öffentliche Umfragen';

    protected static ?string $icon = 'heroicon-o-clipboard-list';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-check';

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
                    TextColumn::make('title')->weight(FontWeight::ExtraBold)->label('Titel')->prefix('Titel: ')->searchable(),
                    TextColumn::make('description')->label('Beschreibung')->searchable(),
                    TextColumn::make('category.title')->prefix('Kategorie: ')->label('Kategorie')->searchable(),
                    TextColumn::make('closes_after')->label('Ende')->prefix('Endet in: ')->state(fn (PublicPoll $publicPoll) => $publicPoll->hasEnded() ? 'Geschlossen' : Carbon::make($publicPoll->published_at)?->add($publicPoll->closes_after)->diffForHumans().' ('.$publicPoll->closes_at->format('d.m.Y H:i').' Uhr)'),
                    TextColumn::make('user.name')->label('Ersteller')->prefix('Von: ')->state(fn (PublicPoll $publicPoll) => $publicPoll->not_anonymous ? $publicPoll->user->name : ''),
                    TextColumn::make('participants_count')->counts('participants')->prefix('Teilnehmer: ')->label('Teilnehmerzahl'),
                    TextColumn::make('within_target_group')->label('Innerhalb deiner Zielgruppe')->icon(function (PublicPoll $poll) {
                        return $poll->userIsWithinTargetGroup(Auth::user()) ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle';
                    })->iconColor(function (PublicPoll $poll) {
                        return $poll->userIsWithinTargetGroup(Auth::user()) ? 'success' : 'danger';
                    })->prefix('In Zielgruppe: ')->state(fn (PublicPoll $publicPoll) => $publicPoll->userIsWithinTargetGroup(Auth::user()) ? 'Ja' : 'Nein'),
                    TextColumn::make('participated')->label('Teilgenommen')->icon(function (PublicPoll $poll) {
                        return $poll->userParticipated(Auth::user()) ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle';
                    })->iconColor(function (PublicPoll $poll) {
                        return $poll->userParticipated(Auth::user()) ? 'success' : 'danger';
                    })->state(fn (PublicPoll $poll) => $poll->userParticipated(Auth::user()) ? 'Ja' : 'Nein')->prefix('Teilgenommen: '),
                ]),
            ])
            ->filters([])
            ->groups([
                Group::make('category.title')->label('Kategorie'),
            ])
            ->recordActions([
                Action::make('target_group')->visible(fn (PublicPoll $poll) => $poll->target_group !== null)->schema(function (PublicPoll $poll) {

                    return [
                        Grid::make()->schema([
                            TextEntry::make('min_age')->label('Mindestalter')->state($poll->target_group[0]['data']['min_age'] ?? 'Egal'),
                            TextEntry::make('max_age')->label('Höchstalter')->state($poll->target_group[0]['data']['max_age'] ?? 'Egal'),
                            TextEntry::make('nationality')->label('Nationalität')->state(isset($poll->target_group[0]['data']['nationality']) ? collect($poll->target_group[0]['data']['nationality'])->filter(fn ($n) => $n !== null)->map(fn (string $n) => Nationality::from($n)->getLabel())->implode(', ') : 'Egal'),
                            TextEntry::make('gender')->label('Geschlecht')->state(isset($poll->target_group[0]['data']['gender']) ? Gender::from($poll->target_group[0]['data']['gender'])->getLabel() : 'Egal'),
                        ]),
                    ];
                })->modalSubmitAction(false)->button()->label('Zielgruppe')->modalHeading('Zielgruppe'),
                Action::make('results')->button()->label('Ergebnisse ansehen')->url(fn (PublicPoll $poll) => route('filament.pr0p0ll.resources.umfragen.results', ['record' => $poll]))->visible(fn (PublicPoll $poll) => $poll->resultsArePublic() || Auth::user()?->isAdmin()),
                Action::make('participate')
                    ->icon('heroicon-o-plus-circle')
                    ->button()
                    ->label('Teilnehmen')
                    ->url(fn (PublicPoll $publicPoll): string => route('filament.pr0p0ll.resources.umfragen.teilnehmen', ['record' => $publicPoll]))
                    ->disabled(fn (PublicPoll $publicPoll) => $publicPoll->userParticipated(Auth::user()) || ! $publicPoll->userIsWithinTargetGroup(Auth::user()) || $publicPoll->hasEnded()),
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

        $inTargetGroup = $notParticipatedPolls->filter(fn (PublicPoll $publicPoll) => $publicPoll->userIsWithinTargetGroup(Auth::user()));

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
