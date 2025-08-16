<?php

namespace Modules\Task\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TaskMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }
}
