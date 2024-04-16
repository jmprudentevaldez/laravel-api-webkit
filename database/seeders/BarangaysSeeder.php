<?php

namespace Database\Seeders;

use App\Enums\BarangayClassification;
use App\Models\Address\Barangay;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BarangaysSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rawData = file_get_contents(base_path('database/seeders/dumps/psgc_barangays_1q23.json'));
        $barangaysJson = json_decode($rawData, true);

        // We just need to seed a few if we're running tests
        if (app()->runningUnitTests()) {
            $barangaysJson = array_slice($barangaysJson, 0, 200);
        }

        $barangays = [];
        foreach ($barangaysJson as $barangay) {
            $barangays[] = [
                'id' => $barangay['brgy_id'],
                'code' => $barangay['code'],
                'name' => $barangay['name'],
                'code_correspondence' => $barangay['code_correspondence'],
                'classification' => $barangay['urb_rur'] === 'R' ? BarangayClassification::RURAL : BarangayClassification::URBAN,
                'city_id' => $barangay['city_id'],
                'old_name' => $barangay['old_name'],
                'geo_level' => strtolower($barangay['geo_level']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];

            // We insert every 200 records loaded in-memory then clear to prevent memory exhaustion
            if (count($barangays) >= 200) {
                Barangay::insert($barangays);
                $barangays = [];
            }
        }

        Barangay::insert($barangays);
    }
}
