<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Models\Polls\MyPoll;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;

class SubmitForReviewAction
{
    public static function make(): Action
    {
        return Action::make('submit_for_review')->label('Zur Prüfung freigeben')->infolist([TextEntry::make('Info')->state('Sobald deine Umfrage in der Prüfung ist werden wir sie überprüfen und entscheiden ob Sie in Ordnung ist oder nicht. Ist die Umfrage genehmigt wird sie automatisch für alle Benutzer verfügbar sein.')])->action(function (MyPoll $myPoll) {
            $myPoll->update([
                'in_review' => true,
            ]);
            Notification::make('submitted')->title('Umfrage wurde zur Prüfung freigegeben')->body('Deine Umfrage wurde zur Prüfung freigegeben.')->send();
        })->requiresConfirmation()->hidden(fn (MyPoll $myPoll) => $myPoll->isInReview() || $myPoll->isApproved());
    }
}
