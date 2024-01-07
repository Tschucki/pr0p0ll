<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    protected $guarded = [];

    protected $casts = [
        'visible_to_public' => 'boolean',
        'in_review' => 'boolean',
        'approved' => 'boolean',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function questions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Question::class, 'poll_id');
    }

    public function getBuilderData(): array
    {
        return $this->questions->map(function (Question $question) {
            $type = $question->questionType;
            return [
                'id' => $question->getKey(),
                'type' => (string)($type->getKey()),
                'data' => [
                    'question_type_id' => $type->getKey(),
                    'title' => $question->title,
                    'hint' => $question->hint,
                    'options' => $question->options,
                ],
            ];
        })->toArray();
    }
}
