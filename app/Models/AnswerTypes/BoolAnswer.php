<?php

declare(strict_types=1);

namespace App\Models\AnswerTypes;

use App\Models\Abstracts\AnswerType;

class BoolAnswer extends AnswerType
{
    protected $guarded = [];

    protected $casts = [
        'answer_value' => 'boolean',
    ];
}
