<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = \App\Models\Category::all();

        if ($categories->isEmpty()) {
            $this->command->info('No categories found, seeding categories first...');
            $this->call(CategorySeeder::class);
            $categories = \App\Models\Category::all();
        }

        \App\Models\Event::factory()->count(100)->create()->each(function ($event) use ($categories) {
            // Attach random categories to each event
            $event->categories()->attach(
                $categories->random(rand(1, 3))->pluck('id')->toArray()
            );
        });
    }
}
