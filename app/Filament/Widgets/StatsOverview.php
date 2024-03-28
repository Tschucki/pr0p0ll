<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Answer;
use App\Models\Polls\Poll;
use App\Models\Question;
use App\Models\QuestionType;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
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

        $answerTypeCounts = QuestionType::where('disabled', false)->get()->map(function (QuestionType $type) {

            $cacheKey = $type->title.'answers_count';
            if (\Cache::has($cacheKey)) {
                $count = \Cache::get($cacheKey);
            } else {
                $count = Answer::where('answerable_type', $type->answerType()->getMorphClass())->count();
                \Cache::put($cacheKey, $count, now()->addHours(12));
            }

            return Stat::make('Antworten '.$type->title, Number::format(number: $count, precision: 0, locale: 'de'));
        });

        try {
            $visitors = (int) Plausible::realtime();

            $aggregates = Plausible::aggregates(
                period: 'day',
                metrics: ['visitors'],
            );
            $todayVisitors = $aggregates['visitors']['value'];
        } catch (\Exception $e) {
            Notification::make('plausible_error')->danger()->title('Plausible Fehler')->body('Es gab einen Fehler beim Abrufen der Besucherzahlen.')->send();
            $visitors = 0;
            $todayVisitors = 0;
        }

        $trends = [];
        $trends['polls'] = Trend::query(Poll::where('approved', true))
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->count()
            ->pluck('aggregate')
            ->toArray();

        $trends['users'] = Trend::model(User::class)
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->count()
            ->pluck('aggregate')
            ->toArray();

        $trends['answers'] = Trend::model(Answer::class)
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->count()
            ->pluck('aggregate')
            ->toArray();

        return [
            Stat::make('Umfragen', Number::format(number: Poll::where('approved', true)->count(), precision: 0, locale: 'de'))->chart($trends['polls'])->chartColor('primary'),
            Stat::make('Benutzer', Number::format(number: User::count(), precision: 0, locale: 'de'))->chart($trends['users'])->chartColor('primary'),
            Stat::make('Antworten', Number::format(number: $answers, precision: 0, locale: 'de'))->chart($trends['answers'])->chartColor('primary'),
            Stat::make('Fragen', Number::format(number: $questions, precision: 0, locale: 'de')),
            Stat::make('Aktuelle Besucher', Number::format(number: $visitors, precision: 0, locale: 'de')),
            Stat::make('Heutige Besucher', Number::format(number: $todayVisitors, precision: 0, locale: 'de')),
            ...$answerTypeCounts,
            Stat::make('Noch eine Idee?', 'Schreib mir!')->url('https://pr0gramm.com/inbox/messages/PimmelmannJones')->openUrlInNewTab(),
        ];
    }
}
