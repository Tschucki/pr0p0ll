<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\QuestionType;
use App\Models\AnswerTypes\BoolAnswer;
use App\Models\AnswerTypes\MultipleChoiceAnswer;
use App\Models\AnswerTypes\SingleOptionAnswer;
use App\Models\AnswerTypes\TextAnswer;
use App\Models\Polls\MyPoll;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Seeder;

class PollSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (range(1, 1) as $poll) {
            $myPoll = MyPoll::create([
                'title' => 'My Poll',
                'description' => 'This is my poll',
                'user_id' => 1,
                'closes_after' => '+3 days',
                'published_at' => now(),
                'admin_notes' => 'Eine klasse Umfrage',
                'in_review' => false,
                'approved' => true,
                'visible_to_public' => true,
                'not_anonymous' => true,
            ]);

            $this->createQuestionsForPoll($myPoll);
            $this->createAnswersForPoll($myPoll);
        }
        foreach (range(1, 1) as $poll) {
            $myPoll = MyPoll::create([
                'title' => 'My Poll',
                'description' => 'This is my poll',
                'user_id' => 1,
                'closes_after' => '+3 days',
                'closes_at' => now()->subDay(),
                'published_at' => now()->subDays(4),
                'admin_notes' => 'Eine klasse Umfrage',
                'in_review' => false,
                'approved' => true,
                'visible_to_public' => true,
                'not_anonymous' => true,
            ]);

            $this->createQuestionsForPoll($myPoll);
            $this->createAnswersForPoll($myPoll);
        }
        foreach (range(1, 1) as $poll) {
            $myPoll = MyPoll::create([
                'title' => 'My Poll',
                'description' => 'This is my poll',
                'user_id' => User::inRandomOrder()->first()->getKey(),
                'closes_after' => '+3 days',
                'published_at' => now(),
                'admin_notes' => 'Eine klasse Umfrage',
                'in_review' => false,
                'approved' => true,
                'visible_to_public' => true,
                'not_anonymous' => true,
            ]);

            $this->createQuestionsForPoll($myPoll);
            $this->createAnswersForPoll($myPoll);
        }
    }

    private function createQuestionsForPoll(MyPoll $myPoll): void
    {
        foreach (range(1, 2) as $index) {
            $this->createSingleOptionQuestionForPoll($myPoll);
            $this->createBoolQuestionForPoll($myPoll);
            $this->createMultipleOptionQuestionForPoll($myPoll);
            $this->createSingleOptionQuestionForPoll($myPoll);
            $this->createBoolQuestionForPoll($myPoll);
            $this->createMultipleOptionQuestionForPoll($myPoll);
            $this->createTextQuestionForPoll($myPoll);
        }
    }

    private function createBoolQuestionForPoll(MyPoll $myPoll): void
    {
        $myPoll->questions()->create([
            'title' => 'Bool Question',
            'question_type_id' => \App\Models\QuestionType::where('component', QuestionType::TOGGLE)->firstOrFail()->getKey(),
            'options' => null,
            'description' => 'This is a bool question',
            'position' => 1,
        ]);
    }

    private function createSingleOptionQuestionForPoll(MyPoll $myPoll): void
    {
        $myPoll->questions()->create([
            'title' => 'Single Option Question',
            'question_type_id' => \App\Models\QuestionType::where('component', QuestionType::SINGLE)->firstOrFail()->getKey(),
            'options' => [['title' => 'Option 1'], ['title' => 'Option 2'], ['title' => 'Option 3']],
            'description' => 'This is a single option question',
            'position' => 1,
        ]);
    }

    private function createMultipleOptionQuestionForPoll(MyPoll $myPoll): void
    {
        $myPoll->questions()->create([
            'title' => 'Multiple Option Question',
            'question_type_id' => \App\Models\QuestionType::where('component', QuestionType::MULTIPLE)->firstOrFail()->getKey(),
            'options' => [['title' => 'Option 1'], ['title' => 'Option 2'], ['title' => 'Option 3']],
            'description' => 'This is a multiple option question',
            'position' => 1,
        ]);
    }

    private function createTextQuestionForPoll(MyPoll $myPoll): void
    {
        $myPoll->questions()->create([
            'title' => 'Text Question',
            'question_type_id' => \App\Models\QuestionType::where('component', QuestionType::TEXT)->firstOrFail()->getKey(),
            'position' => 1,
        ]);
    }

    private function createAnswersForPoll(MyPoll $myPoll): void
    {
        $myPoll->questions->each(function (Question $question) use ($myPoll) {
            if ($question->answerType() instanceof SingleOptionAnswer) {
                $this->createSingleOptionAnswersForQuestion($question, $myPoll);
            } elseif ($question->answerType() instanceof MultipleChoiceAnswer) {
                $this->createMultipleOptionAnswersForQuestion($question, $myPoll);
            } elseif ($question->answerType() instanceof BoolAnswer) {
                $this->createBoolAnswersForQuestion($question, $myPoll);
            } elseif ($question->answerType() instanceof TextAnswer) {
                $this->createTextAnswersForQuestion($question, $myPoll);
            }
        });
    }

    private function createTextAnswersForQuestion(Question $question, MyPoll $myPoll): void
    {

        foreach (range(1, 10) as $i) {
            $user = User::inRandomOrder()->first();
            $anonymousUser = $user->createAnonymousUser();
            $anonymousUserId = $anonymousUser->getKey();
            $question->answers()->create([
                'answerable_id' => $question->answerType()->create([
                    'answer_value' => 'AnswerText '.random_int(1, 3),
                ])->getKey(),
                'answerable_type' => get_class($question->answerType()),
                'user_id' => null,
                'poll_id' => $myPoll->getKey(),
                'anonymous_user_id' => $anonymousUserId,
            ]);
        }
        $myPoll->participants()->attach($user->getKey());
    }

    private function createSingleOptionAnswersForQuestion(Question $question, MyPoll $myPoll): void
    {

        foreach (range(1, 10) as $i) {
            $user = User::inRandomOrder()->first();
            $anonymousUser = $user->createAnonymousUser();
            $anonymousUserId = $anonymousUser->getKey();
            $question->answers()->create([
                'answerable_id' => $question->answerType()->create([
                    'answer_value' => 'Option '.random_int(1, 3),
                ])->getKey(),
                'answerable_type' => get_class($question->answerType()),
                'user_id' => null,
                'poll_id' => $myPoll->getKey(),
                'anonymous_user_id' => $anonymousUserId,
            ]);
        }
        $myPoll->participants()->attach($user->getKey());
    }

    private function createMultipleOptionAnswersForQuestion(Question $question, MyPoll $myPoll): void
    {

        foreach (range(1, 10) as $i) {
            $user = User::inRandomOrder()->first();
            $anonymousUser = $user->createAnonymousUser();
            $anonymousUserId = $anonymousUser->getKey();
            $question->answers()->create([
                'answerable_id' => $question->answerType()->create([
                    'answer_value' => 'Option '.random_int(1, 3),
                ])->getKey(),
                'answerable_type' => get_class($question->answerType()),
                'user_id' => null,
                'poll_id' => $myPoll->getKey(),
                'anonymous_user_id' => $anonymousUserId,
            ]);
        }
        $myPoll->participants()->attach($user->getKey());
    }

    private function createBoolAnswersForQuestion(Question $question, MyPoll $myPoll): void
    {

        foreach (range(1, 10) as $i) {
            $user = User::inRandomOrder()->first();
            $anonymousUser = $user->createAnonymousUser();
            $anonymousUserId = $anonymousUser->getKey();
            $question->answers()->create([
                'answerable_id' => $question->answerType()->create([
                    'answer_value' => random_int(0, 1) === 1,
                ])->getKey(),
                'answerable_type' => get_class($question->answerType()),
                'user_id' => null,
                'poll_id' => $myPoll->getKey(),
                'anonymous_user_id' => $anonymousUserId,
            ]);
        }
        $myPoll->participants()->attach($user->getKey());
    }
}
