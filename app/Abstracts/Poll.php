<?php

namespace App\Abstracts;

use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

abstract class Poll extends Model
{
    protected $guarded = [];

    protected $table = 'polls';

    protected $casts = [
        'published_at' => 'datetime',
        'visible_to_public' => 'boolean',
        'in_review' => 'boolean',
        'approved' => 'boolean',
        'not_anonymous' => 'boolean',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function questions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Question::class, 'poll_id');
    }

    public function answers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Answer::class, 'poll_id');
    }

    public function getBuilderData(): array
    {
        return $this->questions->map(function (Question $question) {
            $type = $question->questionType;

            return [
                'id' => $question->getKey(),
                'type' => (string) ($type->getKey()),
                'data' => [
                    'question_type_id' => $type->getKey(),
                    'title' => $question->title,
                    'hint' => $question->hint,
                    'options' => $question->options,
                ],
            ];
        })->toArray();
    }

    public function isInReview(): bool
    {
        return (bool) $this->in_review;
    }

    public function isApproved(): bool
    {
        return (bool) $this->approved;
    }
}
