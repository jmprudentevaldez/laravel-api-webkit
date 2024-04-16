<?php

namespace Database\Seeders;

use App\Enums\MunicipalClassification;
use App\Models\Address\City;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rawData = file_get_contents(base_path('database/seeders/dumps/psgc_cities_1q23.json'));
        $citiesJson = json_decode($rawData, true);

        $cities = [];
        foreach ($citiesJson as $city) {
            $cities[] = [
                'id' => $city['city_id'],
                'code' => $city['code'],
                'name' => $city['name'],
                'code_correspondence' => $city['code_correspondence'],
                'classification' => $city['classification'] === 'MUNICIPALITY'
                    ? MunicipalClassification::MUNICIPALITY : MunicipalClassification::CITY,
                'old_name' => $city['old_name'],
                'city_class' => $city['city_class'],
                'income_classification' => $city['income_classification'],
                'province_id' => $city['prov_id'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        City::insert($cities);
    }
}
