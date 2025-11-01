<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = json_decode(file_get_contents(resource_path('data/categories.json')), true);
        foreach ($categories as $category) {
            Category::updateOrCreate([
                'id' => $category['id']
            ], [
                'name' => $category['name'],
                'description' => $category['description'],
                'slug' => $category['slug'],
            ]);
        }
    }
}
