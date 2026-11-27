<?php

use App\Libraries\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: '',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Default given by Laravel, but we want to make sure that if the request is for an API route, 
        // we return JSON instead of HTML.
        // If the request is for an API route (like 'v1/*'), we want to return JSON responses for exceptions.
        // This callback stops here if the request is not for an API route, allowing Laravel to handle it normally.
        // Which will not execute the below exception renderers, and will instead return the default HTML error pages.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('v1/*') || $request->expectsJson(),
        );

        // For validation errors
        $exceptions->render(function (ValidationException $e) {
            return ApiResponse::error($e->errors(), __('general.validation_failed'), 422);
        });

        // Model (resource) not found errors
        $exceptions->render(function (ModelNotFoundException $e) {
            return ApiResponse::error(null, __('general.not_found'), 404);
        });

        // Auth errors
        $exceptions->render(function (AuthenticationException $e) {
            return ApiResponse::error(null, __('general.unauthenticated'), 401);
        });

        // Authorization errors
        $exceptions->render(function (AuthorizationException $e) {
            return ApiResponse::error(null, __('general.unauthorized'), 403);
        });
        
        // Catch all other exceptions
        $exceptions->render(function (Throwable $e) {
            $message = app()->environment('local') ? $e->getMessage() : __('general.something_went_wrong');
            $code = $e->getCode() ?? 500;
            return ApiResponse::error(null, $message, $code);
        });
    })->create();
