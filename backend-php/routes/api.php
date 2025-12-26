<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\CabinetController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\AllController;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\RBACMiddleware;
use App\Http\Middleware\CabinetAccessMiddleware;
use App\Http\Middleware\UserTypeMiddleware;

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me'])->middleware(AuthMiddleware::class);
    });

    Route::middleware([AuthMiddleware::class])->group(function () {

        Route::prefix('users')->group(function () {
            Route::get('/{id}', [UserController::class, 'show']);
            Route::put('/{id}', [UserController::class, 'update']);
        });

        Route::prefix('doctors')->group(function () {
            Route::get('/', [DoctorController::class, 'index']);
            Route::get('/{id}', [DoctorController::class, 'show']);
            Route::get('/{id}/availability', [DoctorController::class, 'availability']);
            Route::get('/{id}/appointments', [DoctorController::class, 'appointments']);
            Route::get('/{id}/consultations', [DoctorController::class, 'consultations']);
            Route::get('/{id}/patients', [DoctorController::class, 'patients']);
            Route::get('/search/filter', [DoctorController::class, 'search']);
        });

        Route::prefix('patients')->group(function () {
            Route::get('/', [PatientController::class, 'index']);
            Route::get('/{id}', [PatientController::class, 'show']);
            Route::get('/{id}/appointments', [PatientController::class, 'appointments']);
            Route::get('/{id}/consultations', [PatientController::class, 'consultations']);
        });

        Route::prefix('appointments')->group(function () {
            Route::get('/', [AppointmentController::class, 'index']);
            Route::get('/{id}', [AppointmentController::class, 'show']);
            Route::post('/', [AppointmentController::class, 'store'])->middleware(RBACMiddleware::class . ':' . RBACMiddleware::PERM_BOOK_APPOINTMENT . ',' . RBACMiddleware::PERM_EDIT_APPOINTMENT);
            Route::put('/{id}', [AppointmentController::class, 'update'])->middleware(RBACMiddleware::class . ':' . RBACMiddleware::PERM_EDIT_APPOINTMENT);
            Route::delete('/{id}', [AppointmentController::class, 'destroy'])->middleware(RBACMiddleware::class . ':' . RBACMiddleware::PERM_CANCEL_APPOINTMENT);
        });

        Route::prefix('consultations')->group(function () {
            Route::get('/', [ConsultationController::class, 'index']);
            Route::get('/{id}', [ConsultationController::class, 'show']);
            Route::post('/', [ConsultationController::class, 'store'])->middleware(RBACMiddleware::class . ':' . RBACMiddleware::PERM_ADD_CONSULTATION);
            Route::put('/{id}', [ConsultationController::class, 'update'])->middleware(RBACMiddleware::class . ':' . RBACMiddleware::PERM_EDIT_CONSULTATION);
        });

        Route::prefix('cabinets')->group(function () {
            Route::get('/', [CabinetController::class, 'index']);
            Route::get('/{id}', [CabinetController::class, 'show'])->middleware(CabinetAccessMiddleware::class);
            Route::get('/{id}/doctors', [CabinetController::class, 'doctors']);
            Route::get('/{id}/appointments', [CabinetController::class, 'appointments'])->middleware(CabinetAccessMiddleware::class);
            Route::get('/{id}/assistants', [CabinetController::class, 'assistants'])->middleware(CabinetAccessMiddleware::class);
            Route::post('/', [CabinetController::class, 'store'])->middleware(RBACMiddleware::class . ':' . RBACMiddleware::PERM_ADD_CABINET);
            Route::post('/{id}/bootstrap-admin', [CabinetController::class, 'bootstrapAdmin'])->middleware(UserTypeMiddleware::class . ':superadmin');
            Route::put('/{id}', [CabinetController::class, 'update'])->middleware([CabinetAccessMiddleware::class, RBACMiddleware::class . ':' . RBACMiddleware::PERM_EDIT_CABINET]);
            Route::delete('/{id}', [CabinetController::class, 'destroy'])->middleware([CabinetAccessMiddleware::class, RBACMiddleware::class . ':' . RBACMiddleware::PERM_REMOVE_CABINET]);
            Route::post('/{id}/doctors', [CabinetController::class, 'addDoctor'])->middleware([CabinetAccessMiddleware::class, RBACMiddleware::class . ':' . RBACMiddleware::PERM_EDIT_CABINET]);
            Route::delete('/{id}/doctors/{doctorId}', [CabinetController::class, 'removeDoctor'])->middleware([CabinetAccessMiddleware::class, RBACMiddleware::class . ':' . RBACMiddleware::PERM_EDIT_CABINET]);
            Route::post('/{id}/doctors/create', [CabinetController::class, 'createDoctor'])->middleware([CabinetAccessMiddleware::class, RBACMiddleware::class . ':' . RBACMiddleware::PERM_ADD_DOCTOR]);
            Route::put('/{id}/doctors/{doctorId}/set-admin', [CabinetController::class, 'setAdminDoctor'])->middleware([CabinetAccessMiddleware::class, RBACMiddleware::class . ':' . RBACMiddleware::PERM_SET_ADMIN_DOCTOR]);
            Route::post('/{id}/assistants', [CabinetController::class, 'addAssistant'])->middleware([CabinetAccessMiddleware::class, RBACMiddleware::class . ':' . RBACMiddleware::PERM_EDIT_CABINET]);
            Route::post('/{id}/assistants/create', [CabinetController::class, 'createAssistant'])->middleware([CabinetAccessMiddleware::class, RBACMiddleware::class . ':' . RBACMiddleware::PERM_ASSIGN_ASSISTANT]);
            Route::put('/{id}/assistants/{assistantId}/assign', [CabinetController::class, 'assignAssistant'])->middleware([CabinetAccessMiddleware::class, RBACMiddleware::class . ':' . RBACMiddleware::PERM_ASSIGN_ASSISTANT]);
            Route::delete('/{id}/assistants/{assistantId}', [CabinetController::class, 'removeAssistant'])->middleware([CabinetAccessMiddleware::class, RBACMiddleware::class . ':' . RBACMiddleware::PERM_EDIT_CABINET]);
        });

        Route::prefix('all')->middleware(UserTypeMiddleware::class . ':superadmin')->group(function () {
            Route::get('/appointments', [AllController::class, 'appointments']);
            Route::get('/doctors', [AllController::class, 'doctors']);
            Route::get('/patients', [AllController::class, 'patients']);
            Route::get('/assistants', [AllController::class, 'assistants']);
            Route::get('/cabinets', [AllController::class, 'cabinets']);
            Route::get('/users', [AllController::class, 'users']);
            Route::get('/consultations', [AllController::class, 'consultations']);
        });

        Route::prefix('calendars')->group(function () {
            Route::get('', [CalendarController::class, 'index']);
            Route::get('/', [CalendarController::class, 'index']);
            Route::get('/{id}', [CalendarController::class, 'show']);
            Route::post('', [CalendarController::class, 'store'])->middleware(RBACMiddleware::class . ':' . RBACMiddleware::PERM_EDIT_CALENDAR);
            Route::post('/', [CalendarController::class, 'store'])->middleware(RBACMiddleware::class . ':' . RBACMiddleware::PERM_EDIT_CALENDAR);
            Route::put('/{id}', [CalendarController::class, 'update'])->middleware(RBACMiddleware::class . ':' . RBACMiddleware::PERM_EDIT_CALENDAR);
        });

        Route::prefix('messages')->group(function () {
            Route::get('/', [MessageController::class, 'index'])->middleware(RBACMiddleware::class . ':' . RBACMiddleware::PERM_VIEW_MESSAGE);
            Route::get('/recipients', [MessageController::class, 'recipients'])->middleware(RBACMiddleware::class . ':' . RBACMiddleware::PERM_VIEW_MESSAGE);
            Route::post('/', [MessageController::class, 'store'])->middleware(RBACMiddleware::class . ':' . RBACMiddleware::PERM_SEND_MESSAGE);
        });

        Route::get('/health', function () {
            return response()->json([
                'status' => 'ok',
                'message' => 'MedWorld API is running',
            ]);
        });
    });
});
