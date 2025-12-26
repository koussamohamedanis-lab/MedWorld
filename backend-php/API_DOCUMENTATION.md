# MedWorld Backend API - Laravel

## Overview

Complete REST API backend for the MedWorld medical management system built with Laravel 12.

### Database Schema

The backend includes the following tables and models:

1. **Users** - Base user model with roles: superadmin, admin, doctor, assistant, patient
2. **Doctors** - Doctor profiles with speciality and consultation rates
3. **Patients** - Patient medical information and history
4. **Cabinets** - Medical clinics/cabinets
5. **Assistants** - Medical assistants
6. **SuperAdmins** - System administrators
7. **Appointments** - Appointment bookings
8. **Consultations** - Medical consultations
9. **Prescriptions** - Medical prescriptions
10. **Messages** - Inter-staff messaging
11. **Ratings** - Cabinet and service ratings
12. **Payments** - Payment tracking

### Key Relationships

- **User** → **Doctor**, **Patient**, **Assistant**, **SuperAdmin** (polymorphic relationships)
- **Doctor** ↔ **Assistant** (Many-to-Many)
- **Cabinet** → **Doctors**, **Assistants**, **Appointments**, **Consultations**
- **Appointment** → **Patient**, **Doctor**, **Cabinet**, **Consultation**
- **Consultation** → **Doctor**, **Patient**, **Appointment**
- **Prescription** → **Consultation**, **Patient**, **Doctor**
- **Message** → **Sender (User)**, **Receiver (User)**, **Cabinet**
- **Rating** → **Patient**, **Cabinet**
- **Payment** → **Patient**, **Doctor**, **Cabinet**, **Appointment**

## Setup Instructions

### Prerequisites

- PHP 8.2+
- Composer
- Node.js (for frontend sync)
- SQLite or MySQL

### Installation

1. **Install Dependencies**
   ```bash
   cd my-laravel-app
   composer install
   ```

2. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Setup**
   ```bash
   php artisan migrate:fresh --seed
   ```

   This creates all tables and populates them with sample data matching the team12 frontend.

4. **Start the Server**
   ```bash
   php artisan serve
   ```

   Server runs on `http://localhost:8000`

## API Endpoints

### Authentication (Public)

```
POST   /api/v1/auth/login              - Login with email/password
POST   /api/v1/auth/register           - Register new user
POST   /api/v1/auth/logout             - Logout (requires auth)
GET    /api/v1/auth/me                 - Get authenticated user
```

### Users (Protected)

```
GET    /api/v1/users                   - List all users
GET    /api/v1/users/{id}              - Get user details
POST   /api/v1/users                   - Create user
PUT    /api/v1/users/{id}              - Update user
DELETE /api/v1/users/{id}              - Delete user
```

### Doctors (Protected)

```
GET    /api/v1/doctors                 - List all doctors
GET    /api/v1/doctors/{id}            - Get doctor details
POST   /api/v1/doctors                 - Create doctor
PUT    /api/v1/doctors/{id}            - Update doctor
DELETE /api/v1/doctors/{id}            - Delete doctor
GET    /api/v1/doctors/{id}/appointments    - Get doctor's appointments
GET    /api/v1/doctors/{id}/consultations   - Get doctor's consultations
GET    /api/v1/doctors/{id}/assistants      - Get doctor's assistants
```

### Patients (Protected)

```
GET    /api/v1/patients                     - List all patients
GET    /api/v1/patients/{id}                - Get patient details
POST   /api/v1/patients                     - Create patient
PUT    /api/v1/patients/{id}                - Update patient
DELETE /api/v1/patients/{id}                - Delete patient
GET    /api/v1/patients/{id}/appointments  - Get patient's appointments
GET    /api/v1/patients/{id}/consultations - Get patient's consultations
GET    /api/v1/patients/{id}/prescriptions - Get patient's prescriptions
```

### Cabinets (Protected)

