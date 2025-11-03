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
        $municipalityId = Municipality::whereName('Gulmidarbar Rural Municipality')->first()?->id;
        $email = 'admin@lems.com';
        User::updateOrCreate([
            'email' => $email,
        ], [
            'name' => 'Admin',
            'username' => 'admin',
            'email' => $email,
            'email_verified_at' => now(),
            'password' => Hash::make('L3M$@DM!n'),
            'phone_no' => '9800001111',
            'phone_no_verified_at' => now(),
            'profile_picture' => 'profile_pictures/anonymus.jpg',
            'remember_token' => Str::random(60),
            'municipality_id' => $municipalityId ? $municipalityId : 1,
            'ward_no' => 2,
            'street' => 'Thulachour',
            'role' => RoleEnum::ADMIN->value
        ]);
    }
}
