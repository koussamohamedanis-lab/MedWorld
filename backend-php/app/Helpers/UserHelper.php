<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Assistant;
use App\Models\Consultation;
use App\Models\Message;

class UserHelper
{
    public static function getUserWithExtendedInfo($user, $depth = 2)
    {
        if (!$user) {
            return null;
        }

        $baseUser = [
            'id' => $user->id,
            'firstName' => $user->firstName,
            'lastName' => $user->lastName,
            'fullName' => $user->firstName . ' ' . $user->lastName,
            'email' => $user->email,
            'phoneNumber' => $user->phoneNumber,
            'avatarUrl' => $user->avatarUrl,
            'address' => $user->address,
            'gender' => $user->gender,
            'dateOfBirth' => $user->dateOfBirth,
            'type' => $user->type,
            'createdAt' => $user->created_at,
        ];

        $messages = Message::where('receiverId', $user->id)
            ->orWhere('senderId', $user->id)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->get();
        $baseUser['messages'] = $messages;

        if ($depth <= 0) {
            return $baseUser;
        }

        switch ($user->type) {
            case 'doctor':
            case 'admin':
                $doctor = Doctor::where('userId', $user->id)
                    ->with(['cabinet', 'calendars'])
                    ->first();

                if ($doctor) {
                    $baseUser['doctorId'] = $doctor->id;
                    $baseUser['speciality'] = $doctor->speciality;
                    $baseUser['licenseNumber'] = $doctor->licenseNumber;
                    $baseUser['careerStart'] = $doctor->careerStart;
                    $baseUser['consultationPrice'] = $doctor->consultationPrice;
                    $baseUser['consultationDuration'] = $doctor->consultationDuration;
                    $baseUser['cabinet'] = $doctor->cabinet;
                    $baseUser['cabinetId'] = $doctor->cabinetId;
                    $baseUser['calendars'] = $doctor->calendars;

                    $consultations = Consultation::where('doctorId', $doctor->id)
                        ->with(['doctor.user', 'patient.user', 'appointment'])
                        ->orderBy('created_at', 'desc')
                        ->get();
                    $baseUser['consultations'] = $consultations;

                    $assistant = Assistant::where('doctorId', $doctor->id)
                        ->with(['user', 'cabinet'])
                        ->first();

                    if ($assistant && $assistant->user) {
                        $baseUser['assistantId'] = $assistant->id;
                        $baseUser['assistant'] = self::getUserWithExtendedInfo($assistant->user, $depth - 1);
                    } else {
                        $baseUser['assistantId'] = null;
                        $baseUser['assistant'] = null;
                    }
                }
                break;

            case 'patient':
                $patient = Patient::where('userId', $user->id)->first();
                if ($patient) {
                    $baseUser['patientId'] = $patient->id;
                    $baseUser['emergencyContact'] = $patient->emergencyContact;
                    $baseUser['bloodType'] = $patient->bloodType;
                    $baseUser['weight'] = $patient->weight;
                    $baseUser['medicalHistory'] = $patient->medicalHistory;
                    $baseUser['allergies'] = $patient->allergies;
                }
                break;

            case 'assistant':
                $assistant = Assistant::where('userId', $user->id)
                    ->with(['cabinet', 'doctor.user', 'doctor.cabinet', 'doctor.calendars'])
                    ->first();

                if ($assistant) {
                    $baseUser['assistantId'] = $assistant->id;
                    $baseUser['cabinet'] = $assistant->cabinet;
                    $baseUser['cabinetId'] = $assistant->cabinetId;
                    $baseUser['doctorId'] = $assistant->doctorId;

                    if ($assistant->doctor && $assistant->doctor->user) {
                        $baseUser['doctor'] = self::getUserWithExtendedInfo($assistant->doctor->user, $depth - 1);
                    } else {
                        $baseUser['doctor'] = null;
                    }
                }
                break;
        }

        return $baseUser;
    }
}
