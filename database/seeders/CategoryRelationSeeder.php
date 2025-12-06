<?php

namespace Database\Seeders;

use App\Enums\OllamaModel;
use App\Models\Category;
use App\Models\CategoryRelation;
use App\Services\OllamaService;
use Illuminate\Database\Seeder;
use Nette\Utils\Json;

class CategoryRelationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::select('id', 'name')->orderBy('id')->get()->toJson();
        $ollamaService = new OllamaService();

        $prompt = "You are a category relation assistant. From the following categories, provide the relatedness score between all pairs of categories from 0.00 to 1.00.
The relation is bidirectional, meaning if category A is related to category B, then category B is also related to category A, so only provide one direction for each pair.

The output must strictly follow this JSON format (as an array of objects):

[
    {
        \"category_a_id\": 1,
        \"category_b_id\": 2,
        \"relatedness\": 0.5
    }
]

Here are the categories: $categories

Use the correct category IDs from the list and provide a relatedness object for every unique pair of categories. Do not include duplicate pairs or self-relations.";

        $recommendation = $ollamaService->getCategoryRelations($prompt, OllamaModel::GEMMA3_4B->value);
        if ($recommendation && count($recommendation) > 0) {
            foreach ($recommendation as $relation) {
                CategoryRelation::updateOrCreate([
                    'category_a_id' => $relation['category_a_id'],
                    'category_b_id' => $relation['category_b_id'],
                ], [
                    'relatedness' => $relation['relatedness'],
                ]);
            }
        }
    }
}
