<?php

namespace Database\Factories;

use App\Enums\Gender;
use App\Enums\Nationality;
use App\Enums\Region;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'pr0gramm_identifier' => fake()->userName(),
            'needs_data_review' => false,
            // Random Nationality, Region, Gender, Birthday,
            'nationality' => Nationality::cases()[fake()->numberBetween(0, count(Nationality::cases()) - 1)]->value,
            'region' => Region::cases()[fake()->numberBetween(0, count(Region::cases()) - 1)]->value,
            'gender' => Gender::cases()[fake()->numberBetween(0, count(Gender::cases()) - 1)]->value,
            'birthday' => fake()->dateTimeBetween('-100 years', '-18 years'),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
