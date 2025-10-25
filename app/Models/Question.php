<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Abstracts\AnswerType;
use App\Models\Polls\MyPoll;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $guarded = [];

    protected $casts = [
        'position' => 'integer',
        'options' => 'array',
        'blocks' => 'array',
    ];

    public function questionType(): BelongsTo
    {
        return $this->belongsTo(QuestionType::class, 'question_type_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function answerType(): AnswerType
    {
        return $this->questionType->answerType();
    }

    public function poll(): BelongsTo
    {
        return $this->belongsTo(MyPoll::class, 'poll_id', 'id');
    }

    public function hasOptions(): bool
    {
        return ! empty($this->options);
    }
}