```
GET    /api/v1/cabinets                - List all cabinets
GET    /api/v1/cabinets/{id}           - Get cabinet details
POST   /api/v1/cabinets                - Create cabinet
PUT    /api/v1/cabinets/{id}           - Update cabinet
DELETE /api/v1/cabinets/{id}           - Delete cabinet
GET    /api/v1/cabinets/{id}/doctors   - Get cabinet's doctors
GET    /api/v1/cabinets/{id}/assistants - Get cabinet's assistants
GET    /api/v1/cabinets/{id}/appointments - Get cabinet's appointments
GET    /api/v1/cabinets/{id}/ratings   - Get cabinet's ratings
```

### Assistants (Protected)

```
GET    /api/v1/assistants              - List all assistants
GET    /api/v1/assistants/{id}         - Get assistant details
POST   /api/v1/assistants              - Create assistant
PUT    /api/v1/assistants/{id}         - Update assistant
DELETE /api/v1/assistants/{id}         - Delete assistant
GET    /api/v1/assistants/{id}/doctors - Get assigned doctors
POST   /api/v1/assistants/{id}/doctors/attach  - Attach doctor
POST   /api/v1/assistants/{id}/doctors/detach  - Detach doctor
```

### Appointments (Protected)

```
GET    /api/v1/appointments            - List all appointments
GET    /api/v1/appointments/{id}       - Get appointment details
POST   /api/v1/appointments            - Create appointment
PUT    /api/v1/appointments/{id}       - Update appointment
DELETE /api/v1/appointments/{id}       - Delete appointment
GET    /api/v1/appointments/patient/{patientId}  - Get patient's appointments
GET    /api/v1/appointments/doctor/{doctorId}    - Get doctor's appointments
GET    /api/v1/appointments/cabinet/{cabinetId}  - Get cabinet's appointments
```

### Consultations (Protected)

```
GET    /api/v1/consultations           - List all consultations
GET    /api/v1/consultations/{id}      - Get consultation details
POST   /api/v1/consultations           - Create consultation
PUT    /api/v1/consultations/{id}      - Update consultation
DELETE /api/v1/consultations/{id}      - Delete consultation
GET    /api/v1/consultations/patient/{patientId} - Get patient's consultations
GET    /api/v1/consultations/doctor/{doctorId}   - Get doctor's consultations
```

### Prescriptions (Protected)

```
GET    /api/v1/prescriptions           - List all prescriptions
GET    /api/v1/prescriptions/{id}      - Get prescription details
POST   /api/v1/prescriptions           - Create prescription
PUT    /api/v1/prescriptions/{id}      - Update prescription
DELETE /api/v1/prescriptions/{id}      - Delete prescription
GET    /api/v1/prescriptions/patient/{patientId} - Get patient's prescriptions
GET    /api/v1/prescriptions/doctor/{doctorId}   - Get doctor's prescriptions
```

### Messages (Protected)

```
GET    /api/v1/messages                - List all messages
GET    /api/v1/messages/{id}           - Get message details
POST   /api/v1/messages                - Send message
PUT    /api/v1/messages/{id}           - Update message
DELETE /api/v1/messages/{id}           - Delete message
GET    /api/v1/messages/conversation/{userId1}/{userId2} - Get conversation
GET    /api/v1/messages/user/{userId}  - Get user's messages
POST   /api/v1/messages/{id}/mark-seen - Mark message as seen
```

### Ratings (Protected)

```
GET    /api/v1/ratings                 - List all ratings
GET    /api/v1/ratings/{id}            - Get rating details
POST   /api/v1/ratings                 - Create rating
PUT    /api/v1/ratings/{id}            - Update rating
DELETE /api/v1/ratings/{id}            - Delete rating
GET    /api/v1/ratings/cabinet/{cabinetId} - Get cabinet's ratings
GET    /api/v1/ratings/patient/{patientId} - Get patient's ratings
```

