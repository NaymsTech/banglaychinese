<?php

use App\Support\WebRoot;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Admin access is enforced by Filament via the User model's
        // canAccessPanel() method — no route middleware needed.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (TooManyRequestsHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Too many requests. Please try again later.',
                ], 429);
            }

            return back()->withErrors(['throttle' => 'Too many requests. Please try again later.']);
        });
    })->create();

// Shared-hosting deployments keep the web root as a sibling of the app root
// (Hostinger: laravel-app/ next to public_html/), so Laravel must not assume
// the public directory is <base>/public. Resolve it as soon as the environment
// is loaded — public_path(), Vite's manifest lookup and `storage:link` then all
// target the real web root. APP_PUBLIC_PATH may override the detected layout.
$app->afterLoadingEnvironment(function () use ($app): void {
    $app->usePublicPath(WebRoot::resolve($app->basePath(), env('APP_PUBLIC_PATH')));
});

return $app;
