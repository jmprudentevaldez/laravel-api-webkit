<?php

namespace Tests\Feature;

use App\Enums\Role as RoleEnum;
use App\Enums\SexualCategory;
use App\Models\Address\Barangay;
use App\Models\Address\City;
use App\Models\Address\Province;
use App\Models\Address\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Throwable;

class ProfileTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private string $baseUri = self::BASE_API_URI.'/profile';

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');

        $this->user = $this->produceUsers();
        $this->user->syncRoles(RoleEnum::STANDARD_USER->value);
        Sanctum::actingAs($this->user);
    }

    public function test_user_can_view_profile(): void
    {
        $response = $this->get($this->baseUri);
        $response->assertStatus(200);
    }

    /** @throws Throwable */
    public function test_user_can_update_profile(): void
    {
        $edits = [
            'email' => fake()->unique()->email(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'middle_name' => fake()->lastName(),
            'ext_name' => fake()->randomElement(['Jr.'.'Sr.', 'III']),
            'sex' => fake()->randomElement([SexualCategory::MALE->value, SexualCategory::FEMALE->value]),
            'telephone_number' => '+63279434211',
            'mobile_number' => '+639064647210',
            'birthday' => '1997-01-04',
            'home_address' => 'Address Line 1',
            'barangay_id' => Barangay::latest()->first()->id,
            'city_id' => City::latest()->first()->id,
            'province_id' => Province::latest()->first()->id,
            'region_id' => Region::latest()->first()->id,
            'postal_code' => '221',
        ];

        $response = $this->patchJson($this->baseUri, $edits);
        $response->assertStatus(200);
        $result = $response->decodeResponseJson();

        foreach ($edits as $key => $value) {
            // check for credentials correctness
            if ($key === 'email') {
                $this->assertEquals($value, $result['data'][$key]);

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

            // profile details are wrapped with a `user_profile` field
            $result = $response['data']['user_profile'][$key];
            $this->assertEquals($value, $result);
        }
    }

    public function test_it_can_upload_profile_picture(): void
    {
        $file = UploadedFile::fake()->image('fake_image.jpg', 500, 500);
        $response = $this->post("$this->baseUri/profile-picture", ['photo' => $file]);
        $response->assertStatus(200);

        // clean the bucket
        Storage::disk('s3')->deleteDirectory('images/');
    }

    public function test_user_can_change_password(): void
    {
        $oldPassword = 'OldPassword123';
        $this->user->password = $oldPassword;
        $this->user->save();

        $newPassword = 'NewPassword123';
        $input = [
            'old_password' => $oldPassword,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ];
        $result = $this->patchJson("$this->baseUri/password", $input);
        $result->assertStatus(200);

        // login again with the new password
        $creds = ['email' => $this->user->email, 'password' => $newPassword];
        $response = $this->post('api/v1/auth/tokens', $creds);
        $response->assertStatus(200);
    }
}
