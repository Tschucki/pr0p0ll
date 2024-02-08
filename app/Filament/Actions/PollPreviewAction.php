<?php

namespace App\Filament\Actions;

use App\Abstracts\Poll;
use App\Services\PollFormService;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Str;

class PollPreviewAction
{
    public static function make($fullPreview = true): Action
    {
        return Action::make('Vorschau anzeigen')->form(function (Model $poll) use ($fullPreview) {
            if (! ($poll instanceof Poll)) {
                Notification::make('invalid_poll_given')->title('Ungültige Umfrage')->body('Die Umfrage konnte nicht aufgelöst werden')->danger()->send();

                return false;
            }
            $preview = [];

            if ($fullPreview) {
                $preview[] = Placeholder::make('Titel')->content($poll->title);
                $preview[] = Placeholder::make('Beschreibung')->content(fn () => new HtmlString('<div class="prose dark:prose-invert">'.Str::markdown($poll->description).'</div>'))->visible(fn () => $poll->description);
            }

            $preview[] = Section::make('Fragen')->schema(function () use ($poll) {
                return (new PollFormService($poll))->buildForm();
            });

            return $preview;
        })->modalHeading('Vorschau')->modalSubmitAction(false);
    }
}
