<?php

namespace Tests\Feature;

use App\Models\Address\City;
use App\Models\Address\Province;
use App\Models\Address\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Throwable;

class AddressTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
    }

    private string $baseUri = self::BASE_API_URI.'/address';

    public function test_it_can_fetch_all_regions(): void
    {
        $response = $this->getJson($this->baseUri.'/regions');
        $response->assertStatus(200);
    }

    /** @throws Throwable */
    public function test_it_can_filter_regions_via_code(): void
    {
        // We search for BARMM (code=1900000000)
        $response = $this->getJson($this->baseUri.'/regions?code=1900000000');
        $response = $response->decodeResponseJson();
        $this->assertCount(1, $response['data']);
    }

    public function test_it_can_fetch_all_provinces(): void
    {
        $response = $this->getJson($this->baseUri.'/provinces');
        $response->assertStatus(200);
    }

    /** @throws Throwable */
    public function test_it_can_filter_provinces_via_code(): void
    {
        // We search for Abra (code=1400100000)
        $response = $this->getJson($this->baseUri.'/provinces?code=1400100000');
        $response = $response->decodeResponseJson();
        $this->assertCount(1, $response['data']);
    }

    /** @throws Throwable */
    public function test_it_can_filter_provinces_via_region_id(): void
    {
        // We search for provinces belonging to CAR
        $carRegion = Region::where('code_correspondence', '1400000000')->first();
        $response = $this->getJson($this->baseUri."/provinces?region=$carRegion->id");
        $response = $response->decodeResponseJson();

        // CAR has 6 provinces under it
        $this->assertCount(6, $response['data']);
    }

    public function test_it_can_fetch_all_cities(): void
    {
        $response = $this->getJson($this->baseUri.'/cities');
        $response->assertStatus(200);
    }

    /** @throws Throwable */
    public function it_can_filter_cities_via_code(): void
    {
        // We search for Angeles City
        $response = $this->getJson($this->baseUri.'/cities?code=0330100000');
        $response = $response->decodeResponseJson();

        $this->assertCount(1, $response['data']);
    }

    public function it_can_fetch_all_barangays(): void
    {
        $response = $this->getJson($this->baseUri.'/barangays');
        $response->assertStatus(200);
    }

    /**
     * @throws Throwable
     */
    public function it_can_filter_barangays_via_code(): void
    {
        // We search for A. Rivera
        $response = $this->getJson($this->baseUri.'/barangays?code=0300805001');
        $response = $response->decodeResponseJson();

        $this->assertCount(1, $response['data']);
    }

    /**
     * @throws Throwable
     */
    public function it_can_filter_barangays_via_city_id(): void
    {
        $angelesCity = City::where('code_correspondence', '0330100000')->first();
        $response = $this->getJson($this->baseUri."/barangays?city=$angelesCity->id");
        $response = $response->decodeResponseJson();

        foreach ($response['data'] as $barangay) {
            $this->assertEquals($barangay['city_id'], $angelesCity->id);
        }
    }

    /** @throws Throwable */
    public function it_can_filter_cities_via_province_id(): void
    {
        // We search for Metro Manila
        $metroManila = Province::where('code_correspondence', '133900000')->first();
        $response = $this->getJson($this->baseUri."/cities?province=$metroManila->id");
        $response = $response->decodeResponseJson();

        foreach ($response['data'] as $city) {
            $this->assertEquals($city['province_id'], $metroManila->id);
        }
    }

    /** @throws Throwable */
    public function it_can_filter_cities_via_classification_flag(): void
    {
        $responseCities = $this->getJson($this->baseUri.'/cities?classification=city');
        $responseCities = $responseCities->decodeResponseJson();

        foreach ($responseCities['data'] as $city) {
            $this->assertEquals('city', $city['classification']);
        }

        $responseMunicipalities = $this->getJson($this->baseUri.'/cities?classification=municipality');
        $responseMunicipalities = $responseMunicipalities->decodeResponseJson();

        foreach ($responseMunicipalities['data'] as $municipality) {
            $this->assertEquals('municipality', $municipality['classification']);
        }
    }
}
