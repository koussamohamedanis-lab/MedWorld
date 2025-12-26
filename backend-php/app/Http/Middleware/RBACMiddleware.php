<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RBACMiddleware
{
    const PERM_VIEW_CABINET = 'view_cabinet';
    const PERM_ADD_CABINET = 'add_cabinet';
    const PERM_EDIT_CABINET = 'edit_cabinet';
    const PERM_MANAGE_CABINET = 'manage_cabinet';
    const PERM_REMOVE_CABINET = 'remove_cabinet';
    const PERM_SET_ADMIN_DOCTOR = 'set_admin_doctor';
    const PERM_VIEW_DOCTOR = 'view_doctor';
    const PERM_ADD_DOCTOR = 'add_doctor';
    const PERM_EDIT_DOCTOR = 'edit_doctor';
    const PERM_REMOVE_DOCTOR = 'remove_doctor';
    const PERM_VIEW_CALENDAR = 'view_calendar';
    const PERM_EDIT_CALENDAR = 'edit_calendar';
    const PERM_VIEW_ASSISTANT = 'view_assistant';
    const PERM_ADD_ASSISTANT = 'add_assistant';
    const PERM_EDIT_ASSISTANT = 'edit_assistant';
    const PERM_REMOVE_ASSISTANT = 'remove_assistant';
    const PERM_ASSIGN_ASSISTANT = 'assign_assistant';
    const PERM_CANCEL_APPOINTMENT = 'cancel_appointment';
    const PERM_EDIT_APPOINTMENT = 'edit_appointment';
    const PERM_VIEW_APPOINTMENT = 'view_appointment';
    const PERM_BOOK_APPOINTMENT = 'book_appointment';
    const PERM_VIEW_PATIENT = 'view_patient';
    const PERM_ADD_PATIENT = 'add_patient';
    const PERM_EDIT_PATIENT = 'edit_patient';
    const PERM_REMOVE_PATIENT = 'remove_patient';
    const PERM_VIEW_CONSULTATION = 'view_consultation';
    const PERM_ADD_CONSULTATION = 'add_consultation';
    const PERM_EDIT_CONSULTATION = 'edit_consultation';
    const PERM_REMOVE_CONSULTATION = 'remove_consultation';
    const PERM_ADD_PRESCRIPTION = 'add_prescription';
    const PERM_EDIT_PRESCRIPTION = 'edit_prescription';
    const PERM_VIEW_MEDICAL_FOLDER = 'view_medical_folder';
    const PERM_ADD_MEDICAL_FOLDER = 'add_medical_folder';
    const PERM_EDIT_MEDICAL_FOLDER = 'edit_medical_folder';
    const PERM_REMOVE_MEDICAL_FOLDER = 'remove_medical_folder';
    const PERM_VIEW_MESSAGE = 'view_message';
    const PERM_SEND_MESSAGE = 'send_message';
    const PERM_RATE_DOCTOR = 'rate_doctor';
    const PERM_ALL = 'all';

    private static function getPermissionsForUserType($userType)
    {
        return match ($userType) {
            'superadmin' => [
                self::PERM_VIEW_DOCTOR,
                self::PERM_ADD_DOCTOR,
                self::PERM_EDIT_DOCTOR,
                self::PERM_REMOVE_DOCTOR,
                self::PERM_VIEW_CABINET,
                self::PERM_ADD_CABINET,
                self::PERM_EDIT_CABINET,
                self::PERM_REMOVE_CABINET,
                self::PERM_VIEW_ASSISTANT,
                self::PERM_ADD_ASSISTANT,
                self::PERM_EDIT_ASSISTANT,
                self::PERM_REMOVE_ASSISTANT,
                self::PERM_VIEW_MESSAGE,
                self::PERM_SEND_MESSAGE,
            ],
            'admin' => [
                self::PERM_VIEW_MEDICAL_FOLDER,
                self::PERM_ADD_MEDICAL_FOLDER,
                self::PERM_EDIT_MEDICAL_FOLDER,
                self::PERM_REMOVE_MEDICAL_FOLDER,
                self::PERM_VIEW_CALENDAR,
                self::PERM_EDIT_CALENDAR,
                self::PERM_VIEW_PATIENT,
                self::PERM_ADD_PATIENT,
                self::PERM_EDIT_PATIENT,
                self::PERM_REMOVE_PATIENT,
                self::PERM_VIEW_CONSULTATION,
                self::PERM_ADD_CONSULTATION,
                self::PERM_EDIT_CONSULTATION,
                self::PERM_VIEW_APPOINTMENT,
                self::PERM_EDIT_APPOINTMENT,
                self::PERM_CANCEL_APPOINTMENT,
                self::PERM_VIEW_MESSAGE,
                self::PERM_SEND_MESSAGE,
                self::PERM_ASSIGN_ASSISTANT,
                self::PERM_EDIT_CABINET,
                self::PERM_ADD_DOCTOR,
                self::PERM_EDIT_DOCTOR,
                self::PERM_SET_ADMIN_DOCTOR,
            ],
            'doctor' => [
                self::PERM_VIEW_MEDICAL_FOLDER,
                self::PERM_ADD_MEDICAL_FOLDER,
                self::PERM_EDIT_MEDICAL_FOLDER,
                self::PERM_REMOVE_MEDICAL_FOLDER,
                self::PERM_VIEW_CALENDAR,
                self::PERM_EDIT_CALENDAR,
                self::PERM_VIEW_PATIENT,
                self::PERM_ADD_PATIENT,
                self::PERM_EDIT_PATIENT,
                self::PERM_REMOVE_PATIENT,
                self::PERM_VIEW_CONSULTATION,
                self::PERM_ADD_CONSULTATION,
                self::PERM_EDIT_CONSULTATION,
                self::PERM_VIEW_APPOINTMENT,
                self::PERM_EDIT_APPOINTMENT,
                self::PERM_CANCEL_APPOINTMENT,
                self::PERM_VIEW_MESSAGE,
                self::PERM_SEND_MESSAGE,
                self::PERM_ASSIGN_ASSISTANT,
            ],
            'assistant' => [
                self::PERM_VIEW_APPOINTMENT,
                self::PERM_EDIT_APPOINTMENT,
                self::PERM_CANCEL_APPOINTMENT,
                self::PERM_VIEW_CONSULTATION,
                self::PERM_ADD_CONSULTATION,
                self::PERM_EDIT_CONSULTATION,
                self::PERM_ADD_PRESCRIPTION,
                self::PERM_EDIT_PRESCRIPTION,
                self::PERM_VIEW_CALENDAR,
                self::PERM_EDIT_CALENDAR,
                self::PERM_ADD_PATIENT,
                self::PERM_EDIT_PATIENT,
            ],
            'patient' => [
                self::PERM_BOOK_APPOINTMENT,
                self::PERM_VIEW_APPOINTMENT,
                self::PERM_CANCEL_APPOINTMENT,
                self::PERM_RATE_DOCTOR,
            ],
            default => [],
        };
    }

    private static function hasPermission($userType, $permission)
    {
        $permissions = self::getPermissionsForUserType($userType);
        return in_array($permission, $permissions) || in_array(self::PERM_ALL, $permissions);
    }

    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        $user = $request->attributes->get('user');

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        foreach ($permissions as $permission) {
            if (self::hasPermission($user->type, $permission)) {
                return $next($request);
            }
        }

        return response()->json(['message' => "You don't have permission to perform this action"], 403);
    }
}
