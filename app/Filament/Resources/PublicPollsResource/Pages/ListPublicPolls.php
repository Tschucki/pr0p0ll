<?php

declare(strict_types=1);

namespace App\Filament\Resources\PublicPollsResource\Pages;

use App\Filament\Resources\PublicPollsResource;
use App\Models\Polls\PublicPoll;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ListPublicPolls extends ListRecords
{
    protected static string $resource = PublicPollsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create_own_poll')->label('Eigene Umfrage erstellen')->url(route('filament.pr0p0ll.resources.my-polls.create')),
        ];
    }

    public function getTabs(): array
    {
        return [
            'offene-umfragen' => Tab::make('Offen')->modifyQueryUsing(function (Builder $query) {
                $query->whereDoesntHave('participants', function (Builder $query) {
                    $query->where('participant_id', auth()->id());
                })->where('closes_at', '>', now())->orderBy('closes_at', 'DESC');
            })->badge(function () {
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
            }),

            'teilgenommen' => Tab::make('Teilgenommen')->modifyQueryUsing(function (Builder $query) {
                $query->whereHas('participants', function (Builder $query) {
                    $query->where('participant_id', auth()->id());
                });
            })->badge(fn () => PublicPoll::whereHas('participants', static function (Builder $query) {
                $query->where('participant_id', auth()->id());
            })->count()),

            'alle' => Tab::make('Alle')->modifyQueryUsing(function (Builder $query) {
                $query->orderBy('published_at', 'desc');
            })->badge(fn () => \Number::abbreviate(PublicPoll::count())),
        ];
    }
}
