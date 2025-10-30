<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Municipality;
use App\Models\Province;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $addresses = json_decode(file_get_contents(resource_path('data/provinces_with_municipalities_in_nepali.json')), true);
        foreach ($addresses as $province => $districts) {
            $newProvince = Province::updateOrCreate([
                'name' => $province,
            ], [
                'name' => $province,
            ]);
            foreach ($districts as $district => $municipalities) {
                $newDistrict = District::updateOrCreate([
                    'name' => $district,
                    'province_id' => $newProvince->id
                ], [
                    'name' => $district,
                    'province_id' => $newProvince->id
                ]);
                foreach ($municipalities as $municipality => $wards) {
                    Municipality::updateOrCreate([
                        'name' => $municipality,
                        'district_id' => $newDistrict->id
                    ], [
                        'name' => $municipality,
                        'district_id' => $newDistrict->id,
                        'no_of_wards' => count($wards)
                    ]);
                }
            }
        }
    }
}
