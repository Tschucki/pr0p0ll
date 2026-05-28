<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Gender;
use App\Enums\Nationality;
use App\Enums\Region;
use App\Models\Abstracts\Poll;
use App\Models\AnonymousUser;
use Carbon\Carbon;
use Illuminate\Support\Collection;

// Aggregiert die (bewusst ungefilterte) Teilnehmer-Demografie einer Umfrage über AnonymousUser.
class PollDemographicsService
{
    public function __construct(private Poll $poll) {}

    public function get(): array
    {
        $participants = $this->participants();
        $total = $participants->count();

        return [
            'total' => $total,
            'averageAge' => $this->averageAge($participants),
            'gender' => $this->genderDistribution($participants, $total),
            'age' => $this->ageDistribution($participants, $total),
            'regions' => $this->topDistribution($participants, 'region', fn (string $value) => Region::tryFrom($value)?->getLabel() ?? $value, $total),
            'nationalities' => $this->topDistribution($participants, 'nationality', fn (string $value) => Nationality::tryFrom($value)?->getLabel() ?? $value, $total),
        ];
    }

    private function participants(): Collection
    {
        $aIds = $this->poll->answers()
            ->whereNotNull('anonymous_user_id')
            ->distinct()
            ->pluck('anonymous_user_id');

        return AnonymousUser::whereIn('id', $aIds)->get();
    }

    private function averageAge(Collection $participants): ?float
    {
        $aAges = $participants->map(fn (AnonymousUser $user) => $user->birthday ? Carbon::make($user->birthday)->age : null)
            ->filter(fn (?int $age) => $age !== null);

        if ($aAges->isEmpty()) {
            return null;
        }

        return round((float) $aAges->average(), 1);
    }

    private function genderDistribution(Collection $participants, int $total): array
    {
        $aDistribution = [];
        foreach (Gender::cases() as $gender) {
            $count = $participants->where('gender', $gender->value)->count();
            if ($count === 0) {
                continue;
            }
            $aDistribution[] = $this->row($gender->getLabel() ?? $gender->value, $count, $total);
        }

        $unknown = $participants->filter(fn (AnonymousUser $user) => blank($user->gender))->count();
        if ($unknown > 0) {
            $aDistribution[] = $this->row('Keine Angabe', $unknown, $total);
        }

        return $aDistribution;
    }

    private function ageDistribution(Collection $participants, int $total): array
    {
        $aBuckets = [
            'unter 18' => fn (int $age) => $age < 18,
            '18–24' => fn (int $age) => $age >= 18 && $age <= 24,
            '25–34' => fn (int $age) => $age >= 25 && $age <= 34,
            '35–44' => fn (int $age) => $age >= 35 && $age <= 44,
            '45–54' => fn (int $age) => $age >= 45 && $age <= 54,
            '55+' => fn (int $age) => $age >= 55,
        ];

        $aAges = $participants->map(fn (AnonymousUser $user) => $user->birthday ? Carbon::make($user->birthday)->age : null)
            ->filter(fn (?int $age) => $age !== null);

        $aDistribution = [];
        foreach ($aBuckets as $label => $matcher) {
            $count = $aAges->filter($matcher)->count();
            if ($count === 0) {
                continue;
            }
            $aDistribution[] = $this->row($label, $count, $total);
        }

        return $aDistribution;
    }

    private function topDistribution(Collection $participants, string $attribute, callable $labelResolver, int $total, int $limit = 5): array
    {
        return $participants
            ->filter(fn (AnonymousUser $user) => filled($user->{$attribute}))
            ->groupBy($attribute)
            ->map(fn (Collection $group, string $value) => $this->row($labelResolver($value), $group->count(), $total))
            ->sortByDesc('count')
            ->take($limit)
            ->values()
            ->all();
    }

    private function row(string $label, int $count, int $total): array
    {
        return [
            'label' => $label,
            'count' => $count,
            'percentage' => $total > 0 ? round($count / $total * 100, 1) : 0.0,
        ];
    }
}
