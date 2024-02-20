<?php

declare(strict_types=1);

namespace App\Filament\Resources\AllPollsResource\Pages;

use App\Filament\Actions\ApprovePollAction;
use App\Filament\Actions\DenyPollAction;
use App\Filament\Actions\DisablePollAction;
use App\Filament\Actions\PollPreviewAction;
use App\Filament\Resources\AllPollsResource;
use Filament\Resources\Pages\ViewRecord;

class ViewAllPolls extends ViewRecord
{
    protected static string $resource = AllPollsResource::class;

    protected function getActions(): array
    {
        return [
            PollPreviewAction::make(),
            DenyPollAction::make(),
            ApprovePollAction::make(),
            DisablePollAction::make(),
        ];
    }
}
