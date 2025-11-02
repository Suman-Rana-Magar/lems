<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ProvinceSeeder extends Seeder
{
    public function run(): void
    {
        $provinces = json_decode(file_get_contents(resource_path('data/nepal_location_en.json')), true);

        foreach ($provinces as $province) {
            // Insert or Update Province
            DB::table('provinces')->updateOrInsert(
                ['id' => $province['id']], // match condition
                [
                    'name' => $province['name'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            // Insert or Update Districts
            foreach ($province['districts'] as $district) {
                DB::table('districts')->updateOrInsert(
                    ['id' => $district['id']],
                    [
                        'province_id' => $province['id'],
                        'name' => $district['name'],
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );

                // Municipalities may be array or object
                $municipalities = is_array($district['municipalities'])
                    ? $district['municipalities']
                    : array_values($district['municipalities']);

                foreach ($municipalities as $municipality) {
                    DB::table('municipalities')->updateOrInsert(
                        ['id' => $municipality['id']],
                        [
                            'district_id' => $district['id'],
                            'name' => $municipality['name'],
                            'no_of_wards' => isset($municipality['wards'])
                                ? count($municipality['wards'])
                                : 0,
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }
            }
        }
    }
}
