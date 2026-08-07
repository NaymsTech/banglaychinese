<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ($user->is_admin || $user->role === 'admin')) {
            return $next($request);
        }

        return redirect('/dashboard')->with('error', 'You do not have permission to access the admin area.');
    }
}
