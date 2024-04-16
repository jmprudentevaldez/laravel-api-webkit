<?php

namespace Database\Factories\Address;

use App\Models\Address\Address;
use App\Models\Address\Barangay;
use App\Models\Address\City;
use App\Models\Address\Province;
use App\Models\Address\Region;
use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $regionId = Region::first()->id;
        $provinceId = Province::where('region_id', $regionId)->inRandomOrder()->first()->id;
        $cityId = City::where('province_id', $provinceId)->inRandomOrder()->first()->id;
        $barangayId = Barangay::first()->id; // don't run full search since there's too many

        return [
            'user_profile_id' => UserProfile::factory(),
            'home_address' => fake()->streetAddress(),
            'barangay_id' => $barangayId,
            'city_id' => $cityId,
            'province_id' => $provinceId,
            'region_id' => $regionId,
            'postal_code' => fake()->postcode(),
        ];
    }
}
