<?php

declare(strict_types=1);

namespace App\Abstracts;

use App\Models\Answer;
use Illuminate\Database\Eloquent\Model;

abstract class AnswerType extends Model
{
    public function answer(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(Answer::class, 'answerable');
    }
}
