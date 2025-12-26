<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Assistant;
use App\Helpers\JWTHelper;
use App\Helpers\UserHelper;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->checkPassword($request->password)) {
            return response()->json(['message' => 'Invalid email or password'], 401);
        }

        $token = JWTHelper::generateToken($user->id, $user->type);

        $userData = UserHelper::getUserWithExtendedInfo($user);

        return response()->json([
            'user' => $userData,
            'token' => $token,
        ]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string',
            'lastName' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'type' => 'required|in:superadmin,admin,doctor,assistant,patient',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        if (User::where('email', $request->email)->exists()) {
            return response()->json(['message' => 'Email already registered'], 409);
        }

        $user = User::create($request->only([
            'firstName',
            'lastName',
            'email',
            'password',
            'phoneNumber',
            'address',
            'gender',
            'dateOfBirth',
            'type'
        ]));

        switch ($user->type) {
            case 'patient':
                Patient::create(['userId' => $user->id]);
                break;
            case 'doctor':
            case 'admin':
                Doctor::create(['userId' => $user->id]);
                break;
            case 'assistant':
                Assistant::create(['userId' => $user->id]);
                break;
        }

        $token = JWTHelper::generateToken($user->id, $user->type);
        $userData = UserHelper::getUserWithExtendedInfo($user);

        return response()->json([
            'user' => $userData,
            'token' => $token,
        ], 201);
    }

    public function me(Request $request)
    {
        $user = $request->attributes->get('user');
        $userData = UserHelper::getUserWithExtendedInfo($user);

        return response()->json(['user' => $userData]);
    }

    public function logout()
    {
        return response()->json(['message' => 'Logged out successfully']);
    }
}
