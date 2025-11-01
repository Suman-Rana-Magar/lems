<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BaseController extends Controller
{
    protected function successResponse($message, $data = null, $code = 200): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function errorResponse($message, $code = 400): JsonResponse
    {
        // return response()->json([
        //     'error' => $message,
        // ], $code);
        throw ValidationException::withMessages([
            'error' => $message,
        ]);
    }
}
