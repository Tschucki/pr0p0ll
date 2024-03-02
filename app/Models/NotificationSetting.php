<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'enabled' => 'bool',
    ];
}
