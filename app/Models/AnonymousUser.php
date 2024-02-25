<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnonymousUser extends Model
{
    protected $fillable = [
        'demographic_data'
    ];

    protected $casts = [
        'demographic_data' => 'array'
    ];

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class, 'anonymous_user_id');
    }
}
