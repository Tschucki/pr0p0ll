<?php

namespace App\Models\Polls;

use App\Abstracts\Poll as AbstractPoll;
use App\Models\Scopes\PublicPollScope;

class PublicPoll extends AbstractPoll
{
    protected static function booted(): void
    {
        static::addGlobalScope(new PublicPollScope);
    }
}
