<?php

namespace Tests\Feature;

use App\Enums\AppTheme;
use App\Enums\Role as RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AppSettingsTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private string $baseUri = self::BASE_API_URI.'/app-settings';

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');

        /** @var User $user */
        $user = $this->produceUsers();
        $roles = [RoleEnum::ADMIN->value, RoleEnum::SUPER_USER->value];
        $user->syncRoles(fake()->randomElement($roles));
        Sanctum::actingAs($user);
    }

    public function test_it_can_store_app_settings(): void
    {
        $input = [
            'theme' => AppTheme::SPACE->value,
        ];

        $response = $this->postJson($this->baseUri, $input);
        $response->assertStatus(201);
    }

    public function test_it_can_validated_themes(): void
    {
        $input = [
            'theme' => 'this-theme-does-not-exists',
        ];

        $response = $this->postJson($this->baseUri, $input);
        $response->assertStatus(422);
    }

    public function test_it_can_fetch_app_settings(): void
    {
        $response = $this->getJson($this->baseUri);
        $response->assertStatus(200);
    }
}
