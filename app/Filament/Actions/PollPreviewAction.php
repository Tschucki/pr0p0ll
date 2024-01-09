<?php

namespace App\Filament\Actions;

use App\Models\Polls\MyPoll;
use App\Services\PollFormService;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Illuminate\Support\HtmlString;
use Str;

class PollPreviewAction
{
    public static function make($fullPreview = true): Action
    {
        return Action::make('Vorschau anzeigen')->form(function (MyPoll $poll) use ($fullPreview) {
            $preview = [];

            if ($fullPreview) {
                $preview[] = Placeholder::make('Titel')->content($poll->title);
                $preview[] = Placeholder::make('Beschreibung')->content(new HtmlString('<div class="prose dark:prose-invert">' . Str::markdown($poll->description) . '</div>'));
            }

            $preview[] = Section::make('Fragen')->schema(function () use ($poll) {
                return (new PollFormService($poll))->buildForm();
            });

            return $preview;
        })->modalHeading('Vorschau')->modalSubmitAction(false);
    }
}
