<?php

namespace App\Filament\Resources\MyPollResource\Pages;

use App\Filament\Resources\MyPollResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMyPolls extends ListRecords
{
    protected static string $resource = MyPollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
