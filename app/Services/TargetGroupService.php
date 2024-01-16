<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class TargetGroupService
{
    public static function calculateTargetGroupFromBuilder(array $builderData): int
    {
        $gender = null;
        $nationality = [];
        $minAge = null;
        $maxAge = null;
        $region = [];

        collect($builderData)->each(function (array $field) use(&$gender, &$nationality, &$minAge, &$maxAge, &$region) {
            if($field['type'] === 'gender') {
                $gender = $field['data']['gender'];
            }
            if($field['type'] === 'nationality') {
                $nationality = $field['data']['nationality'];
            }
            if($field['type'] === 'min_age') {
                $minAge = $field['data']['min_age'];
            }
            if($field['type'] === 'max_age') {
                $maxAge = $field['data']['max_age'];
            }
            if($field['type'] === 'region') {
                $region = $field['data']['region'];
            }
        });

        $query = User::query();

        $query
            ->when($gender, fn (Builder $query) => $query->where('gender', $gender))
            ->when($nationality, fn(Builder $query) => $query->whereIn('nationality', $nationality))
            ->when($minAge, fn(Builder $query) => $query->whereDate('birthday', '<=', Carbon::now()->subYears($minAge)))
            ->when($maxAge, fn(Builder $query) => $query->whereDate('birthday', '>', Carbon::now()->subYears($maxAge)))
            ->when($region, fn(Builder $query) => $query->whereIn('region', $region));

        return $query->count();
    }
}
