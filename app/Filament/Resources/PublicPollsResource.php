<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PublicPollsResource\Pages;
use App\Models\Polls\PublicPoll;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
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
                Tables\Columns\TextColumn::make('title')->weight(FontWeight::ExtraBold)->label('Titel')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('description')->label('Beschreibung')->hidden()->searchable()->sortable(),
                Tables\Columns\TextColumn::make('category.title')->label('Kategorie')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('closes_after')->label('Ende')->state(fn (PublicPoll $publicPoll) => $publicPoll->hasEnded() ? 'Geschlossen' : now()->add($publicPoll->closes_after)->diffForHumans())->toggleable(),
                Tables\Columns\TextColumn::make('user.name')->label('Ersteller')->state(fn (PublicPoll $publicPoll) => $publicPoll->not_anonymous ? $publicPoll->user->name : '')->searchable()->sortable(),
                Tables\Columns\IconColumn::make('within_target_group')->label('Innerhalb deiner Zielgruppe')->boolean()->state(fn (PublicPoll $publicPoll) => $publicPoll->userIsWithinTargetGroup(\Auth::user())),
                Tables\Columns\IconColumn::make('participated')->label('Teilgenommen')->boolean()->state(fn (PublicPoll $publicPoll) => $publicPoll->userParticipated(\Auth::user())),
            ])
            ->filters([])
            ->groups([
                Tables\Grouping\Group::make('category.title')->label('Kategorie'),
            ])
            ->actions([
                Tables\Actions\Action::make('participate')
                    ->icon('heroicon-o-plus-circle')
                    ->button()
                    ->label('Teilnehmen')
                    ->url(fn (PublicPoll $publicPoll): string => route('filament.pr0p0ll.resources.public-polls.teilnehmen', ['record' => $publicPoll]))
                    ->hidden(fn (PublicPoll $publicPoll) => $publicPoll->userParticipated(\Auth::user()) || ! $publicPoll->userIsWithinTargetGroup(\Auth::user()) || $publicPoll->hasEnded()),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('in_target_group')
                    ->label('Zielgruppe')
                    ->options([
                        true => 'Innerhalb Zielgruppe',
                        false => 'Außerhalb Zielgruppe',
                    ])
                    ->default(fn (Page $livewire) => $livewire->activeTab === 'offene-umfragen' ? '1' : '0')
                    ->query(function (Builder $query, $data) {
                        if ($data['value'] === '1') {
                            $pollIdsWithinTargetGroup = PublicPoll::all()->filter(fn ($poll) => $poll->userIsWithinTargetGroup(\Auth::user()) === true)->pluck('id');
                            $query->whereIn('id', $pollIdsWithinTargetGroup);
                        }
                        if ($data['value'] === '0') {
                            $pollIdsWithinTargetGroup = PublicPoll::all()->filter(fn ($poll) => $poll->userIsWithinTargetGroup(\Auth::user()) === false)->pluck('id');
                            $query->whereIn('id', $pollIdsWithinTargetGroup);
                        }
                    }),
            ])
            ->bulkActions([])
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
            $query->where('participant_id', \Auth::id());
        })
            ->where('original_content_link', null)
            ->where('visible_to_public', true)
            ->where('approved', true)
            ->where('in_review', false)
            ->where('closes_at', '>', now())
            ->withoutGlobalScope(SoftDeletingScope::class)
            ->get();

        return \Number::abbreviate($notParticipatedPolls->count());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPublicPolls::route('/'),
            'teilnehmen' => Pages\PollParticipation::route('/{record}/teilnehmen'),
        ];
    }
}
