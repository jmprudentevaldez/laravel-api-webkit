<?php

namespace Database\Seeders;

use App\Models\Address\Province;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ProvincesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rawData = file_get_contents(base_path('database/seeders/dumps/psgc_provinces_1q23.json'));
        $provincesJson = json_decode($rawData, true);

        $provinces = [];
        foreach ($provincesJson as $province) {
            $provinces[] = [
                'id' => $province['prov_id'],
                'code' => $province['code'],
                'name' => $province['name'],
                'code_correspondence' => $province['code_correspondence'],
                'geo_level' => $province['geo_level'],
                'region_id' => $province['reg_id'],
                'old_name' => $province['old_name'],
                'income_classification' => $province['income_classification'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        Province::insert($provinces);
    }
}
