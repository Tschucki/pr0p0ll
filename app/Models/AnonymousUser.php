<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class AnonymousUser extends Model
{
    protected $fillable = [
        'birthday',
        'nationality',
        'gender',
        'region',
    ];

    protected $casts = [
        'birthday' => 'date',
    ];

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class, 'anonymous_user_id');
    }
}
