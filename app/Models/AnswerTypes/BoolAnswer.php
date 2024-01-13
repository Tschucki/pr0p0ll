<?php

namespace App\Models\AnswerTypes;

use App\Abstracts\AnswerType;

class BoolAnswer extends AnswerType
{
    protected $guarded = [];

    protected $casts = [
        'answer_value' => 'boolean',
    ];
}
