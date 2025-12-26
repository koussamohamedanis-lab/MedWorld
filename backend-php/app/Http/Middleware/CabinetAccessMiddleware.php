<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Doctor;
use App\Models\Assistant;

class CabinetAccessMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->attributes->get('user');

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $cabinetId = $request->route('id');

        if (!$cabinetId) {
            return response()->json(['message' => 'Invalid cabinet ID'], 400);
        }

        if ($user->type === 'superadmin') {
            return $next($request);
        }

        if ($user->type === 'admin' || $user->type === 'doctor') {
            $doctor = Doctor::where('userId', $user->id)->first();
            if (!$doctor) {
                return response()->json(['message' => 'Doctor profile not found'], 403);
            }
            if ($doctor->cabinetId == 0 || $doctor->cabinetId != $cabinetId) {
                return response()->json(['message' => 'Forbidden (outside your cabinet)'], 403);
            }
            return $next($request);
        }

        if ($user->type === 'assistant') {
            $assistant = Assistant::where('userId', $user->id)->first();
            if (!$assistant) {
                return response()->json(['message' => 'Assistant profile not found'], 403);
            }
            if ($assistant->cabinetId == 0 || $assistant->cabinetId != $cabinetId) {
                return response()->json(['message' => 'Forbidden (outside your cabinet)'], 403);
            }
            return $next($request);
        }

        return response()->json(['message' => 'Forbidden'], 403);
    }
}
