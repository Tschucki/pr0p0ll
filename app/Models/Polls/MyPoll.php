<?php

declare(strict_types=1);

namespace App\Models\Polls;

use App\Abstracts\Poll as AbstractPoll;
use App\Models\Scopes\OwnPollScope;

class MyPoll extends AbstractPoll
{
    protected static function booted(): void
    {
        static::addGlobalScope(new OwnPollScope);
    }
}
