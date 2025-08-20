<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware đảm bảo tất cả API responses đều trả về JSON
 * 
 * Middleware này sẽ:
 * - Set Accept header thành application/json
 * - Đảm bảo tất cả responses đều có Content-Type: application/json
 * - Xử lý validation errors để trả về JSON thay vì HTML
 */
class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Đảm bảo request được xử lý như JSON API
        $request->headers->set('Accept', 'application/json');
        
        $response = $next($request);
        
        // Đảm bảo response có Content-Type JSON
        if (!$response->headers->has('Content-Type')) {
            $response->headers->set('Content-Type', 'application/json');
        }
        
        return $response;
    }
}
