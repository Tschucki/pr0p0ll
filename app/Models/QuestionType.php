<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Abstracts\AnswerType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class QuestionType extends Model
{
    protected $guarded = [];

    public function questions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Scope a query to only include popular users.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('disabled', false);
    }

    public function answerType(): AnswerType
    {
        return new $this->answer_type;
    }

    public function hasOptions(): bool
    {
        return in_array($this->component, ['radio', 'checkbox-list']);
    }
}
