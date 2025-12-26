<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Calendar;
use Illuminate\Support\Facades\Validator;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->attributes->get('user');
        $query = Calendar::with(['doctor.user', 'cabinet']);

        if ($user && $user->type !== 'superadmin') {
            $cabinetId = $this->getCurrentUserCabinetId($user);
            if ($cabinetId) {
                $query->where('cabinetId', $cabinetId);
            }
        }

        $calendars = $query->get();
        return response()->json(['data' => $calendars]);
    }

    public function show($id)
    {
        $calendar = Calendar::with(['doctor.user', 'cabinet'])->find($id);

        if (!$calendar) {
            return response()->json(['message' => 'Calendar not found'], 404);
        }

        return response()->json($calendar);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doctorId' => 'required|exists:doctors,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $calendar = Calendar::create($request->only(['doctorId', 'cabinetId', 'availability']));
        $calendar->load(['doctor.user', 'cabinet']);
        return response()->json($calendar, 201);
    }

    public function update(Request $request, $id)
    {
        $calendar = Calendar::find($id);

        if (!$calendar) {
            return response()->json(['message' => 'Calendar not found'], 404);
        }

        $calendar->update($request->only(['availability']));
        $calendar->load(['doctor.user', 'cabinet']);
        return response()->json($calendar);
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
