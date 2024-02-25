<?php

declare(strict_types=1);

namespace App\Filament\Resources\MyPollResource\Widgets;

use Filament\Support\RawJs;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ApexAnswerChart extends ApexChartWidget
{
    protected static ?string $chartId = 'apexAnswerChart';

    protected static ?string $heading = '';

    public array $answerData;

    public ?string $subHeading = null;

    public ?string $color = '#ee4d2e';

    protected function getOptions(): array
    {
        self::$footer = $this->answerData['footerText'];

        if ($this->answerData['heading']) {
            self::$heading = $this->answerData['heading'];
        }

        if ($this->subHeading) {
            self::$subheading = $this->subHeading;
        }
        self::$chartId = $this->answerData['chartId'];

        return $this->answerData['chartOptions'];
    }

    protected function extraJsOptions(): ?RawJs
    {
        if ($this->answerData['chartOptions']['chart']['type'] === 'pie') {
            return RawJs::make(
                <<<'JS'
    {
        dataLabels: {
            enabled: true,
            formatter: function(val, opts) {
            return val + '% ' + '(' + opts.w.config.series[opts.seriesIndex]  +')';
            },
            dropShadow: {
                enabled: true
            },
        }
    }
    JS
            );
        }

        return RawJs::make(
            <<<'JS'
    {
        dataLabels: {
            enabled: true,
            formatter: function(val, opts) {
            var absoluteValue = Math.abs(val);
            var total = opts.w.globals.seriesTotals[opts.seriesIndex];
            var percentage = ((absoluteValue / total) * 100).toFixed(2);
            return absoluteValue + ' (' + percentage + '%)';
            },
            dropShadow: {
                enabled: true
            },
        }
    }
    JS
        );
    }
}
