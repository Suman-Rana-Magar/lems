<?php

namespace App\Services;

use GuzzleHttp\Client;

class OllamaService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'http://172.26.0.1:11434/',
            'timeout'  => 60.0, // Set a reasonable timeout
        ]);
    }

    public function getCategoryRelations(string $prompt, string $model = 'gemma3:1b'): ?array
    {
        try {
            $response = $this->client->post('api/generate', [
                'json' => [
                    'model'  => $model,
                    'prompt' => $prompt,
                    'stream' => false,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (!isset($data['response'])) {
                return null;
            }

            // Remove the ```json and extra quotes if present
            $cleaned = trim($data['response']);
            $cleaned = preg_replace('/^```json\s*|\s*```$/', '', $cleaned);

            // Decode the cleaned JSON string
            $jsonArray = json_decode($cleaned, true);

            return $jsonArray; // returns as proper PHP array
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
