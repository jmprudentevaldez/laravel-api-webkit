<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Throwable;

class PublicRoutesTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
    }

    /** @throws Throwable */
    public function test_user_can_check_for_unavailable_email(): void
    {
        $email = strtoupper(fake()->safeEmail());
        $this->produceUsers(1, ['email' => $email]);

        $response = $this->get(self::BASE_API_URI.'/availability/email?value='.$email);
        $response->assertStatus(200);

        $response = $response->decodeResponseJson();
        $this->assertFalse($response['data']['is_available']);
    }

    /** @throws Throwable */
    public function test_user_can_check_for_available_email(): void
    {
        $this->produceUsers(3);
        $email = strtoupper(fake()->unique()->safeEmail());

        $response = $this->get(self::BASE_API_URI.'/availability/email?value='.$email);
        $response->assertStatus(200);

        $response = $response->decodeResponseJson();
        $this->assertTrue($response['data']['is_available']);
    }

    /** @throws Throwable */
    public function test_user_can_check_for_available_email_except_for_id(): void
    {
        $email = strtoupper(fake()->safeEmail());
        $user = $this->produceUsers(1, ['email' => $email]);

        $response = $this->get(
            self::BASE_API_URI.'/availability/email?value='.$email.'&excluded_id='.$user->id
        );

        $response->assertStatus(200);

        $response = $response->decodeResponseJson();
        $this->assertTrue($response['data']['is_available']);
    }

    /** @throws Throwable */
    public function test_user_can_check_for_unavailable_mobile_number(): void
    {
        $mobileNumber = '+639064647290';
        User::factory()->has(UserProfile::factory()->state(['mobile_number' => $mobileNumber]))->create();

        $response = $this->get(self::BASE_API_URI.'/availability/mobile_number?value='.urlencode($mobileNumber));
        $response->assertStatus(200);

        $response = $response->decodeResponseJson();
        $this->assertFalse($response['data']['is_available']);
    }

    /** @throws Throwable */
    public function test_user_can_check_for_available_mobile_number(): void
    {
        $this->produceUsers(2);
        $mobileNumber = urlencode('+639064647299');
        $response = $this->get(self::BASE_API_URI.'/availability/mobile_number?value='.$mobileNumber);
        $response->assertStatus(200);

        $response = $response->decodeResponseJson();
        $this->assertTrue($response['data']['is_available']);
    }

    /** @throws Throwable */
    public function test_user_can_check_for_available_mobile_number_except_for_id(): void
    {
        $mobileNumber = '+639064647290';
        $user = User::factory()->has(UserProfile::factory()->state(['mobile_number' => $mobileNumber]))->create();

        $url = self::BASE_API_URI.'/availability/mobile_number?value='.urlencode($mobileNumber);
        $url .= '&excluded_id='.$user->id;
        $response = $this->get($url);

        $response->assertStatus(200);
        $response = $response->decodeResponseJson();
        $this->assertTrue($response['data']['is_available']);
    }
}
