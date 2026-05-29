<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Abstracts\Poll;
use App\Models\AnswerTypes\BoolAnswer;
use App\Models\AnswerTypes\MultipleChoiceAnswer;
use App\Models\AnswerTypes\SingleOptionAnswer;
use App\Models\AnswerTypes\TextAnswer;
use App\Models\Question;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

// Persistierte, stets vollständige Auswertungs-Post-Konfiguration eines Polls.
class ResultPostConfig
{
    public const string CHART_BAR = 'bar';

    public const string CHART_DONUT = 'donut';

    public function __construct(
        public string $title,
        public ?string $description,
        public string $color,
        public bool $showDemographics,
        public array $aQuestions,
        public ?string $tags = null,
        public ?string $comment = null,
    ) {}

    public static function default(Poll $poll): self
    {
        $aQuestions = [];
        foreach ($poll->questions as $question) {
            $aQuestions[$question->getKey()] = self::defaultQuestionConfig($question);
        }

        return new self(
            title: (string) $poll->title,
            description: $poll->description,
            color: '#ee4d2e',
            showDemographics: true,
            aQuestions: $aQuestions,
            tags: null,
            comment: null,
        );
    }

    // Lädt gespeicherte Config und füllt fehlende/neue Fragen gegen den Default auf.
    public static function fromArray(?array $aStored, Poll $poll): self
    {
        $default = self::default($poll);

        if (empty($aStored)) {
            return $default;
        }

        $aQuestions = [];
        foreach ($poll->questions as $question) {
            $key = $question->getKey();
            $aDefault = $default->aQuestions[$key];
            $aSaved = $aStored['questions'][$key] ?? [];
            $aQuestions[$key] = [
                'display' => (bool) ($aSaved['display'] ?? $aDefault['display']),
                'title' => (string) ($aSaved['title'] ?? $aDefault['title']),
                'description' => $aSaved['description'] ?? $aDefault['description'],
                'chart' => self::sanitizeChart($aSaved['chart'] ?? $aDefault['chart'], $question),
                'answers' => self::normalizeAnswers($aSaved['answers'] ?? [], $aDefault['answers']),
            ];
        }

        return new self(
            title: (string) ($aStored['title'] ?? $default->title),
            description: $aStored['description'] ?? $default->description,
            color: (string) ($aStored['color'] ?? $default->color),
            showDemographics: (bool) ($aStored['showDemographics'] ?? $default->showDemographics),
            aQuestions: $aQuestions,
            tags: self::blankToNull($aStored['tags'] ?? null),
            comment: self::blankToNull($aStored['comment'] ?? null),
        );
    }

    // Übersetzt den flachen Filament-Form-State des Pr0PostCreator in eine Config.
    public static function fromFlatForm(array $aForm, Poll $poll): self
    {
        $aQuestions = [];
        foreach ($poll->questions as $question) {
            $key = $question->getKey();
            $aDefault = self::defaultQuestionConfig($question);

            $aAnswers = [];
            foreach ($aDefault['answers'] as $answerId => $value) {
                $aAnswers[$answerId] = (bool) ($aForm['answer_'.$answerId] ?? true);
            }

            $aQuestions[$key] = [
                'display' => (bool) ($aForm['display_'.$key] ?? $aDefault['display']),
                'title' => (string) ($aForm['title_'.$key] ?? $aDefault['title']),
                'description' => $aForm['description_'.$key] ?? $aDefault['description'],
                'chart' => self::sanitizeChart($aForm['chart_'.$key] ?? $aDefault['chart'], $question),
                'answers' => $aAnswers,
            ];
        }

        return new self(
            title: (string) ($aForm['title'] ?? $poll->title),
            description: $aForm['description'] ?? null,
            color: (string) ($aForm['color'] ?? '#ee4d2e'),
            showDemographics: (bool) ($aForm['show_demographics'] ?? true),
            aQuestions: $aQuestions,
            tags: self::blankToNull($aForm['tags'] ?? null),
            comment: self::blankToNull($aForm['comment'] ?? null),
        );
    }

