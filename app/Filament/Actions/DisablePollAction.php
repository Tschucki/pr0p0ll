<?php

namespace App\Filament\Actions;

use App\Models\Polls\Poll;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

class DisablePollAction
{
    public static function make(): Action
    {
        return Action::make('Deaktivieren')->form([
            Textarea::make('reason')->required()->placeholder('Grund für die Deaktivierung')->label('Grund'),
        ])->color('danger')->action(function (Poll $poll, array $data) {
            $poll->disable($data['reason']);
            Notification::make('approve')->success()->title('Deaktiviert')->body('Umfrage wurde deaktiviert und ist nicht mehr öffentlich')->send();
        })->visible(fn (Poll $poll) => $poll->isApproved())->requiresConfirmation('Bist du sicher, dass du diese Umfrage deaktivieren möchtest?');
    }
}
