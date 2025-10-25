<?php

declare(strict_types=1);

namespace App\Filament\Resources\AllPolls\Pages;

use App\Filament\Resources\AllPolls\AllPollsResource;
use Filament\Resources\Pages\ListRecords;

class ListAllPolls extends ListRecords
{
    protected static string $resource = AllPollsResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