    // Flacher State zum Befüllen des Pr0PostCreator-Formulars.
    public function toFlatForm(): array
    {
        $aForm = [
            'color' => $this->color,
            'title' => $this->title,
            'description' => $this->description,
            'show_demographics' => $this->showDemographics,
            'tags' => $this->tags ?? '',
            'comment' => $this->comment ?? '',
        ];

        foreach ($this->aQuestions as $questionId => $aQuestion) {
            $aForm['display_'.$questionId] = $aQuestion['display'];
            $aForm['title_'.$questionId] = $aQuestion['title'];
            $aForm['description_'.$questionId] = $aQuestion['description'];
            $aForm['chart_'.$questionId] = $aQuestion['chart'];
            foreach ($aQuestion['answers'] as $answerId => $value) {
                $aForm['answer_'.$answerId] = $value;
            }
        }

        return $aForm;
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'color' => $this->color,
            'showDemographics' => $this->showDemographics,
            'questions' => $this->aQuestions,
            'tags' => $this->tags,
            'comment' => $this->comment,
        ];
    }

    public static function titleTag(Poll $poll): string
    {
        return trim(str_replace(',', ' ', (string) $poll->title));
    }

    public static function defaultTags(Poll $poll): string
    {
        $titleTag = self::titleTag($poll);

        return 'pr0p0ll,Umfrage,Auswertung'.($titleTag !== '' ? ','.$titleTag : '').',Automatischer Post,API';
    }

    public static function defaultComment(Poll $poll): string
    {
        $link = route('filament.pr0p0ll.resources.umfragen.results', ['record' => $poll->getKey()]);
        $title = (string) Str::of((string) $poll->title)->trim();
        $author = $poll->user?->name;
        $credit = $author !== null && $author !== '' ? ' von @'.$author : '';

        return $title.$credit.' — alle Ergebnisse zur Auswertung: '.$link
            ."\n\nBei Fragen und Anregungen bitte @PimmelmannJones schreiben";
    }

    private static function blankToNull(?string $value): ?string
    {
        $value = $value === null ? null : trim($value);

        return ($value === null || $value === '') ? null : $value;
    }

    public function questionConfig(int $questionId): array
    {
        return $this->aQuestions[$questionId] ?? [
            'display' => true,
            'title' => '',
            'description' => null,
            'chart' => self::CHART_BAR,
            'answers' => [],
        ];
    }

    public function wantsAnswer(int $questionId, int $answerId): bool
    {
        return $this->aQuestions[$questionId]['answers'][$answerId] ?? true;
    }

    private static function defaultQuestionConfig(Question $question): array
    {
        $aAnswers = [];
        if ($question->answerType() instanceof TextAnswer) {
            foreach ($question->answers as $answer) {
                $aAnswers[$answer->getKey()] = true;
            }
        }

        return [
            'display' => true,
            'title' => (string) $question->title,
            'description' => $question->description,
            'chart' => self::defaultChart($question),
            'answers' => $aAnswers,
        ];
    }

    private static function defaultChart(Question $question): string
    {
        return $question->answerType() instanceof BoolAnswer ? self::CHART_DONUT : self::CHART_BAR;
    }

    // Nur option-/bool-artige Fragen dürfen zwischen bar/donut wechseln.
    private static function sanitizeChart(string $chart, Question $question): string
    {
        $type = $question->answerType();
        $switchable = $type instanceof SingleOptionAnswer
            || $type instanceof MultipleChoiceAnswer
            || $type instanceof BoolAnswer;

        if (! $switchable) {
            return self::defaultChart($question);
        }

        return in_array($chart, [self::CHART_BAR, self::CHART_DONUT], true) ? $chart : self::defaultChart($question);
    }

    private static function normalizeAnswers(array $aSaved, array $aDefault): array
    {
        $aAnswers = [];
        foreach ($aDefault as $answerId => $value) {
            $aAnswers[$answerId] = (bool) Arr::get($aSaved, (string) $answerId, $value);
        }

        return $aAnswers;
    }
}
