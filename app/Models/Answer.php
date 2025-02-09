<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Polls\MyPoll;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Answer extends Model
{
    protected $guarded = [];

    public function poll(): BelongsTo
    {
        return $this->belongsTo(MyPoll::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function answerable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function anonymousUser(): BelongsTo
    {
        return $this->belongsTo(AnonymousUser::class);
    }

    public function scopeFilter($query, array $filters): void
    {
        /*$filters = [
              "nationality" => [],
              "region" => [],
              "gender" => null,
              "min_age" => null,
              "max_age" => null,
        ];*/
        $query->when($filters['nationality'] ?? null, function ($query, $nationality) {
            $query->where(function (Builder $query) use ($nationality) {
                $query->whereHas('anonymousUser', function (Builder $query) use ($nationality) {
                    $query->whereIn('nationality', $nationality);
                });
            });
        });

        $query->when($filters['region'] ?? null, function ($query, $region) {
            $query->where(function (Builder $query) use ($region) {
                $query->whereHas('anonymousUser', function (Builder $query) use ($region) {
                    $query->whereIn('region', $region);
                });
            });
        });

        $query->when($filters['gender'] ?? null, function ($query, $gender) {
            $query->where(function (Builder $query) use ($gender) {
                $query->whereHas('anonymousUser', function (Builder $query) use ($gender) {
                    $query->where('gender', $gender);
                });
            });
        });

        $query->when($filters['min_age'] ?? null, function ($query, $min_age) {
            $query->where(function (Builder $query) use ($min_age) {
                $query->whereHas('anonymousUser', function (Builder $query) use ($min_age) {
                    $query->whereDate('birthday', '<=', Carbon::now()->subYears($min_age));
                });
            });
        });

        $query->when($filters['max_age'] ?? null, function ($query, $max_age) {
            $query->where(function (Builder $query) use ($max_age) {
                $query->whereHas('anonymousUser', function (Builder $query) use ($max_age) {
                    $query->whereDate('birthday', '>', Carbon::now()->subYears($max_age));
                });
            });
        });
    }
}
