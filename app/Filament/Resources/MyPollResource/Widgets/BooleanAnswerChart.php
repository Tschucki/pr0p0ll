<?php

namespace App\Filament\Resources\MyPollResource\Widgets;

use Filament\Widgets\ChartWidget;

class BooleanAnswerChart extends ChartWidget
{
    protected static ?string $heading = 'Chart';

    public array $answerData;

    public function getData(): array
    {
        self::$heading = $this->answerData['heading'];

        return $this->answerData['chartData'];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
