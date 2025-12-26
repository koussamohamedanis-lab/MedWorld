<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\JWTHelper;
use App\Models\User;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader) {
            return response()->json(['message' => 'Missing authorization header'], 401);
        }

        $parts = explode(' ', $authHeader);
        if (count($parts) !== 2 || $parts[0] !== 'Bearer') {
            return response()->json(['message' => 'Invalid authorization format'], 401);
        }

        $token = $parts[1];
        $claims = JWTHelper::validateToken($token);

        if (!$claims) {
            return response()->json(['message' => 'Invalid or expired token'], 401);
        }

        $user = User::find($claims->userId);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 401);
        }

        $request->merge(['user' => $user]);
        $request->attributes->set('user', $user);

        return $next($request);
    }
}
