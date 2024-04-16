<?php

namespace Tests;

use App\Enums\Role;
use App\Models\Address\Address;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Routing\Middleware\ThrottleRequests;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public const BASE_API_URI = '/api/v1';

    protected function setUp(): void
    {
        parent::setUp();

        // prevent throttling because we run test in parallel
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    /**
     * Check if two arrays have the same value
     */
    protected function arraysHaveSameValue(array $arr1, array $arr2): bool
    {
        return (count($arr1) === count($arr2)) && ! array_diff($arr1, $arr2);
    }

    /**
     * Generate required user info input
     */
    protected function getRequiredUserInputSample(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'password' => 'Sample_Password_1',
            'password_confirmation' => 'Sample_Password_1',
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
        ];
    }

    /**
     * Create a certain number of users with UserProfile and Address factories
     *
     * @return Collection|Model
     */
    protected function produceUsers(
        int $quantity = 1,
        array $userAttr = [],
        bool $unVerified = false,
        ?Role $role = null
    ): Collection|User {
        $factory = User::factory()
            ->has(
                UserProfile::factory()->has(Address::factory())
            )->count($quantity);

        if ($unVerified) {
            $factory = $factory->unVerified();
        }

        $users = $factory->create($userAttr);

        /** @var User $user */
        if ($role) {
            foreach ($users as $user) {
                $user->syncRoles([$role]);
            }
        }

        if ($quantity === 1) {
            return $users->first();
        }

        return $users;
    }
}
