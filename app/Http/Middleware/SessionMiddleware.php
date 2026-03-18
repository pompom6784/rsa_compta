<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Session middleware — kept as a named middleware for named-route compatibility.
 * Laravel's session handling is managed natively by the framework's
 * StartSession middleware; this class is a pass-through that preserves the
 * original middleware name in the stack.
 */
class SessionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
