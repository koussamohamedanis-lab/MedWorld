<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->attributes->get('user');

        $messages = Message::where('receiverId', $user->id)
            ->orWhere('senderId', $user->id)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $messages]);
    }

    public function recipients(Request $request)
    {
        $user = $request->attributes->get('user');
        $cabinetId = $this->getCurrentUserCabinetId($user);

        $query = User::where('id', '!=', $user->id);

        if ($user->type !== 'superadmin' && $cabinetId) {
            $query->where(function ($q) use ($cabinetId, $user) {
                $q->whereHas('doctor', function ($dq) use ($cabinetId) {
                    $dq->where('cabinetId', $cabinetId);
                })->orWhereHas('assistant', function ($aq) use ($cabinetId) {
                    $aq->where('cabinetId', $cabinetId);
                });
            });
        }

        $users = $query->get();
        $result = [];
        foreach ($users as $u) {
            $result[] = [
                'id' => $u->id,
                'firstName' => $u->firstName,
                'lastName' => $u->lastName,
                'fullName' => $u->fullName,
                'email' => $u->email,
                'type' => $u->type,
                'avatarUrl' => $u->avatarUrl,
            ];
        }

        return response()->json(['data' => $result]);
    }

    public function store(Request $request)
    {
        $user = $request->attributes->get('user');

        $validator = Validator::make($request->all(), [
            'receiverId' => 'required|exists:users,id',
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $message = Message::create([
            'senderId' => $user->id,
            'receiverId' => $request->receiverId,
            'content' => $request->content,
            'isRead' => false,
        ]);

        $message->load(['sender', 'receiver']);
        return response()->json($message, 201);
    }

    private function getCurrentUserCabinetId($user)
    {
        if (!$user)
            return null;

        if ($user->type === 'admin' || $user->type === 'doctor') {
            $doctor = \App\Models\Doctor::where('userId', $user->id)->first();
            return $doctor ? $doctor->cabinetId : null;
        }

        if ($user->type === 'assistant') {
            $assistant = \App\Models\Assistant::where('userId', $user->id)->first();
            return $assistant ? $assistant->cabinetId : null;
        }

        return null;
    }
}
