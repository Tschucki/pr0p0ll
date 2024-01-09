<?php

namespace App\Models\Polls;

use App\Abstracts\Poll as AbstractPoll;
use App\Models\Answer;
use App\Models\Question;
use App\Models\Scopes\PublicPollScope;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PublicPoll extends AbstractPoll
{
    protected static function booted(): void
    {
        static::addGlobalScope(new PublicPollScope);
    }
}
