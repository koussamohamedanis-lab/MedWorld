<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Consultation;
use Illuminate\Support\Facades\Validator;

class ConsultationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->attributes->get('user');
        $query = Consultation::with(['patient.user', 'doctor.user', 'appointment']);

        if ($user && $user->type !== 'superadmin') {
            $cabinetId = $this->getCurrentUserCabinetId($user);
            if ($cabinetId) {
                $query->whereHas('doctor', function ($q) use ($cabinetId) {
                    $q->where('cabinetId', $cabinetId);
                });
            }
        }

        $consultations = $query->orderBy('created_at', 'desc')->get();
        return response()->json(['data' => $consultations]);
    }

    public function show($id)
    {
        $consultation = Consultation::with(['patient.user', 'doctor.user', 'appointment'])->find($id);

        if (!$consultation) {
            return response()->json(['message' => 'Consultation not found'], 404);
        }

        return response()->json($consultation);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doctorId' => 'required|exists:doctors,id',
            'patientId' => 'required|exists:patients,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $consultation = Consultation::create($request->only([
            'doctorId',
            'patientId',
            'appointmentId',
            'notes',
            'prescriptions',
            'attachments'
        ]));

        $consultation->load(['patient.user', 'doctor.user', 'appointment']);
        return response()->json($consultation, 201);
    }

    public function update(Request $request, $id)
    {
        $consultation = Consultation::find($id);

        if (!$consultation) {
            return response()->json(['message' => 'Consultation not found'], 404);
        }

        $consultation->update($request->only([
            'notes',
            'prescriptions',
            'attachments'
        ]));

        $consultation->load(['patient.user', 'doctor.user', 'appointment']);
        return response()->json($consultation);
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
