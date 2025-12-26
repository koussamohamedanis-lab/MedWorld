<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Helpers\UserHelper;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->attributes->get('user');
        $query = Patient::with('user');

        if ($user && $user->type !== 'superadmin') {
            $cabinetId = $this->getCurrentUserCabinetId($user);
            if ($cabinetId) {
                $query->whereHas('user', function ($q) use ($cabinetId) {
                });
            }
        }

        $patients = $query->get();
        $result = [];
        foreach ($patients as $patient) {
            if ($patient->user) {
                $result[] = UserHelper::getUserWithExtendedInfo($patient->user);
            }
        }

        return response()->json(['data' => $result]);
    }

    public function show($id)
    {
        $patient = Patient::with('user')->find($id);

        if (!$patient) {
            return response()->json(['message' => 'Patient not found'], 404);
        }

        return response()->json(UserHelper::getUserWithExtendedInfo($patient->user));
    }

    public function appointments(Request $request, $id)
    {
        $appointments = Appointment::where('patientId', $id)
            ->with(['patient.user', 'doctor.user', 'cabinet'])
            ->orderBy('date', 'desc')
            ->get();

        return response()->json(['data' => $this->transformAppointments($appointments)]);
    }

    public function consultations($id)
    {
        $consultations = Consultation::where('patientId', $id)
            ->with(['patient.user', 'doctor.user', 'appointment'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $consultations]);
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
