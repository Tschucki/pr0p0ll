<?php

declare(strict_types=1);

namespace App\Filament\Resources\MyPolls\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\MyPolls\MyPollResource;
use Filament\Actions;
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
