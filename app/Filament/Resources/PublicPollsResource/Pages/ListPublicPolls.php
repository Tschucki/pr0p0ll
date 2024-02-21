<?php

declare(strict_types=1);

namespace App\Filament\Resources\PublicPollsResource\Pages;

use App\Filament\Resources\PublicPollsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPublicPolls extends ListRecords
{
    protected static string $resource = PublicPollsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create_own_poll')->label('Eigene Umfrage erstellen')->url(route('filament.pr0p0ll.resources.my-polls.create')),
        ];
    }
}
