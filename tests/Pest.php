<?php

declare(strict_types=1);
use App\Models\AnonymousUser;
use App\Models\Answer;
use App\Models\Polls\MyPoll;
use App\Models\Question;
use App\Models\QuestionType;
use App\Models\User;
use Database\Seeders\QuestionTypeSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\DuskTestCase;
use Tests\TestCase;

uses(
    DuskTestCase::class,
    DatabaseMigrations::class,
)->in('Browser');

uses(
    TestCase::class,
    RefreshDatabase::class,
)->in('Feature');

uses(
    TestCase::class,
    RefreshDatabase::class,
)->in('Unit');

// Helpers für Auswertungs-Tests.
function ensureQuestionTypes(): void
{
    if (QuestionType::count() === 0) {
        (new QuestionTypeSeeder)->run();
    }
}

function makeClosedPoll(?User $owner = null, bool $resultsPublic = true): MyPoll
{
    ensureQuestionTypes();
    $owner ??= User::factory()->create();

    return MyPoll::create([
        'title' => 'Test Umfrage',
        'description' => 'Beschreibung',
        'user_id' => $owner->getKey(),
        'closes_after' => '+3 days',
        'published_at' => now()->subWeeks(4),
        'closes_at' => $resultsPublic ? now()->subWeeks(3) : now()->subDay(),
        'in_review' => false,
        'approved' => true,
        'visible_to_public' => true,
        'not_anonymous' => false,
    ]);
}

function addQuestion(MyPoll $poll, string $component, array $aOptions = []): Question
{
    $type = QuestionType::where('component', $component)->firstOrFail();

    return $poll->questions()->create([
        'title' => 'Frage '.$component,
        'question_type_id' => $type->getKey(),
        'options' => $aOptions ?: null,
        'description' => null,
        'position' => 1,
    ]);
}

function makeAnon(array $demo = []): AnonymousUser
{
    return AnonymousUser::create(array_merge([
        'gender' => 'M',
        'nationality' => 'germany',
        'region' => 'bavaria',
        'birthday' => now()->subYears(25),
    ], $demo));
}

function addAnswer(Question $question, mixed $value, ?AnonymousUser $anon = null): Answer
{
    $anon ??= makeAnon();
    $answerType = $question->answerType()->create(['answer_value' => $value]);

    return $question->answers()->create([
        'answerable_id' => $answerType->getKey(),
        'answerable_type' => get_class($question->answerType()),
        'user_id' => null,
        'poll_id' => $question->poll_id,
        'anonymous_user_id' => $anon->getKey(),
    ]);
}
