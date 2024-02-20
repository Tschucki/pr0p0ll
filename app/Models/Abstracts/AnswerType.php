<?php

declare(strict_types=1);

namespace App\Models\Abstracts;

use App\Models\Answer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

abstract class AnswerType extends Model
{
    public function answer(): MorphOne
    {
        return $this->morphOne(Answer::class, 'answerable');
    }
}
