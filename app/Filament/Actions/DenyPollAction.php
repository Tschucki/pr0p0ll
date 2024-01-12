<?php

namespace App\Filament\Actions;

use App\Models\Polls\Poll;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

class DenyPollAction
{
    public static function make(): Action
    {
        return Action::make('Ablehnen')->form([
            Textarea::make('reason')->required()->placeholder('Grund für die Ablehnung')->label('Grund'),
        ])->color('danger')->action(function (Poll $poll, array $data) {
            $poll->deny($data['reason']);
            Notification::make('approve')->success()->title('Abgelehnt')->body('Umfrage wurde abgelehnt')->send();
        })->visible(fn (Poll $poll) => ! $poll->isApproved() && $poll->isInReview())->requiresConfirmation('Bist du sicher, dass du diese Umfrage genehmigen möchtest?');
    }
}
