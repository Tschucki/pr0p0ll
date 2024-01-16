<?php

namespace App\Filament\Resources\MyPollResource\Widgets;

use Illuminate\Support\Facades\Hash;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ApexAnswerChart extends ApexChartWidget
{
    protected static ?string $chartId = 'apexAnswerChart';

    protected static ?string $heading = 'ApexAnswerChart';

    public array $answerData;

    protected function getOptions(): array
    {
        self::$heading = $this->answerData['heading'];
        self::$chartId = $this->answerData['chartId'];
        return $this->answerData['chartOptions'];
    }
}
