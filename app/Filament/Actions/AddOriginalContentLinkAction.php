<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Models\Polls\MyPoll;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

class AddOriginalContentLinkAction
{
    public static function make(): Action
    {
        return Action::make('add_original_content')->form([
            TextInput::make('original_content_link')->label('Original Content Link')->required()->url()->activeUrl()->startsWith('https://pr0gramm.com')->placeholder('https://pr0gramm.com/new/6092537'),
        ])->action(function (MyPoll $poll, array $data) {
            $link = $data['original_content_link'];
            $poll->update([
                'original_content_link' => $link,
            ]);
            //TODO Send Notifications

            Notification::make('original_content_added')->success()->title('Post hinzugefügt')->body('Danke fürs hinzufügen. Benachrichtigungen werden in Kürze versendet.')->send();
        })->visible(fn (MyPoll $poll) => $poll->isApproved() && ! $poll->isInReview() && $poll->hasEnded() && $poll->original_content_link === null)->label('pr0-Post hinzufügen')->color('primary')->requiresConfirmation();
    }
}
