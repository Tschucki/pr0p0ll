<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Polls\MyPoll;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $guarded = [];

    public function poll(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MyPoll::class);
    }

    public function question(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function answerable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
