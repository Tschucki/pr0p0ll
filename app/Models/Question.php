<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $guarded = [];

    protected $casts = [
        'position' => 'integer',
        'options' => 'array',
    ];

    public function questionType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(QuestionType::class, 'question_type_id');
    }

    public function hasOptions(): bool
    {
        return !empty($this->options);
    }
}
