<?php

namespace Tests\Unit;

use App\Enums\PaginationType;
use App\Interfaces\HttpResources\UserServiceInterface;
use App\Models\Address\Barangay;
use App\Models\Address\City;
use App\Models\Address\Province;
use App\Models\Address\Region;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\HttpResources\UserService;
use Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Tests\TestCase;
use Throwable;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private UserServiceInterface $userService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->userService = new UserService(new User());
    }

    /** @throws Throwable */
    public function test_it_can_create_a_user(): void
    {
        $this->userService->create($this->getUserDetails());
        $this->assertDatabaseCount('users', 1);
    }

    /** @throws Throwable */
    public function test_it_can_update_a_user(): void
    {
        $user = $this->produceUsers();
        $edited = ['first_name' => fake()->firstName, 'last_name' => fake()->lastName];
        $editedUser = $this->userService->update($user->id, $edited);

        $this->assertEquals($edited['first_name'], $editedUser->userProfile->first_name);
        $this->assertEquals($edited['last_name'], $editedUser->userProfile->last_name);
    }

    public function test_it_can_fetch_all_users(): void
    {
        $count = 10;
        $this->produceUsers($count);

        $users = $this->userService->all();
        $this->assertCount($count, $users);
    }

    public function test_it_can_fetch_all_users_with_pagination(): void
    {
        $count = 10;
        $this->produceUsers($count);

        $request = new Request();
        $limit = 5;
        $request->replace(['limit' => $limit]);
        app()->instance('request', $request);

        $users = $this->userService->all(PaginationType::LENGTH_AWARE);

        $this->assertEquals($count, $users->total());
        $this->assertCount($limit, $users->items());
    }

    /**
     * Generate user info
     */
    private function getUserDetails(): array
    {
        return [
            'email' => fake()->unique()->safeEmail,
            'first_name' => fake()->firstName,
            'last_name' => fake()->lastName,
            'middle_name' => fake()->lastName,
            'ext_name' => fake()->randomElement(['Jr.', 'Sr.', 'III']),
            'password' => 'Sample123_123',
            'password_confirmation' => 'Sample123_123',
            'active' => fake()->boolean,
            'email_verified' => fake()->boolean,
            'mobile_number' => '+63906'.fake()->unique()->randomNumber(7),
            'telephone_number' => '+6327'.fake()->randomNumber(7),
            'sex' => fake()->randomElement(['male', 'female']),
            'birthday' => '1997-01-05',
            'home_address' => $this->faker->streetName,
            'barangay_id' => Barangay::first()->id,
            'city_id' => City::first()->id,
            'province_id' => Province::first()->id,
            'region_id' => Region::first()->id,
            'postal_code' => $this->faker->postcode,
            'profile_picture_path' => $this->faker->filePath,
        ];
    }

    public function test_it_can_read_a_single_user(): void
    {
        $createdUser = $this->produceUsers();
        $foundUser = $this->userService->read($createdUser->id);

        $this->assertEquals($createdUser->id, $foundUser->id);
    }

    public function test_it_can_soft_delete_a_user(): void
    {
        $this->produceUsers(3);
        $this->userService->destroy(User::first());

        $foundUsers = User::all();
        $this->assertCount(2, $foundUsers);

        $trashedUsers = User::onlyTrashed()->count();
        $this->assertEquals(1, $trashedUsers);
    }

    public function test_user_and_user_profile_cascade_soft_delete(): void
    {
        $this->produceUsers(3);
        $this->userService->destroy(User::first());

        $trashedUsers = User::onlyTrashed()->count();
        $this->assertEquals(1, $trashedUsers);

        $trashedUserProfiles = UserProfile::onlyTrashed()->count();
        $this->assertEquals(1, $trashedUserProfiles);
    }

    public function test_it_can_update_password(): void
    {
        $user = $this->produceUsers();
        $oldPassword = 'test_old_123';
        $user->password = $oldPassword;
        $user->save();

        $newPassword = 'test_new_123';
        $updatedUser = $this->userService->updatePassword($user, $newPassword, $oldPassword);
        $this->assertNotNull($updatedUser);

        $isCorrect = Hash::check($newPassword, $updatedUser->password);
        $this->assertTrue($isCorrect);
    }
}
