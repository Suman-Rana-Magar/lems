<?php

use App\Http\Middleware\isEmailVerified;
use App\Http\Middleware\RoleWiseAccessMiddleware;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Queue\InvalidPayloadException;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleWiseAccessMiddleware::class,
            'isEmailVerified' => isEmailVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ModelNotFoundException $e, $request) {
            $model = strtolower(class_basename($e->getModel()));
            $message = "No {$model} found with given data.";
            return response()->json(['errors' => $message], 404);
        });
        $exceptions->render(function (ItemNotFoundException $e) {
            return response()->json(['errors' => 'No item found with given data'], 404);
        });
        $exceptions->render(function (NotFoundHttpException $e) {
            return response()->json(['errors' => 'Url Not Found'], 404);
        });
        $exceptions->render(function (PostTooLargeException $e) {
            return response()->json(['errors' => 'File too large'], 413);
        });
        $exceptions->render(function (ValidationException $e) {
            $errors = $e->validator->errors();
            $messages = [];
            foreach ($errors->messages() as $key => $message) {
                $messages[$key] = $message[0];
            }
            $response = [
                'errors' => $messages,
            ];
            return response()->json($response, 422);
        });
        $exceptions->render(function (InvalidPayloadException $e) {
            return response()->json(['errors' => $e->getMessage()], 410);
        });
        $exceptions->render(function (InvalidFormatException $e) {
            return response()->json(['errors' => 'Invalid Format'], 400);
        });
        $exceptions->render(function (AuthenticationException $e) {
            return response()->json(['errors' => 'Unauthenticated!!'], 401);
        });
    })->create();
