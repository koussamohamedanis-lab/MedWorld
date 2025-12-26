<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Assistant;
use App\Models\Cabinet;
use App\Models\User;
use App\Models\Consultation;
use App\Helpers\UserHelper;

class AllController extends Controller
{
    public function appointments()
    {
        $appointments = Appointment::with(['patient.user', 'doctor.user', 'cabinet'])
            ->orderBy('date', 'desc')
            ->get();

        return response()->json(['data' => $this->transformAppointments($appointments)]);
    }

    public function doctors()
    {
        $doctors = Doctor::with(['user', 'cabinet'])->get();
        $result = [];
        foreach ($doctors as $doctor) {
            if ($doctor->user) {
                $result[] = UserHelper::getUserWithExtendedInfo($doctor->user);
            }
        }
        return response()->json(['data' => $result]);
    }

    public function patients()
    {
        $patients = Patient::with('user')->get();
        $result = [];
        foreach ($patients as $patient) {
            if ($patient->user) {
                $result[] = UserHelper::getUserWithExtendedInfo($patient->user);
            }
        }
        return response()->json(['data' => $result]);
    }

    public function assistants()
    {
        $assistants = Assistant::with('user')->get();
        $result = [];
        foreach ($assistants as $assistant) {
            if ($assistant->user) {
                $result[] = UserHelper::getUserWithExtendedInfo($assistant->user);
            }
        }
        return response()->json(['data' => $result]);
    }

    public function cabinets()
    {
        $cabinets = Cabinet::with('admin')->get();
        return response()->json(['data' => $cabinets]);
    }

    public function users()
    {
        $users = User::all();
        $result = [];
        foreach ($users as $user) {
            $result[] = UserHelper::getUserWithExtendedInfo($user);
        }
        return response()->json(['data' => $result]);
    }

    public function consultations()
    {
        $consultations = Consultation::with(['patient.user', 'doctor.user', 'appointment'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $consultations]);
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
