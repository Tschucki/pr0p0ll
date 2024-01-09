<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

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

    public function answerType()
    {
        return new $this->answer_type;
    }

    public function hasOptions(): bool
    {
        return in_array($this->component, ['radio', 'checkbox-list']);
    }
}