### Payments (Protected)

```
GET    /api/v1/payments                - List all payments
GET    /api/v1/payments/{id}           - Get payment details
POST   /api/v1/payments                - Create payment
PUT    /api/v1/payments/{id}           - Update payment
DELETE /api/v1/payments/{id}           - Delete payment
GET    /api/v1/payments/patient/{patientId} - Get patient's payments
GET    /api/v1/payments/doctor/{doctorId}   - Get doctor's payments
GET    /api/v1/payments/cabinet/{cabinetId} - Get cabinet's payments
GET    /api/v1/payments/status/{status}     - Get payments by status
```

## Sample Login Credentials

```
Email: houria.aichi@medworld.dz
Password: password123
Type: superadmin
```

Other test accounts available:
- kamel.daoud@medworld.dz (admin/doctor)
- rayan.miloudi@medworld.dz (doctor)
- riad.mahrez@email.dz (patient)
- amira.bouraoui@medworld.dz (assistant)

All use password: `password123`

## Response Format

### Success Response (200, 201)
```json
{
  "id": 1,
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "type": "doctor",
  ...
}
```

### List Response
```json
[
  {
    "id": 1,
    ...
  },
  {
    "id": 2,
    ...
  }
]
```

### Error Response (400, 401, 404, 422, 500)
```json
{
  "message": "Error description",
  "errors": {
    "field": ["Error message"]
  }
}
```

## Authentication

All protected endpoints require Bearer token authentication:

```
Authorization: Bearer {token}
```

Obtain token from login endpoint, then include in headers for subsequent requests.

## Database Seeding

The seeder creates:
- 1 Super Admin user
- 5 Admin users with cabinets
- 4 Regular doctors
- 5 Assistants
- 4 Patients
- 7 Appointments
- 4 Consultations
- 4 Prescriptions
- 2 Messages
- 5 Cabinet ratings
- 3 Payments

All data is realistic and matches the team12 frontend expectations.

## Testing

To test endpoints manually:

```bash
# Login
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"houria.aichi@medworld.dz","password":"password123"}'

# Get doctors with token
curl -X GET http://localhost:8000/api/v1/doctors \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## CORS Configuration

If testing from a different origin, update `config/cors.php`:

```php
'allowed_origins' => ['*'],
```

Or specify your frontend URL.

## File Structure

```
my-laravel-app/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── Api/
│   │           ├── AuthController.php
│   │           ├── UserController.php
│   │           ├── DoctorController.php
│   │           ├── PatientController.php
│   │           ├── CabinetController.php
│   │           ├── AssistantController.php
│   │           ├── AppointmentController.php
│   │           ├── ConsultationController.php
│   │           ├── PrescriptionController.php
│   │           ├── MessageController.php
│   │           ├── RatingController.php
│   │           └── PaymentController.php
│   └── Models/
│       ├── User.php
│       ├── Doctor.php
│       ├── Patient.php
│       ├── Cabinet.php
│       ├── Assistant.php
│       ├── SuperAdmin.php
│       ├── Appointment.php
│       ├── Consultation.php
│       ├── Prescription.php
│       ├── Message.php
│       ├── Rating.php
│       └── Payment.php
├── database/
│   ├── migrations/
│   └── seeders/
│       └── DatabaseSeeder.php
├── routes/
│   └── api.php
└── config/
    └── cors.php
```

## Notes

- All timestamps are UTC
- Appointments status: SCHEDULED, CONFIRMED, IN_PROGRESS, COMPLETED, CANCELLED, NO_SHOW
- Prescription status: ACTIVE, COMPLETED, CANCELLED
- Payment status: pending, completed, failed, refunded
- Message status: unseen, seen
- User types: superadmin, admin, doctor, assistant, patient

## Support

For issues or questions, refer to the Laravel documentation:
- https://laravel.com/docs/12
- https://laravel.com/docs/12/sanctum
