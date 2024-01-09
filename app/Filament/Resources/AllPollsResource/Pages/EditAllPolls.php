<?php

namespace App\Filament\Resources\AllPollsResource\Pages;

use App\Filament\Resources\AllPollsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAllPolls extends EditRecord
{
    protected static string $resource = AllPollsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
