<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class NeedsDataReviewWidget extends Widget
{
    protected static string $view = 'filament.widgets.needs-data-review-widget';

    protected int|string|array $columnSpan = 2;

    public static function canView(): bool
    {
        return \Auth::user()?->canUpdateDemographicData();
    }
}
