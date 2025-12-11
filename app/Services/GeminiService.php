<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected Client $client;
    protected string $apiKey;
    protected string $defaultModel = 'gemini-2.0-flash';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', env('GEMINI_API_KEY', ''));

        if (empty($this->apiKey)) {
            throw new \RuntimeException('Gemini API key missing. Set GEMINI_API_KEY in your .env file.');
        }

        $this->client = new Client([
            'base_uri' => 'https://generativelanguage.googleapis.com/',
            'timeout'  => 60.0,
        ]);
    }

    protected function endpoint(string $model, string $action = 'generateContent'): string
    {
        return sprintf('v1beta/models/%s:%s', $model, $action);
    }

    /* ------------------------------------------------------------
       CATEGORY RELATIONS (JSON OUTPUT)
    ------------------------------------------------------------ */
    public function getCategoryRelations(string $prompt, ?string $model = null): ?array
    {
        $model = $model ?: $this->defaultModel;

        $payload = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ]
        ];

        try {
            $response = $this->client->post($this->endpoint($model), [
                'headers' => [
                    'Content-Type'   => 'application/json',
                    'X-goog-api-key' => $this->apiKey,
                ],
                'json' => $payload,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!$text) return null;

            // Remove ```json ... ``` if included
            $cleaned = preg_replace('/^```json\s*|\s*```$/', '', trim($text));

            return json_decode($cleaned, true);
        } catch (\Exception $e) {
            Log::error('Gemini getCategoryRelations error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /* ------------------------------------------------------------
       CHAT SYSTEM (MESSAGES + IMAGES)
    ------------------------------------------------------------ */
    public function chatSystem(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string',
            'image'   => 'nullable|string',
            'history' => 'nullable|array',
        ]);

        $input = $request->message ?? '';
        $imageBase64 = $request->image;
        $history = $request->history ?? [];

        $contents = [];

        // Rebuild history
        foreach ($history as $msg) {
            if (!isset($msg['content'])) continue;

            $parts = [
                ["text" => $msg['content']]
            ];

            if (!empty($msg['images'])) {
                foreach ($msg['images'] as $img) {
                    $parts[] = [
                        "inline_data" => [
                            "mime_type" => "image/jpeg",
                            "data"      => $img,
                        ],
                    ];
                }
            }

            $contents[] = [
                "parts" => $parts
            ];
        }

        // New user message
        $userParts = [
            ["text" => $input ?: "Describe this image."]
        ];

        if ($imageBase64) {
            $imageBase64 = preg_replace('/^data:image\/[a-z]+;base64,/', '', $imageBase64);
            $userParts[] = [
                "inline_data" => [
                    "mime_type" => "image/jpeg",
                    "data"      => $imageBase64,
                ],
            ];
        }

        $contents[] = [
            "parts" => $userParts
        ];

        $payload = [
            "contents" => $contents,
            "generationConfig" => [
                "temperature" => 0.7,
                "topP"        => 0.95
            ]
        ];

        try {
            $response = $this->client->post($this->endpoint($this->defaultModel), [
                'headers' => [
                    'Content-Type'   => 'application/json',
                    'X-goog-api-key' => $this->apiKey,
                ],
                'json' => $payload,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $result = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!$result) {
                return response()->json([
                    'user' => $input,
                    'ai'   => "I'm having trouble right now. Try again shortly."
                ], 500);
            }

            return response()->json([
                'user' => $input,
                'ai'   => trim($result),
            ]);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $status = optional($e->getResponse())->getStatusCode();
            $body   = optional($e->getResponse())->getBody()?->getContents();

            Log::error('Gemini chat request error', [
                'status' => $status,
                'body'   => $body,
                'message' => $e->getMessage(),
            ]);

            if ($status === 429) {
                return response()->json([
                    'user' => $input,
                    'ai'   => "You're being rate limited. Try again soon."
                ], 429);
            }

            return response()->json([
                'user' => $input,
                'ai'   => "Gemini upstream error. Status: " . ($status ?? 'N/A'),
                'details' => $body ? mb_strimwidth($body, 0, 500, "...") : null,
            ], 500);
        } catch (\Exception $e) {
            Log::error('Gemini chat fatal error: ' . $e->getMessage());

            return response()->json([
                'user' => $input,
                'ai'   => "Something went wrong. Try again later."
            ], 500);
        }
    }

    public function chatWithGemini(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string',
            'image'   => 'nullable|string',
            'history' => 'nullable|array',
        ]);

        $input = $request->message ?? '';
        $imageBase64 = $request->image;
        $conversationHistory = $request->history ?? [];

        $apiKey = "AIzaSyAbqohyvBXqx4teOhqgbOiWxLsyutTm_3w"; // YOUR KEY
        $model  = "gemini-2.0-flash";

        $client = new \GuzzleHttp\Client([
            "base_uri" => "https://generativelanguage.googleapis.com/"
        ]);

        // -----------------------------------------------------
        // Build "contents" array for Gemini
        // -----------------------------------------------------
        $contents = [];

        // Add past conversation
        foreach ($conversationHistory as $msg) {
            $parts = [
                ["text" => $msg["content"] ?? ""]
            ];

            if (!empty($msg["images"])) {
                foreach ($msg["images"] as $img) {
                    $parts[] = [
                        "inline_data" => [
                            "mime_type" => "image/jpeg",
                            "data" => $img
                        ]
                    ];
                }
            }

            $contents[] = [
                "parts" => $parts
            ];
        }

        // -----------------------------------------------------
        // Add current user message
        // -----------------------------------------------------
        $userParts = [
            ["text" => $input ?: "Describe this image."]
        ];

        if ($imageBase64) {
            $imageBase64 = preg_replace('/^data:image\/[a-z]+;base64,/', '', $imageBase64);
            $userParts[] = [
                "inline_data" => [
                    "mime_type" => "image/jpeg",
                    "data" => $imageBase64
                ]
            ];
        }

        $contents[] = [
            "parts" => $userParts
        ];

        $payload = [
            "contents" => $contents,
            "generationConfig" => [
                "temperature" => 0.7,
                "topP"        => 0.95
            ]
        ];

        // -----------------------------------------------------
        // Make API request
        // -----------------------------------------------------
        try {
            $response = $client->post(
                "v1beta/models/$model:generateContent",
                [
                    "headers" => [
                        "Content-Type"   => "application/json",
                        "X-goog-api-key" => $apiKey
                    ],
                    "json" => $payload
                ]
            );

            $data = json_decode($response->getBody()->getContents(), true);

            $output = $data["candidates"][0]["content"]["parts"][0]["text"] ?? null;

            if (!$output) {
                return response()->json([
                    "user" => $input,
                    "ai"   => "I couldn’t process that. Please try again."
                ], 500);
            }

            return response()->json([
                "user" => $input,
                "ai"   => trim($output)
            ]);
        }

        // -----------------------------------------------------
        // Error handling
        // -----------------------------------------------------
        catch (\GuzzleHttp\Exception\RequestException $e) {
            return response()->json([
                "user" => $input,
                "ai"   => "Request error: please try again.",
                "error" => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                "user" => $input,
                "ai"   => "Something went wrong on my end.",
                "error" => $e->getMessage()
            ], 500);
        }
    }
}
