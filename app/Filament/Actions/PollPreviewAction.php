<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Models\Abstracts\Poll;
use App\Services\PollFormService;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class PollPreviewAction
{
    public static function make($fullPreview = true): Action
    {
        return Action::make('Vorschau anzeigen')->schema(function (Model $record) use ($fullPreview) {
            if (! ($record instanceof Poll)) {
                Notification::make('invalid_poll_given')->title('Ungültige Umfrage')->body('Die Umfrage konnte nicht aufgelöst werden')->danger()->send();

                return false;
            }
            $preview = [];

            if ($fullPreview) {
                $preview[] = Placeholder::make('Titel')->content($record->title);
                $preview[] = Placeholder::make('Beschreibung')->content(fn () => new HtmlString('<div class="prose dark:prose-invert">'.$record->description.'</div>'))->visible(fn () => (bool) $record->description);
            }

            $preview[] = Section::make('Fragen')->schema(function () use ($record) {
                return (new PollFormService($record))->buildForm();
            });

            return $preview;
        })->modalHeading('Vorschau')->modalSubmitAction(false);
    }
}
