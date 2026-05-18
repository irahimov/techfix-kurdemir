<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Yalnız admin və agent rollu istifadəçilərə icazə ver.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || (! $user->isAdmin() && ! $user->isAgent())) {
            abort(403, 'Bu səhifəyə giriş icazəniz yoxdur.');
        }

        return $next($request);
    }
}