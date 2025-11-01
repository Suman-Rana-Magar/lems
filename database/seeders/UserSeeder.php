<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Municipality;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $municipalityId = Municipality::whereName('गुल्मीदरवार गाउँपालिका')->first()?->id;
        $email = 'suman@gmail.com';
        User::updateOrCreate([
            'email' => $email,
        ], [
            'name' => 'Suman Rana',
            'username' => 'sumanrana',
            'email' => $email,
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'phone_no' => '9800001111',
            'profile_picture' => 'profile_pictures/me.webp',
            'remember_token' => Str::random(60),
            'municipality_id' => $municipalityId ? $municipalityId : 1,
            'ward_no' => 2,
            'street' => 'Thulachour',
            'role' => RoleEnum::ADMIN->value
        ]);
    }
}
