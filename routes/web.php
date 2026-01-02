<?php

use App\Services\GeminiService;
use App\Services\OllamaService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('chat');
});
Route::post('/chat', [OllamaService::class, 'chatSystem']);
// Route::post('/chat', [GeminiService::class, 'chatWithGemini']);


