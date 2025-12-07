<?php

use App\Services\OllamaService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('chat');
});
Route::post('/chat', [OllamaService::class, 'chatSystem']);
