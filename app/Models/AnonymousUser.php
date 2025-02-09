<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    protected $appends = [
        'age',
    ];

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class, 'anonymous_user_id');
    }

    public function getAgeAttribute()
    {
        return $this->birthday->age;
    }
}
