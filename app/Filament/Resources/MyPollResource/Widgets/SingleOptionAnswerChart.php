<?php

namespace App\Filament\Resources\MyPollResource\Widgets;

use Filament\Widgets\ChartWidget;

class SingleOptionAnswerChart extends ChartWidget
{
    protected static ?string $heading = 'Chart';

    public function getData(): array
    {
        self::$heading = $this->answerData['heading'];

        return $this->answerData['chartData'];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
