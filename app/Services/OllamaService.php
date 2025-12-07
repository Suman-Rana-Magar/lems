<?php

namespace App\Services;

use App\Enums\OllamaModel;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

    public function chatSystem(Request $request, OllamaService $ollama)
    {
        $request->validate([
            'message' => 'nullable|string',
            'image'   => 'nullable|string',
            'history' => 'nullable|array',
        ]);

        $input = $request->message ?? '';
        $imageBase64 = $request->image;
        $conversationHistory = $request->history ?? [];

        // Build messages array for conversation history
        $messages = [];
        
        // Add previous conversation history
        foreach ($conversationHistory as $msg) {
            if (isset($msg['role']) && isset($msg['content'])) {
                $messageEntry = [
                    'role' => $msg['role'],
                    'content' => $msg['content']
                ];
                
                // Add image if present in history message
                if (isset($msg['images']) && !empty($msg['images'])) {
                    $messageEntry['images'] = $msg['images'];
                }
                
                $messages[] = $messageEntry;
            }
        }

        // Prepare current user message
        $userMessage = [
            'role' => 'user',
            'content' => $input ?: 'Describe this image.'
        ];

        // Add image if provided
        if ($imageBase64) {
            // Remove data URL prefix if present (e.g., "data:image/jpeg;base64,")
            $imageBase64 = preg_replace('/^data:image\/[a-z]+;base64,/', '', $imageBase64);
            $userMessage['images'] = [$imageBase64];
        }

        $messages[] = $userMessage;

        // Prepare the request payload for chat API
        $payload = [
            'model'    => OllamaModel::GEMMA3_4B->value,
            'messages' => $messages,
            'stream'   => false,
        ];

        try {
            $response = $this->client->post('api/chat', [
                'json' => $payload,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            if (!isset($data['message']['content'])) {
                return response()->json([
                    'user' => $input,
                    'ai'   => 'I apologize, but I\'m having trouble processing your request right now. Please try again in a moment, or rephrase your question.'
                ], 500);
            }

            // Get the response content (keep markdown for frontend formatting)
            $result = trim($data['message']['content']);

            // return the message back to ajax with markdown preserved for formatting
            return response()->json([
                'user' => $input,
                'ai'   => $result
            ]);
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            return response()->json([
                'user' => $input,
                'ai'   => 'I\'m currently unavailable. Please check your connection and try again later.'
            ], 500);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            return response()->json([
                'user' => $input,
                'ai'   => 'I encountered an issue while processing your request. Please try again, or rephrase your question.'
            ], 500);
        } catch (\Exception $e) {
            // Log the error for debugging but show user-friendly message
            Log::error('Chat system error: ' . $e->getMessage());
            
            return response()->json([
                'user' => $input,
                'ai'   => 'Something went wrong on my end. Please try again in a moment. If the problem persists, feel free to rephrase your question.'
            ], 500);
        }
    }
}
