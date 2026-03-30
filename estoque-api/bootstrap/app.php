<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, Request $request) {
            if (!$request->is('api/*')) {
                return null;
            }

            if ($e instanceof ValidationException) {
                return null;
            }

            if ($e instanceof AuthenticationException) {
                return response()->json(['message' => 'Nao autenticado.'], 401);
            }

            if ($e instanceof AuthorizationException) {
                return response()->json(['message' => 'Acesso negado.'], 403);
            }

            if ($e instanceof ModelNotFoundException) {
                return response()->json(['message' => 'Recurso nao encontrado.'], 404);
            }

            $errorId = (string) Str::uuid();

            Log::error('API exception', [
                'error_id' => $errorId,
                'exception' => $e,
                'path' => $request->path(),
                'method' => $request->method(),
            ]);

            if ($e instanceof QueryException) {
                return response()->json([
                    'message' => 'Nao foi possivel processar a requisicao.',
                    'error_id' => $errorId,
                ], 500);
            }

            return response()->json([
                'message' => 'Erro interno do servidor.',
                'error_id' => $errorId,
            ], 500);
        });
    })->create();
