<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Polls\PublicPoll;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $guarded = [];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function publicPolls(): HasMany
    {
        return $this->hasMany(PublicPoll::class, 'category_id');
    }
}
