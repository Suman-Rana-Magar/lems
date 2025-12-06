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
        $categories = Category::select('id', 'name')->orderBy('id')->get();
        $ollamaService = new OllamaService();

        $categoryCount = count($categories); // e.g., 7
        $expectedPairs = $categoryCount * ($categoryCount - 1) / 2;

        $prompt = "You are a category relation assistant. From the following categories, provide the relatedness score between all pairs of categories from 0.00 to 1.00.

Rules:
1. Include each unique pair once: only category_a_id → category_b_id where category_a_id < category_b_id.
2. Do not include the reverse pair (B->A) or self-relations.
3. You must return exactly $expectedPairs objects — one for each unique pair.
4. Provide ONLY JSON, no extra text or explanations.

The output must strictly follow this JSON format (as an array of objects):

[
    {
        \"category_a_id\": 1,
        \"category_b_id\": 2,
        \"relatedness\": 0.5
    }
]

Here are the categories: $categories

Provide a relatedness object for every unique pair of categories. The output MUST contain exactly $expectedPairs items.";

dd($prompt);
        $recommendation = $ollamaService->getCategoryRelations($prompt, OllamaModel::GEMMA3_1B->value);
        if ($recommendation && count($recommendation) > 0 && is_array($recommendation)) {
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
