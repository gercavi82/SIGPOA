<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $hasAny = collect($permissions)
            ->flatMap(fn (string $permission) => explode('|', $permission))
            ->contains(fn (string $permission) => (bool) $request->user()?->hasPermission(trim($permission)));

        abort_unless($hasAny, 403);

        return $next($request);
    }
}
