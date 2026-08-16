<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class WebAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('auth_token') || !session('auth_user')) {
            return redirect()->route('login')->with('error', 'Please log in to continue.');
        }
        return $next($request);
    }
}
