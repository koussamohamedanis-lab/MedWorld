<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserTypeMiddleware
{
    public function handle(Request $request, Closure $next, ...$types): Response
    {
        $user = $request->attributes->get('user');

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if (in_array($user->type, $types)) {
            return $next($request);
        }

        return response()->json(['message' => "You don't have permission to perform this action"], 403);
    }
}
