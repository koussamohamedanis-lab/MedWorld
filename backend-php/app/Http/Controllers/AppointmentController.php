<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Helpers\UserHelper;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->attributes->get('user');
        $query = Appointment::with(['patient.user', 'doctor.user', 'cabinet']);

        if ($user && $user->type !== 'superadmin') {
            $cabinetId = $this->getCurrentUserCabinetId($user);
            if ($cabinetId) {
                $query->where('cabinetId', $cabinetId);
            }
        }

        $appointments = $query->orderBy('date', 'desc')->get();
        return response()->json(['data' => $this->transformAppointments($appointments)]);
    }

    public function show($id)
    {
        $appointment = Appointment::with(['patient.user', 'doctor.user', 'cabinet'])->find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found'], 404);
        }

        $transformed = $this->transformAppointments([$appointment]);
        return response()->json($transformed[0] ?? []);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'patientId' => 'required|exists:patients,id',
            'doctorId' => 'required|exists:doctors,id',
            'cabinetId' => 'required|exists:cabinets,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $appointment = Appointment::create([
            'date' => $request->date,
            'status' => Appointment::STATUS_SCHEDULED,
            'patientId' => $request->patientId,
            'doctorId' => $request->doctorId,
            'cabinetId' => $request->cabinetId,
        ]);

        $appointment->load(['patient.user', 'doctor.user', 'cabinet']);
        $transformed = $this->transformAppointments([$appointment]);
        return response()->json($transformed[0] ?? [], 201);
    }

    public function update(Request $request, $id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found'], 404);
        }

        $appointment->update($request->only(['date', 'status']));
        $appointment->load(['patient.user', 'doctor.user', 'cabinet']);
        $transformed = $this->transformAppointments([$appointment]);
        return response()->json($transformed[0] ?? []);
    }

    public function destroy($id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found'], 404);
        }

        $appointment->delete();
        return response()->json(['message' => 'Appointment deleted successfully']);
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

    private function transformAppointments($appointments)
    {
        $result = [];
        foreach ($appointments as $apt) {
            $aptData = [
                'id' => $apt->id,
                'date' => $apt->date,
                'status' => $apt->status,
                'createdAt' => $apt->created_at,
                'updatedAt' => $apt->updated_at,
            ];

            if ($apt->patient && $apt->patient->user) {
                $aptData['patient'] = UserHelper::getUserWithExtendedInfo($apt->patient->user);
                $aptData['patientId'] = $apt->patientId;
            }

            if ($apt->doctor && $apt->doctor->user) {
                $aptData['doctor'] = UserHelper::getUserWithExtendedInfo($apt->doctor->user);
                $aptData['doctorId'] = $apt->doctorId;
            }

            if ($apt->cabinet) {
                $aptData['cabinet'] = $apt->cabinet;
                $aptData['cabinetId'] = $apt->cabinetId;
            }

            $result[] = $aptData;
        }
        return $result;
    }
}
