<?php

namespace App\Filament\Resources\MyPollResource\Pages;

use App\Filament\Actions\PollPreviewAction;
use App\Filament\Actions\SubmitForReviewAction;
use App\Filament\Resources\MyPollResource;
use App\Filament\Resources\MyPollResource\Widgets\ParticipationCountWidget;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;

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
            ]),
        ];
    }
}
