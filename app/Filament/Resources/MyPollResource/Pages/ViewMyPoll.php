<?php

namespace App\Filament\Resources\MyPollResource\Pages;

use App\Filament\Actions\PollPreviewAction;
use App\Filament\Actions\SubmitForReviewAction;
use App\Filament\Resources\MyPollResource;
use App\Models\Polls\MyPoll;
use App\Services\PollFormService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\HtmlString;

class ViewMyPoll extends ViewRecord
{
    protected static string $resource = MyPollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            PollPreviewAction::make(fullPreview: false),
            SubmitForReviewAction::make(),
            ActionGroup::make([
                EditAction::make('edit')->label('Bearbeiten'),
                DeleteAction::make('delete')->label('Löschen')->infolist([TextEntry::make('Info')->state('Wenn du diese Umfrage löschst wird sie für alle Benutzer gelöscht. Du kannst sie nicht wiederherstellen.')])->requiresConfirmation(),
            ])
        ];
    }
}
