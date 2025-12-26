<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cabinet;
use App\Models\Doctor;
use App\Models\Assistant;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Calendar;
use App\Helpers\UserHelper;
use Illuminate\Support\Facades\Validator;

class CabinetController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->attributes->get('user');
        $query = Cabinet::with('admin');

        if ($user && $user->type !== 'superadmin') {
            if ($user->type === 'patient') {
            } else {
                $cabinetId = $this->getCurrentUserCabinetId($user);
                if ($cabinetId) {
                    $query->where('id', $cabinetId);
                } else {
                    $query->whereRaw('1 = 0');
                }
            }
        }

        return response()->json(['data' => $query->get()]);
    }

    public function show($id)
    {
        $cabinet = Cabinet::with('admin')->find($id);

        if (!$cabinet) {
            return response()->json(['message' => 'Cabinet not found'], 404);
        }

        return response()->json($cabinet);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        if (isset($data['location']) && is_array($data['location'])) {
            $data['location'] = json_encode($data['location']);
        }
        if (isset($data['openingHours']) && is_array($data['openingHours'])) {
            $data['openingHours'] = json_encode($data['openingHours']);
        }

        $cabinet = Cabinet::create($data);
        return response()->json($cabinet, 201);
    }

    public function update(Request $request, $id)
    {
        $cabinet = Cabinet::find($id);

        if (!$cabinet) {
            return response()->json(['message' => 'Cabinet not found'], 404);
        }

        $data = $request->all();
        if (isset($data['location']) && is_array($data['location'])) {
            $data['location'] = json_encode($data['location']);
        }
        if (isset($data['openingHours']) && is_array($data['openingHours'])) {
            $data['openingHours'] = json_encode($data['openingHours']);
        }

        $cabinet->update($data);
        return response()->json($cabinet);
    }

    public function destroy($id)
    {
        $cabinet = Cabinet::find($id);

        if (!$cabinet) {
            return response()->json(['message' => 'Cabinet not found'], 404);
        }

        $cabinet->delete();
        return response()->json(['message' => 'Cabinet deleted successfully']);
    }

    public function doctors(Request $request, $id)
    {
        $user = $request->attributes->get('user');

        if ($user && $user->type !== 'superadmin' && $user->type !== 'patient') {
            $cabinetId = $this->getCurrentUserCabinetId($user);
            if (!$cabinetId || $id != $cabinetId) {
                return response()->json(['message' => 'Forbidden (outside your cabinet)'], 403);
            }
        }

        $doctors = Doctor::where('cabinetId', $id)->with('user')->get();
        $result = [];
        foreach ($doctors as $doctor) {
            if ($doctor->user) {
                $result[] = UserHelper::getUserWithExtendedInfo($doctor->user);
            }
        }

        return response()->json(['data' => $result]);
    }

    public function appointments($id)
    {
        $appointments = Appointment::where('cabinetId', $id)
            ->with(['patient.user', 'doctor.user', 'cabinet'])
            ->orderBy('date', 'desc')
            ->get();

        return response()->json(['data' => $this->transformAppointments($appointments)]);
    }

    public function assistants($id)
    {
        $assistants = Assistant::where('cabinetId', $id)
            ->with(['user', 'doctor.user', 'doctor.cabinet', 'doctor.calendars'])
            ->get();

        $result = [];
        foreach ($assistants as $assistant) {
            if ($assistant->user) {
                $userData = UserHelper::getUserWithExtendedInfo($assistant->user);
                $userData['assistantId'] = $assistant->id;
                $userData['doctorId'] = $assistant->doctorId;
                if ($assistant->doctor && $assistant->doctor->user) {
                    $userData['doctor'] = UserHelper::getUserWithExtendedInfo($assistant->doctor->user, 1);
                } else {
                    $userData['doctor'] = null;
                }
                $result[] = $userData;
            }
        }

        return response()->json(['data' => $result]);
    }

    public function addDoctor(Request $request, $id)
    {
        $email = $request->input('email');
        $user = User::where('email', $email)->where('type', 'doctor')->first();

        if (!$user) {
            return response()->json(['message' => 'Doctor not found with this email'], 404);
        }

        Doctor::where('userId', $user->id)->update(['cabinetId' => $id]);

        return response()->json(['message' => 'Doctor added successfully']);
    }

    public function removeDoctor($id, $doctorId)
    {
        Doctor::where('id', $doctorId)->update(['cabinetId' => 0]);
        return response()->json(['message' => 'Doctor removed successfully']);
    }

    public function createDoctor(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required',
            'lastName' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $user = User::create([
            'firstName' => $request->firstName,
            'lastName' => $request->lastName,
            'email' => $request->email,
            'password' => $request->password,
            'phoneNumber' => $request->phoneNumber,
            'address' => $request->address,
            'gender' => $request->gender,
            'type' => 'doctor',
        ]);

        $yearsOfExperience = $request->yearsOfExperience ?? 0;
        $careerStart = $yearsOfExperience > 0 ? now()->subYears($yearsOfExperience) : null;

        $doctor = Doctor::create([
            'userId' => $user->id,
            'cabinetId' => $id,
            'speciality' => $request->speciality,
            'licenseNumber' => $request->licenseNumber,
            'consultationPrice' => $request->consultationPrice ?? 0,
            'consultationDuration' => $request->consultationDuration ?? 30,
            'careerStart' => $careerStart,
        ]);

        $availability = $this->defaultWeekAvailability();
        Calendar::create([
            'doctorId' => $doctor->id,
            'cabinetId' => $id,
            'availability' => json_encode($availability),
        ]);

        return response()->json([
            'message' => 'Doctor created successfully',
            'data' => UserHelper::getUserWithExtendedInfo($user),
        ], 201);
    }

    public function setAdminDoctor(Request $request, $id, $doctorId)
    {
        $doctor = Doctor::find($doctorId);

        if (!$doctor || $doctor->cabinetId != $id) {
            return response()->json(['message' => 'Doctor not found in this cabinet'], 404);
        }

        User::where('id', $doctor->userId)->update(['type' => 'admin']);
        Cabinet::where('id', $id)->update(['adminId' => $doctor->userId]);

        return response()->json(['message' => 'Admin doctor set successfully']);
    }

    public function addAssistant(Request $request, $id)
    {
        $email = $request->input('email');
        $doctorId = $request->input('doctorId', 0);

        $user = User::where('email', $email)->where('type', 'assistant')->first();

        if (!$user) {
            return response()->json(['message' => 'Assistant not found with this email'], 404);
        }

        if ($doctorId) {
            $doctor = Doctor::where('id', $doctorId)->where('cabinetId', $id)->first();
            if (!$doctor) {
                return response()->json(['message' => 'Invalid doctorId for this cabinet'], 400);
            }

            $existing = Assistant::where('doctorId', $doctorId)->where('userId', '!=', $user->id)->first();
            if ($existing) {
                return response()->json(['message' => 'This doctor already has an assigned assistant'], 409);
            }
        } else {
            $doctor = Doctor::where('cabinetId', $id)->first();
            $doctorId = $doctor ? $doctor->id : 0;
        }

        Assistant::where('userId', $user->id)->update([
            'cabinetId' => $id,
            'doctorId' => $doctorId ?: null,
        ]);

        return response()->json(['message' => 'Assistant added successfully']);
    }

    public function createAssistant(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required',
            'lastName' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'doctorId' => 'required|exists:doctors,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $doctor = Doctor::where('id', $request->doctorId)->where('cabinetId', $id)->first();
        if (!$doctor) {
            return response()->json(['message' => 'Invalid doctorId for this cabinet'], 400);
        }

        $existing = Assistant::where('doctorId', $request->doctorId)->first();
        if ($existing) {
            return response()->json(['message' => 'This doctor already has an assigned assistant'], 409);
        }

        $user = User::create([
            'firstName' => $request->firstName,
            'lastName' => $request->lastName,
            'email' => $request->email,
            'password' => $request->password,
            'phoneNumber' => $request->phoneNumber,
            'address' => $request->address,
            'gender' => $request->gender,
            'type' => 'assistant',
        ]);

        Assistant::create([
            'userId' => $user->id,
            'cabinetId' => $id,
            'doctorId' => $request->doctorId,
        ]);

        return response()->json([
            'message' => 'Assistant created successfully',
            'data' => UserHelper::getUserWithExtendedInfo($user),
        ], 201);
    }

    public function assignAssistant(Request $request, $id, $assistantId)
    {
        $doctorId = $request->input('doctorId', 0);

        $assistant = Assistant::where('id', $assistantId)->where('cabinetId', $id)->first();

        if (!$assistant) {
            return response()->json(['message' => 'Assistant not found in this cabinet'], 404);
        }

        if ($doctorId) {
            $doctor = Doctor::where('id', $doctorId)->where('cabinetId', $id)->first();
            if (!$doctor) {
                return response()->json(['message' => 'Invalid doctorId for this cabinet'], 400);
            }

            $existing = Assistant::where('doctorId', $doctorId)->where('id', '!=', $assistant->id)->first();
            if ($existing) {
                return response()->json(['message' => 'This doctor already has an assigned assistant'], 409);
            }
        }

        $assistant->update(['doctorId' => $doctorId ?: null]);

        return response()->json([
            'message' => 'Assistant assignment updated',
            'data' => [
                'assistantId' => $assistant->id,
                'cabinetId' => $assistant->cabinetId,
                'doctorId' => $assistant->doctorId,
            ],
        ]);
    }

    public function removeAssistant($id, $assistantId)
    {
        Assistant::where('id', $assistantId)->update([
            'cabinetId' => 0,
            'doctorId' => 0,
        ]);

        return response()->json(['message' => 'Assistant removed successfully']);
    }

    public function bootstrapAdmin(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required',
            'lastName' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $cabinet = Cabinet::find($id);

        if (!$cabinet) {
            return response()->json(['message' => 'Cabinet not found'], 404);
        }

        if ($cabinet->adminId) {
            return response()->json(['message' => 'Cabinet already has an admin'], 409);
        }

        $user = User::create([
            'firstName' => $request->firstName,
            'lastName' => $request->lastName,
            'email' => $request->email,
            'password' => $request->password,
            'phoneNumber' => $request->phoneNumber,
            'address' => $request->address,
            'gender' => $request->gender,
            'type' => 'admin',
        ]);

        $doctor = Doctor::create([
            'userId' => $user->id,
            'cabinetId' => $id,
        ]);

        $availability = $this->defaultWeekAvailability();
        Calendar::create([
            'doctorId' => $doctor->id,
            'cabinetId' => $id,
            'availability' => json_encode($availability),
        ]);

        $cabinet->update(['adminId' => $user->id]);

        return response()->json([
            'message' => 'Admin doctor created successfully',
            'data' => UserHelper::getUserWithExtendedInfo($user),
        ], 201);
    }

    private function getCurrentUserCabinetId($user)
    {
        if (!$user)
            return null;

        if ($user->type === 'admin' || $user->type === 'doctor') {
            $doctor = Doctor::where('userId', $user->id)->first();
            return $doctor ? $doctor->cabinetId : null;
        }

        if ($user->type === 'assistant') {
            $assistant = Assistant::where('userId', $user->id)->first();
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

    private function defaultWeekAvailability()
    {
        $availability = [];
        $slots = ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00'];

        for ($i = 0; $i < 7; $i++) {
            $date = now()->addDays($i)->format('Y-m-d');
            $availability[] = [
                'date' => $date,
                'slots' => $slots,
            ];
        }

        return $availability;
    }
}
