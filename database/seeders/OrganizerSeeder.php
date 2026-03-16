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
        $organizers = [
            ['name' => 'Nepal Tech Community', 'email' => 'contact@ntc.org.np', 'username' => 'nepaltech'],
            ['name' => 'Valley Events & Media', 'email' => 'info@valleyevents.com', 'username' => 'valleyevents'],
            ['name' => 'Himalayan Arts Council', 'email' => 'arts@himalayan.org', 'username' => 'himalayanarts'],
            ['name' => 'Kathmandu Music Group', 'email' => 'rock@kmg.com.np', 'username' => 'kmg_music'],
            ['name' => 'Youth Innovations Nepal', 'email' => 'hello@youthinn.org', 'username' => 'youthinnovations'],
        ];
        
        foreach ($organizers as $org) {
            \App\Models\User::updateOrCreate([
                'email' => $org['email'],
            ], [
                'name' => $org['name'],
                'username' => $org['username'],
                'email_verified_at' => now(),
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'phone_no' => '98' . rand(11111111, 99999999),
                'phone_no_verified_at' => now(),
                'municipality_id' => $municipalityIds[array_rand($municipalityIds)] ?? 1,
                'ward_no' => rand(1, 15),
                'street' => 'Commercial St, Kathmandu',
                'role' => \App\Enums\RoleEnum::ORGANIZER->value
            ]);
        }
    }
}
