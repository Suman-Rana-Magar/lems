<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrganizerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $municipalityIds = \App\Models\Municipality::pluck('id')->toArray();
        
        for ($i = 1; $i <= 5; $i++) {
            \App\Models\User::updateOrCreate([
                'email' => "organizer{$i}@lems.com",
            ], [
                'name' => "Organizer {$i}",
                'username' => "organizer{$i}",
                'email_verified_at' => now(),
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'phone_no' => '980000000' . $i,
                'phone_no_verified_at' => now(),
                'municipality_id' => $municipalityIds[array_rand($municipalityIds)] ?? 1,
                'ward_no' => rand(1, 15),
                'street' => 'Organizer Street ' . $i,
                'role' => \App\Enums\RoleEnum::ORGANIZER->value
            ]);
        }
    }
}
