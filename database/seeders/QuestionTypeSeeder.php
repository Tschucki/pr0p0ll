<?php

namespace Database\Seeders;

use App\Models\AnswerTypes\BoolAnswer;
use App\Models\AnswerTypes\ColorAnswer;
use App\Models\AnswerTypes\DateAnswer;
use App\Models\AnswerTypes\DateTimeAnswer;
use App\Models\AnswerTypes\MultipleChoiceAnswer;
use App\Models\AnswerTypes\SingleOptionAnswer;
use App\Models\AnswerTypes\TextAnswer;
use App\Models\AnswerTypes\TimeAnswer;
use App\Models\QuestionType;
use Illuminate\Database\Seeder;

class QuestionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $questionTypes = [
            [
                'title' => 'Einzelauswahl',
                'description' => 'Eine Frage mit einer Einzelauswahl Antwort',
                'component' => 'radio',
                'answer_type' => SingleOptionAnswer::class,
                'icon' => 'heroicon-o-chevron-up-down',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Mehrfachauswahl',
                'description' => 'Eine Frage mit mehreren Antworten',
                'component' => 'checkbox-list',
                'answer_type' => MultipleChoiceAnswer::class,
                'icon' => 'heroicon-o-list-bullet',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Ja - Nein',
                'description' => 'Eine Frage mit einer Ja oder Nein Antwort',
                'component' => 'toggle',
                'answer_type' => BoolAnswer::class,
                'icon' => 'heroicon-o-check-circle',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Freitext',
                'description' => 'Eine Frage mit einer Freitext Antwort',
                'component' => 'text',
                'answer_type' => TextAnswer::class,
                'icon' => 'heroicon-o-pencil',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Datum',
                'description' => 'Eine Frage mit einer Datum Antwort',
                'component' => 'date',
                'answer_type' => DateAnswer::class,
                'icon' => 'heroicon-o-calendar',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Datum und Uhrzeit',
                'description' => 'Eine Frage mit einer Datum und Uhrzeit Antwort',
                'component' => 'datetime',
                'answer_type' => DateTimeAnswer::class,
                'icon' => 'heroicon-o-calendar',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Uhrzeit',
                'description' => 'Eine Frage mit einer Uhrzeit Antwort',
                'component' => 'time',
                'answer_type' => TimeAnswer::class,
                'icon' => 'heroicon-o-clock',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Farbe',
                'description' => 'Eine Frage mit einer Farbe-Antwort',
                'component' => 'color',
                'answer_type' => ColorAnswer::class,
                'icon' => 'heroicon-o-swatch',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        QuestionType::insert($questionTypes);
    }
}
