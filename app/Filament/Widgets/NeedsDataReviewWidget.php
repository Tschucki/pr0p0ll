<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class NeedsDataReviewWidget extends Widget
{
    protected static string $view = 'filament.widgets.needs-data-review-widget';

    protected int | string | array $columnSpan = 2;
}
