<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'password' => 'Sample_Password_1',
            'active' => true,
            'email_verified_at' => fake()->dateTime(),
        ];
    }

    /**
     * @State
     * User is suspended
     */
    public function suspended(): Factory
    {
        return $this->state(function () {
            return ['active' => false];
        });
    }

    /**
     * @State
     * User has their email unverified
     */
    public function unVerified(): Factory
    {
        return $this->state(function () {
            return ['email_verified_at' => null];
        });
    }
}
