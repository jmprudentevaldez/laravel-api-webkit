<?php

namespace Tests\Feature;

use App\Enums\Role as RoleEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Throwable;

class RolesTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private string $baseUri = self::BASE_API_URI.'/roles';

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');

        $this->user = $this->produceUsers();
        $this->user->syncRoles(RoleEnum::ADMIN->value);
        Sanctum::actingAs($this->user);
    }

    /** @throws Throwable */
    public function test_it_can_fetch_all_roles(): void
    {
        $response = $this->get($this->baseUri);
        $response->assertStatus(200);

        $response = $response->decodeResponseJson();

        // We seed the ff: standard_user, admin, system_support, super_user
        $this->assertCount(4, $response['data']);
    }
}
