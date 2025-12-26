<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Helpers\UserHelper;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $userData = UserHelper::getUserWithExtendedInfo($user);
        return response()->json($userData);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $data = $request->except(['password', 'email', 'type']);

        if ($request->has('password') && !empty($request->password)) {
            $data['password'] = $request->password;
        }

        $user->update($data);

        $userData = UserHelper::getUserWithExtendedInfo($user);
        return response()->json($userData);
    }
}
