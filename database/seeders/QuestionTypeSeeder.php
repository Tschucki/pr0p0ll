<?php

namespace Database\Seeders;

use App\Enums\AnswerType;
use App\Models\QuestionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
                'icon' => 'heroicon-o-chevron-up-down',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Mehrfachauswahl',
                'description' => 'Eine Frage mit mehreren Antworten',
                'component' => 'checkbox-list',
                'icon' => 'heroicon-o-list-bullet',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Ja - Nein',
                'description' => 'Eine Frage mit einer Ja oder Nein Antwort',
                'component' => 'toggle',
                'icon' => 'heroicon-o-check-circle',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Freitext',
                'description' => 'Eine Frage mit einer Freitext Antwort',
                'component' => 'text',
                'icon' => 'heroicon-o-pencil',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Datum',
                'description' => 'Eine Frage mit einer Datum Antwort',
                'component' => 'date',
                'icon' => 'heroicon-o-calendar',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Datum und Uhrzeit',
                'description' => 'Eine Frage mit einer Datum und Uhrzeit Antwort',
                'component' => 'datetime',
                'icon' => 'heroicon-o-calendar',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Uhrzeit',
                'description' => 'Eine Frage mit einer Uhrzeit Antwort',
                'component' => 'time',
                'icon' => 'heroicon-o-clock',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Farbe',
                'description' => 'Eine Frage mit einer Farbe-Antwort',
                'component' => 'color',
                'icon' => 'heroicon-o-swatch',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        QuestionType::insert($questionTypes);
    }
}
