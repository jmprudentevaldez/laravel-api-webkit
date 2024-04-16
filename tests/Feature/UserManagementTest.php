<?php

namespace Tests\Feature;

use App\Enums\Role as RoleEnum;
use App\Models\Address\Barangay;
use App\Models\Address\City;
use App\Models\Address\Province;
use App\Models\Address\Region;
use App\Models\User;
use App\Models\UserProfile;
use App\Notifications\WelcomeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Throwable;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private string $baseUri = self::BASE_API_URI.'/users';

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        Notification::fake();

        /** @var User $user */
        $user = $this->produceUsers();
        $roles = [RoleEnum::ADMIN->value, RoleEnum::SUPER_USER->value];
        $user->syncRoles(fake()->randomElement($roles));
        Sanctum::actingAs($user);
    }

    /**
     * @dataProvider validCreateUserInputs
     *
     * @note we can't use Eloquent nor faker in data providers
     *
     * @throws Throwable
     */
    public function test_it_can_create_a_user($input, $statusCode): void
    {
        $input['city_id'] = City::first()->id;
        $input['province_id'] = Province::first()->id;
        $input['region_id'] = Region::first()->id;
        $input['profile_picture_path'] = fake()->filePath();

        $response = $this->postJson($this->baseUri, $input);
        $response->assertStatus($statusCode);

        if ($statusCode !== 422) {
            $createdUser = User::find($response->decodeResponseJson()['data']['id']);
            Notification::assertSentTo($createdUser, WelcomeNotification::class);
        }
    }

    public static function validCreateUserInputs(): array
    {
        $requiredFieldsOnly = [
            'email' => 'sample@email.com',
            'password' => 'Sample_Password_1',
            'password_confirmation' => 'Sample_Password_1',
            'first_name' => 'Jeg',
            'last_name' => 'Ramos',
        ];

        $allFields = array_merge($requiredFieldsOnly, [
            'active' => true,
            'email_verified' => false,
            'middle_name' => 'Bucu',
            'ext_name' => 'Jr.',
            'mobile_number' => '+639064647295',
            'telephone_number' => '+63279434285',
            'sex' => 'male',
            'birthday' => '1997-01-04',
            'home_address' => 'Home Address',
            'postal_code' => '211',
        ]);

        $missingRequiredFields = Arr::except(
            $allFields,
            ['email', 'password', 'password_confirmation', 'first_name', 'last_name']
        );

        return [
            [$requiredFieldsOnly, 201],
            [$allFields, 201],
            [$missingRequiredFields, 422],
        ];
    }

    /** @throws Throwable */
    public function test_it_should_validate_unique_fields_when_creating_a_user(): void
    {
        $user = $this->produceUsers();
        $input = $this->getRequiredUserInputSample();
        $input['email'] = $user->email;

        $response = $this->postJson($this->baseUri, $input);
        $response->assertStatus(422);

        $response = $response->decodeResponseJson();
        foreach ($response['errors'] as $error) {
            $this->assertTrue($error['field'] === 'email');
        }
    }

    /** @throws Throwable */
    public function test_it_can_update_a_user(): void
    {
        $user = $this->produceUsers();

        $edits = [
            'email' => fake()->unique()->safeEmail(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'password' => 'Sample123_123',
            'password_confirmation' => 'Sample123_123',
            'active' => fake()->boolean(),
            'middle_name' => fake()->lastName(),
            'ext_name' => fake()->randomElement(['Jr.', 'Sr.', 'III']),
            'mobile_number' => '+639064647291',
            'telephone_number' => '+63279434285',
            'sex' => fake()->randomElement(['male', 'female']),
            'birthday' => '1997-01-05',
            'home_address' => fake()->buildingNumber(),
            'barangay_id' => Barangay::first()->id,
            'city_id' => City::first()->id,
            'province_id' => Province::first()->id,
            'region_id' => Region::first()->id,
            'postal_code' => fake()->postcode(),
            'profile_picture_path' => fake()->filePath(),
        ];

        $response = $this->patchJson("$this->baseUri/$user->id", $edits);
        $response->assertStatus(200);

        $response = $response->decodeResponseJson();

        // compare the input edits to the actual response data
        foreach ($edits as $key => $value) {
            // ignore hidden fields in the response
            if (in_array($key, ['password', 'password_confirmation'])) {
                continue;
            }

            // home_address, barangay, postal_code are wrapped in `user_profile.address` field
            if (in_array($key, ['home_address', 'postal_code'])) {
                $result = $response['data']['user_profile']['address'][$key];
                $this->assertEquals($value, $result);

                continue;
            }

            // city_id, province_id, region_id are wrapped in `user_profile.address.[city|region|province]`
            if (in_array($key, ['city_id', 'province_id', 'region_id', 'barangay_id'])) {
                // from city_id => city
                $relationName = explode('_id', $key)[0];

                $result = $response['data']['user_profile']['address'][$relationName]['id'];
                $this->assertEquals($value, $result);

                continue;
            }

            // if the profile_picture_path is provided, a profile_picture_url is returned
            if ($key === 'profile_picture_path') {
                $result = $response['data']['user_profile']['profile_picture_url'];
                $this->assertTrue(URL::isValidUrl($result));

                continue;
            }

            // profile details are wrapped with a `user_profile` field
            if (! in_array($key, ['email', 'active'])) {
                $result = $response['data']['user_profile'][$key];
                $this->assertEquals($value, $result);

                continue;
            }

            // the rest are credentials
            $this->assertEquals($value, $response['data'][$key]);
        }
    }

    /** @throws Throwable */
    public function test_it_should_validate_unique_mobile_number_and_email_when_updating_a_user(): void
    {
        $users = $this->produceUsers(2);
        $users[1]->userProfile->mobile_number = '+639164647295';
        $users[1]->save();

        $user2Info = [
            'email' => $users[1]->email,
            'mobile_number' => $users[1]->userProfile->mobile_number,
        ];

        // try to update the first user's username and email with user 2's
        $response = $this->patchJson("$this->baseUri/{$users[0]->id}", $user2Info);
        $response->assertStatus(422);
    }

    /** @throws Throwable */
    public function test_it_should_ignore_unique_validation_when_updating_the_same_user_with_the_same_field_values(): void
    {
        $user = $this->produceUsers();
        $user->userProfile->mobile_number = '+639164647295';
        $user->save();

        $input = [
            'email' => $user->email,
            'mobile_number' => $user->userProfile->mobile_number,
        ];

        $response = $this->patchJson("$this->baseUri/$user->id", $input);
        $response->assertStatus(200);
    }

    /** @dataProvider differentMobileNumbers */
    public function test_it_should_validate_mobile_number_formats($input, $statusCode): void
    {
        $result = $this->postJson($this->baseUri, $input);
        $result->assertStatus($statusCode);
    }

    public static function differentMobileNumbers(): array
    {
        $requiredFields = [
            'email' => 'sample_email@email.com',
            'username' => 'username1',
            'password' => 'Sample_Password_1',
            'password_confirmation' => 'Sample_Password_1',
            'first_name' => 'Jeg',
            'last_name' => 'Ramos',
        ];

        return [
            [array_merge($requiredFields, ['mobile_number' => '+639064647295']), 201],
            [array_merge($requiredFields, ['mobile_number' => '+63 9064647295']), 422],
            [array_merge($requiredFields, ['mobile_number' => '639064647295']), 422],
            [array_merge($requiredFields, ['mobile_number' => '09064647295']), 422],
        ];
    }

    /** @dataProvider differentTelephoneNumbers */
    public function test_it_should_validate_telephone_number_formats($input, $statusCode): void
    {
        $result = $this->postJson('api/v1/users', $input);
        $result->assertStatus($statusCode);
    }

    public static function differentTelephoneNumbers(): array
    {
        $requiredFields = [
            'email' => 'sample_email@email.com',
            'username' => 'username1',
            'password' => 'Sample_Password_1',
            'password_confirmation' => 'Sample_Password_1',
            'first_name' => 'Jeg',
            'last_name' => 'Ramos',
        ];

        return [
            [array_merge($requiredFields, ['telephone_number' => '+63279434285']), 201],
            [array_merge($requiredFields, ['telephone_number' => '+63 279434285']), 422],
            [array_merge($requiredFields, ['telephone_number' => '63279434285']), 422],
            [array_merge($requiredFields, ['telephone_number' => '279434285']), 422],
        ];
    }

    public function test_it_can_read_a_user(): void
    {
        $user = $this->produceUsers();

        $response = $this->get("$this->baseUri/$user->id");
        $response->assertStatus(200);
    }

    /** @throws Throwable */
    public function test_it_can_delete_a_user(): void
    {
        $user = $this->produceUsers();

        $response = $this->delete("$this->baseUri/$user->id");
        $response->assertStatus(204);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    /** @throws Throwable */
    public function test_it_can_fetch_users(): void
    {
        $this->produceUsers(5);
        $totalUserCount = User::count('id');

        $response = $this->get($this->baseUri);
        $response = $response->decodeResponseJson();

        $this->assertIsArray($response['data']);
        $this->assertCount($totalUserCount, $response['data']);
    }

    /** @throws Throwable */
    public function test_it_can_return_length_aware_paginated_results(): void
    {
        $this->produceUsers(15);
        $totalUserCount = User::count('id');

        $limit = 5;
        $response = $this->get("$this->baseUri?limit=$limit");
        $response = $response->decodeResponseJson();

        $this->assertArrayHasKey('pagination', $response);
        $this->assertEquals($totalUserCount, $response['pagination']['total']);
        $this->assertCount($limit, $response['data']);
    }

    /** @throws Throwable */
    public function test_it_sets_up_the_active_and_email_verified_at_fields_when_not_provided(): void
    {
        $onlyRequiredInputs = $this->getRequiredUserInputSample();
        $response = $this->postJson($this->baseUri, $onlyRequiredInputs);
        $response = $response->decodeResponseJson();
        $user = User::find($response['data']['id']);

        $this->assertTrue($user->active);
        $this->assertNull($user->email_verified_at);
    }

    /** @throws Throwable */
    public function test_if_email_verified_is_not_in_payload_then_email_verified_at_should_be_null(): void
    {
        $input = $this->getRequiredUserInputSample();
        $response = $this->postJson($this->baseUri, $input);
        $response = $response->decodeResponseJson();
        $user = User::find($response['data']['id']);
        $this->assertNull($user->email_verified_at);
    }

    /** @throws Throwable */
    public function test_if_email_verified_field_is_false_then_email_verified_at_field_should_be_null(): void
    {
        $input = $this->getRequiredUserInputSample();
        $input['email_verified'] = false;
        $response = $this->postJson($this->baseUri, $input);
        $response = $response->decodeResponseJson();
        $user = User::find($response['data']['id']);
        $this->assertNull($user->email_verified_at);
    }

    /** @throws Throwable */
    public function test_if_email_verified_field_is_true_then_email_verified_at_field_should_be_a_valid_date(): void
    {
        $input = $this->getRequiredUserInputSample();
        $input['email_verified'] = true;
        $response = $this->postJson($this->baseUri, $input);
        $response = $response->decodeResponseJson();
        $user = User::find($response['data']['id']);
        $this->assertTrue((bool) strtotime($user->email_verified_at->toDateString()));
    }

    public function test_it_can_upload_profile_picture(): void
    {
        $user = $this->produceUsers();
        $file = UploadedFile::fake()->image('fake_image.jpg', 500, 500);
        $response = $this->post("$this->baseUri/$user->id/profile-picture", ['photo' => $file]);
        $response->assertStatus(200);

        // clean the bucket
        Storage::disk('s3')->deleteDirectory('images/');
    }

    /** @throws Throwable */
    public function test_it_can_set_a_default_role_as_standard_user(): void
    {
        $response = $this->post($this->baseUri, $this->getRequiredUserInputSample());
        $response = $response->decodeResponseJson();

        $this->assertCount(1, $response['data']['roles']);
        $this->assertEquals(RoleEnum::STANDARD_USER->value, $response['data']['roles'][0]['name']);
    }

    /** @throws Throwable */
    public function test_it_can_attach_roles_to_a_user(): void
    {
        $firstRole = Role::query()->where('name', RoleEnum::STANDARD_USER->value)->first()->id;
        $secondRole = Role::query()->where('name', RoleEnum::ADMIN->value)->first()->id;
        $expectedRoles = ['roles' => [$firstRole, $secondRole]];

        $response = $this->post($this->baseUri, array_merge($this->getRequiredUserInputSample(), $expectedRoles));
        $response->assertStatus(201);

        $response = $response->decodeResponseJson();
        $this->assertCount(2, $response['data']['roles']);
        $this->assertTrue(in_array($response['data']['roles'][0]['id'], $expectedRoles['roles']));
        $this->assertTrue(in_array($response['data']['roles'][1]['id'], $expectedRoles['roles']));
    }

    /** @throws Throwable */
    public function test_it_can_filter_by_email_while_ignoring_the_case(): void
    {
        $email = fake()->unique()->safeEmail();
        $this->produceUsers(1, ['email' => $email]);

        $email = strtoupper($email);
        $response = $this->get("$this->baseUri?email=$email");
        $response->assertStatus(200);

        $response = $response->decodeResponseJson();
        $this->assertCount(1, $response['data']);
    }

    /** @throws Throwable */
    public function test_it_can_filter_via_email_verified_status(): void
    {
        User::query()->delete();

        // Create 3 unverified accounts, and 2 verified ones
        $this->produceUsers(3, [], true);
        $this->produceUsers(2);

        $response = $this->get("$this->baseUri?verified=1");
        $response->decodeResponseJson();
        $this->assertCount(2, $response['data']);

        $response = $this->get("$this->baseUri?verified=0");
        $response->decodeResponseJson();
        $this->assertCount(3, $response['data']);
    }

    /** @throws Throwable */
    public function test_it_can_filter_via_role_id(): void
    {
        User::query()->delete();

        // Create 5 standard users
        $this->produceUsers();

        $superUser = User::first();
        $role = Role::query()->where('name', '=', RoleEnum::SUPER_USER->value)->first();
        $superUser->syncRoles($role->id);

        $response = $this->get("$this->baseUri?role=$role->id");
        $response = $response->decodeResponseJson();

        $this->assertCount(1, $response['data']);
    }

    /** @throws Throwable */
    public function test_fetch_can_be_sorted_via_last_name(): void
    {
        $this->produceUsers(3);

        // test `asc` sort
        $sortedLastNames = UserProfile::orderBy('last_name')->pluck('last_name')->toArray();
        $response = $this->get("$this->baseUri?sort=asc&sort_by=user_profile.last_name");
        $response = $response->decodeResponseJson();
        $mappedLastNames = array_map(fn ($userProfile) => $userProfile['last_name'], $response['data']);
        $this->assertEquals($sortedLastNames, $mappedLastNames);

        // test `desc` sort
        $sortedLastNames = UserProfile::orderBy('last_name', 'desc')->pluck('last_name')->toArray();
        $response = $this->get("$this->baseUri?sort=desc&sort_by=user_profile.last_name");
        $response = $response->decodeResponseJson();
        $mappedLastNames = array_map(fn ($userProfile) => $userProfile['last_name'], $response['data']);
        $this->assertEquals($sortedLastNames, $mappedLastNames);
    }

    /** @throws Throwable */
    public function test_fetch_can_be_sorted_via_first_name(): void
    {
        $this->produceUsers(3);

        // test `asc` sort
        $sortedLastNames = UserProfile::orderBy('first_name')->pluck('first_name')->toArray();
        $response = $this->get("$this->baseUri?sort=asc&sort_by=user_profile.first_name");
        $response = $response->decodeResponseJson();
        $mappedLastNames = array_map(fn ($userProfile) => $userProfile['first_name'], $response['data']);
        $this->assertEquals($sortedLastNames, $mappedLastNames);

        // test `desc` sort
        $sortedLastNames = UserProfile::orderBy('first_name', 'desc')->pluck('first_name')->toArray();
        $response = $this->get("$this->baseUri?sort=desc&sort_by=user_profile.first_name");
        $response = $response->decodeResponseJson();
        $mappedLastNames = array_map(fn ($userProfile) => $userProfile['first_name'], $response['data']);
        $this->assertEquals($sortedLastNames, $mappedLastNames);
    }

    /** @throws Throwable */
    public function test_it_can_search_via_last_name(): void
    {
        User::query()->delete();
        $last_name = $this->produceUsers()->userProfile->last_name;

        $last_name = Str::substr($last_name, 2);
        $response = $this->get("$this->baseUri/search?query=$last_name");
        $response = $response->decodeResponseJson();
        $this->assertCount(1, $response['data']);
    }

    /** @throws Throwable */
    public function test_it_can_search_via_first_name(): void
    {
        User::query()->delete();
        $first_name = $this->produceUsers()->userProfile->first_name;

        $first_name = Str::substr($first_name, 2);
        $response = $this->get("$this->baseUri/search?query=$first_name");
        $response = $response->decodeResponseJson();
        $this->assertCount(1, $response['data']);
    }

    /** @throws Throwable */
    public function test_it_can_search_via_middle_name(): void
    {
        User::query()->delete();
        $middle_name = $this->produceUsers()->userProfile->middle_name;

        $middle_name = Str::substr($middle_name, 2);
        $response = $this->get("$this->baseUri/search?query=$middle_name");
        $response = $response->decodeResponseJson();
        $this->assertCount(1, $response['data']);
    }

    /** @throws Throwable */
    public function test_it_can_search_via_ext_name(): void
    {
        User::query()->delete();
        $ext_name = $this->produceUsers()->userProfile->ext_name;

        $ext_name = urlencode($ext_name);
        $response = $this->get("$this->baseUri/search?query=$ext_name");
        $response = $response->decodeResponseJson();
        $this->assertCount(1, $response['data']);
    }

    /** @throws Throwable */
    public function test_it_can_prefix_search_via_email(): void
    {
        User::query()->delete();
        $email = $this->produceUsers()->email;

        $email = Str::substr($email, 0, -2);
        $response = $this->get("$this->baseUri/search?query=$email");
        $response = $response->decodeResponseJson();
        $this->assertCount(1, $response['data']);
    }
}
