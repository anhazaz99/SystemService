<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;

class JwtAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        $token = $this->getTokenFromHeader($request);
        if (!$token) {
            return response()->json(['error' => 'Chưa có token được đưa lên Authorization header'], 401);
        }

        $secret = config('jwt.secret');
        $algo   = config('jwt.algorithm', 'HS256');

        if (empty($secret)) {
            return response()->json(['error' => 'JWT secret is not configured'], 500);
        }

        try {
            $decoded = JWT::decode($token, new Key($secret, $algo));
            $payload = (array) $decoded;

            // Kiểm tra hạn token (exp)
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                return response()->json(['error' => 'Token đã hết hạn'], 401);
            }

            // Gắn payload vào request
            $request->attributes->set('jwt_payload', $payload);
            if (isset($payload['sub'])) {
                $request->attributes->set('jwt_sub', $payload['sub']);
            }
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Token không hợp lệ : ' . $e->getMessage()], 401);
        }

        return $next($request);
    }

    private function getTokenFromHeader(Request $request): ?string
    {
        $authorization = $request->header('Authorization', '');
        if (!str_starts_with($authorization, 'Bearer ')) {
            return null;
        }
        $token = substr($authorization, 7);
        return $token !== '' ? $token : null;
    }
}
