<?php

declare(strict_types=1);

namespace App\Filament\Resources\MyPollResource\Pages;

use App\Filament\Resources\MyPollResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMyPolls extends ListRecords
{
    protected static string $resource = MyPollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
