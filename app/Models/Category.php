<?php

namespace App\Models;

use App\Models\Polls\PublicPoll;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = [];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function publicPolls(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PublicPoll::class, 'category_id');
    }
}
