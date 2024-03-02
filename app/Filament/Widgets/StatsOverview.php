<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Answer;
use App\Models\Polls\Poll;
use App\Models\Question;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;
use NjoguAmos\Plausible\Facades\Plausible;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        if (\Cache::has('answers_count')) {
            $answers = \Cache::get('answers_count');
        } else {
            $answers = Answer::count();
            \Cache::put('answers_count', $answers, now()->addHours(12));
        }

        if (\Cache::has('questions_count')) {
            $questions = \Cache::get('questions_count');
        } else {
            $questions = Question::count();
            \Cache::put('questions_count', $questions, now()->addHours(12));
        }

        $visitors = (int) Plausible::realtime();

        $aggregates = Plausible::aggregates(
            period: 'day',
            metrics: ['visitors'],
        );
        $todayVisitors = $aggregates['visitors']['value'];

        return [
            Stat::make('Umfragen', Number::abbreviate(Poll::count())),
            Stat::make('Benutzer', Number::abbreviate(User::count())),
            Stat::make('Antworten', Number::abbreviate($answers)),
            Stat::make('Fragen', Number::abbreviate($questions)),
            Stat::make('Aktuelle Besucher', Number::abbreviate($visitors)),
            Stat::make('Heutige Besucher', Number::abbreviate($todayVisitors)),
        ];
    }
}
