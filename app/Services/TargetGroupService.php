<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class TargetGroupService
{
    public static function calculateTargetGroupFromBuilder(array $aBuilderData): int
    {
        $aTargetGroupData = self::builderDataToArray($aBuilderData);

        return self::baseQuery($aTargetGroupData)->count();
    }

    public static function userIsWithinTargetGroup(array $aBuilderData = [], ?User $user): bool
    {
        if (empty($aBuilderData) || $user === null) {
            return true;
        }
        $aTargetGroupData = self::builderDataToArray($aBuilderData);

        return self::baseQuery($aTargetGroupData)->where('id', $user->getKey())->exists();
    }

    private static function baseQuery(array $aTargetGroupData): Builder
    {
        $query = User::query();

        $query
            ->when($aTargetGroupData['gender'], fn (Builder $query) => $query->where('gender', $aTargetGroupData['gender']))
            ->when($aTargetGroupData['nationality'], fn (Builder $query) => $query->whereIn('nationality', $aTargetGroupData['nationality']))
            ->when($aTargetGroupData['minAge'], fn (Builder $query) => $query->whereDate('birthday', '<=', Carbon::now()->subYears($aTargetGroupData['minAge'])))
            ->when($aTargetGroupData['maxAge'], fn (Builder $query) => $query->whereDate('birthday', '>', Carbon::now()->subYears($aTargetGroupData['maxAge'])))
            ->when($aTargetGroupData['region'], fn (Builder $query) => $query->whereIn('region', $aTargetGroupData['region']));

        return $query;
    }

    private static function builderDataToArray(array $builderData): array
    {
        $gender = null;
        $nationality = [];
        $minAge = null;
        $maxAge = null;
        $region = [];

        collect($builderData)->each(function (array $field) use (&$gender, &$nationality, &$minAge, &$maxAge, &$region) {
            if ($field['type'] === 'gender') {
                $gender = $field['data']['gender'];
            }
            if ($field['type'] === 'nationality') {
                $nationality = $field['data']['nationality'];
            }
            if ($field['type'] === 'min_age') {
                $minAge = $field['data']['min_age'];
            }
            if ($field['type'] === 'max_age') {
                $maxAge = $field['data']['max_age'];
            }
            if ($field['type'] === 'region') {
                $region = $field['data']['region'];
            }
        });

        return [
            'gender' => $gender,
            'nationality' => $nationality,
            'minAge' => $minAge,
            'maxAge' => $maxAge,
            'region' => $region,
        ];
    }
}
