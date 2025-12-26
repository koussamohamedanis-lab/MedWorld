<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Calendar;
use App\Helpers\UserHelper;

class DoctorController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->attributes->get('user');
        $query = Doctor::with(['user', 'cabinet']);

        if ($user && $user->type !== 'superadmin') {
            $cabinetId = $this->getCurrentUserCabinetId($user);
            if ($cabinetId) {
                $query->where('cabinetId', $cabinetId);
            }
        }

        $doctors = $query->get();
        $result = [];
        foreach ($doctors as $doctor) {
            if ($doctor->user) {
                $result[] = UserHelper::getUserWithExtendedInfo($doctor->user);
            }
        }

        return response()->json(['data' => $result]);
    }

    public function show(Request $request, $id)
    {
        $user = $request->attributes->get('user');
        $doctor = Doctor::with(['user', 'cabinet'])->find($id);

        if (!$doctor) {
            return response()->json(['message' => 'Doctor not found'], 404);
        }

        if ($user && $user->type !== 'superadmin') {
            $cabinetId = $this->getCurrentUserCabinetId($user);
            if (!$cabinetId || $doctor->cabinetId != $cabinetId) {
                return response()->json(['message' => 'Forbidden (outside your cabinet)'], 403);
            }
        }

        return response()->json(UserHelper::getUserWithExtendedInfo($doctor->user));
    }

    public function availability(Request $request, $id)
    {
        $user = $request->attributes->get('user');
        $date = $request->query('date');

        if (!$date) {
            return response()->json(['message' => 'date query param is required (YYYY-MM-DD)'], 400);
        }

        if ($user && $user->type !== 'superadmin' && $user->type !== 'patient') {
            $cabinetId = $this->getCurrentUserCabinetId($user);
            $doctor = Doctor::find($id);
            if (!$doctor) {
                return response()->json(['message' => 'Doctor not found'], 404);
            }
            if (!$cabinetId || $doctor->cabinetId != $cabinetId) {
                return response()->json(['message' => 'Forbidden (outside your cabinet)'], 403);
            }
        }

        $calendar = Calendar::where('doctorId', $id)->first();
        $availableSlots = [];

        if ($calendar && $calendar->availability) {
            $availability = json_decode($calendar->availability, true);
            foreach ($availability as $day) {
                if ($day['date'] === $date) {
                    $availableSlots = $day['slots'] ?? [];
                    break;
                }
            }
        }

        $bookedSlots = Appointment::where('doctorId', $id)
            ->whereDate('date', $date)
            ->where('status', '!=', Appointment::STATUS_CANCELLED)
            ->get()
            ->pluck('date')
            ->map(fn($d) => $d->format('H:i'))
            ->toArray();

        $availableSlots = array_values(array_diff($availableSlots, $bookedSlots));
        sort($availableSlots);

        return response()->json([
            'date' => $date,
            'doctorId' => $id,
            'availableSlots' => $availableSlots,
        ]);
    }

    public function appointments(Request $request, $id)
    {
        $user = $request->attributes->get('user');

        if ($user && $user->type !== 'superadmin') {
            $cabinetId = $this->getCurrentUserCabinetId($user);
            $doctor = Doctor::find($id);
            if (!$doctor) {
                return response()->json(['message' => 'Doctor not found'], 404);
            }
            if (!$cabinetId || $doctor->cabinetId != $cabinetId) {
                return response()->json(['message' => 'Forbidden (outside your cabinet)'], 403);
            }
        }

        $appointments = Appointment::where('doctorId', $id)
            ->with(['patient.user', 'doctor.user', 'cabinet'])
            ->orderBy('date', 'desc')
            ->get();

        return response()->json(['data' => $this->transformAppointments($appointments)]);
    }

    public function consultations(Request $request, $id)
    {
        $user = $request->attributes->get('user');

        if ($user && $user->type !== 'superadmin') {
            $cabinetId = $this->getCurrentUserCabinetId($user);
            $doctor = Doctor::find($id);
            if (!$doctor) {
                return response()->json(['message' => 'Doctor not found'], 404);
            }
            if (!$cabinetId || $doctor->cabinetId != $cabinetId) {
                return response()->json(['message' => 'Forbidden (outside your cabinet)'], 403);
            }
        }

        $consultations = Consultation::where('doctorId', $id)
            ->with(['patient.user', 'doctor.user', 'appointment'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $consultations]);
    }

    public function patients(Request $request, $id)
    {
        $user = $request->attributes->get('user');

        if ($user && $user->type !== 'superadmin') {
            $cabinetId = $this->getCurrentUserCabinetId($user);
            $doctor = Doctor::find($id);
            if (!$doctor) {
                return response()->json(['message' => 'Doctor not found'], 404);
            }
            if (!$cabinetId || $doctor->cabinetId != $cabinetId) {
                return response()->json(['message' => 'Forbidden (outside your cabinet)'], 403);
            }
        }

        $appointments = Appointment::where('doctorId', $id)
            ->with('patient.user')
            ->get();

        $patientMap = [];
        foreach ($appointments as $appointment) {
            if ($appointment->patient && $appointment->patient->user) {
                $patientMap[$appointment->patient->id] = $appointment->patient;
            }
        }

        $result = [];
        foreach ($patientMap as $patient) {
            $result[] = UserHelper::getUserWithExtendedInfo($patient->user);
        }

        return response()->json(['data' => $result]);
    }

    public function search(Request $request)
    {
        $user = $request->attributes->get('user');
        $query = Doctor::with(['user', 'cabinet']);

        if ($user && $user->type !== 'superadmin') {
            $cabinetId = $this->getCurrentUserCabinetId($user);
            if ($cabinetId) {
                $query->where('cabinetId', $cabinetId);
            }
        }

        if ($request->has('speciality')) {
            $query->whereRaw('LOWER(speciality) LIKE ?', ['%' . strtolower($request->speciality) . '%']);
        }

        if ($user && $user->type === 'superadmin' && $request->has('cabinet_id')) {
            $query->where('cabinetId', $request->cabinet_id);
        }

        if ($request->has('price_max')) {
            $query->where('consultationPrice', '<=', $request->price_max);
        }

        if ($request->has('price_min')) {
            $query->where('consultationPrice', '>=', $request->price_min);
        }

        $doctors = $query->get();
        $result = [];
        foreach ($doctors as $doctor) {
            if ($doctor->user) {
                $result[] = UserHelper::getUserWithExtendedInfo($doctor->user);
            }
        }

        return response()->json(['data' => $result]);
    }

    private function getCurrentUserCabinetId($user)
    {
        if (!$user) {
            return null;
        }

        if ($user->type === 'admin' || $user->type === 'doctor') {
            $doctor = Doctor::where('userId', $user->id)->first();
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
