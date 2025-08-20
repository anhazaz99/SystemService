<?php

namespace Modules\Task\app\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TaskMiddleware
{
    /**
     * Xử lý request đến.
     */
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }
}
