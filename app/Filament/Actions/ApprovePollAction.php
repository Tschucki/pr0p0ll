<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Models\Polls\Poll;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ApprovePollAction
{
    public static function make(): Action
    {
        return Action::make('Genehmigen')->action(function (Poll $record) {
            $record->approve();
            Notification::make('approve')->success()->title('Genehmigt')->body('Umfrage wurde genehmigt und ist öffentlich')->send();
        })->visible(fn (Poll $record) => ! $record->isApproved() && $record->isInReview())->color('success')->requiresConfirmation();
    }
}
