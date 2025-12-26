# MedWorld Laravel Backend

Complete Laravel backend migrated from Go Fiber, providing full API compatibility.

## Features

- JWT Authentication
- Role-Based Access Control (RBAC)
- Cabinet-scoped access control
- All Go backend endpoints implemented
- SQLite database
- Comprehensive test data seeding

## Quick Start

### Option 1: Automated Setup (Windows)
```bash
setup.bat
```

### Option 2: Manual Setup

1. Install dependencies:
```bash
composer install
```

2. Create database:
```bash
type nul > database\database.sqlite
```

3. Generate application key:
```bash
php artisan key:generate
```

4. Run migrations and seed data:
```bash
php artisan migrate:fresh --seed
```

5. Start the server:
```bash
php artisan serve --port=8000
```

## API Endpoints

All endpoints are prefixed with `/api/v1`:

### Authentication
- `POST /auth/login` - User login
- `POST /auth/register` - User registration
- `POST /auth/logout` - User logout
- `GET /auth/me` - Get current user

### Users
- `GET /users/{id}` - Get user by ID
- `PUT /users/{id}` - Update user

### Doctors
- `GET /doctors` - List all doctors
- `GET /doctors/{id}` - Get doctor by ID
- `GET /doctors/{id}/availability` - Get doctor availability
- `GET /doctors/{id}/appointments` - Get doctor appointments
- `GET /doctors/{id}/consultations` - Get doctor consultations
- `GET /doctors/{id}/patients` - Get doctor patients
- `GET /doctors/search/filter` - Search doctors

### Patients
- `GET /patients` - List all patients
- `GET /patients/{id}` - Get patient by ID
- `GET /patients/{id}/appointments` - Get patient appointments
- `GET /patients/{id}/consultations` - Get patient consultations

### Appointments
- `GET /appointments` - List all appointments
- `GET /appointments/{id}` - Get appointment by ID
- `POST /appointments` - Create appointment
- `PUT /appointments/{id}` - Update appointment
- `DELETE /appointments/{id}` - Delete appointment

### Consultations
- `GET /consultations` - List all consultations
- `GET /consultations/{id}` - Get consultation by ID
- `POST /consultations` - Create consultation
- `PUT /consultations/{id}` - Update consultation

### Cabinets
- `GET /cabinets` - List all cabinets
- `GET /cabinets/{id}` - Get cabinet by ID
- `GET /cabinets/{id}/doctors` - Get cabinet doctors
- `GET /cabinets/{id}/appointments` - Get cabinet appointments
- `GET /cabinets/{id}/assistants` - Get cabinet assistants
- `POST /cabinets` - Create cabinet
- `PUT /cabinets/{id}` - Update cabinet
- `DELETE /cabinets/{id}` - Delete cabinet
- `POST /cabinets/{id}/doctors` - Add doctor to cabinet
- `DELETE /cabinets/{id}/doctors/{doctorId}` - Remove doctor from cabinet
- `POST /cabinets/{id}/doctors/create` - Create doctor in cabinet
- `PUT /cabinets/{id}/doctors/{doctorId}/set-admin` - Set admin doctor
- `POST /cabinets/{id}/assistants` - Add assistant to cabinet
- `POST /cabinets/{id}/assistants/create` - Create assistant in cabinet
- `PUT /cabinets/{id}/assistants/{assistantId}/assign` - Assign assistant to doctor
- `DELETE /cabinets/{id}/assistants/{assistantId}` - Remove assistant from cabinet
- `POST /cabinets/{id}/bootstrap-admin` - Bootstrap admin doctor (SuperAdmin only)

### Calendars
- `GET /calendars` - List all calendars
- `GET /calendars/{id}` - Get calendar by ID
- `POST /calendars` - Create calendar
- `PUT /calendars/{id}` - Update calendar

### Messages
- `GET /messages` - List user messages
- `GET /messages/recipients` - Get available recipients
- `POST /messages` - Send message

### All (SuperAdmin Only)
- `GET /all/appointments` - All appointments
- `GET /all/doctors` - All doctors
- `GET /all/patients` - All patients
- `GET /all/assistants` - All assistants
- `GET /all/cabinets` - All cabinets
- `GET /all/users` - All users
- `GET /all/consultations` - All consultations

## Test Credentials

- **SuperAdmin**: admin@medworld.com / admin123
- **Admin Doctor**: admin.doctor@medworld.com / admin123
- **Doctor**: doctor@medworld.com / doctor123
- **Assistant**: assistant@medworld.com / assistant123
- **Patient**: patient@medworld.com / patient123

## Environment Variables

Key environment variables in `.env`:
- `DB_CONNECTION=sqlite`
- `DB_DATABASE=` (full path to database.sqlite)
- `JWT_SECRET=medworld`
- `JWT_EXPIRATION=1440` (minutes)
- `CORS_ALLOWED_ORIGINS=` (comma-separated list)

## Architecture

### Models
- User, Doctor, Patient, Assistant
- Cabinet, Appointment, Consultation
- Calendar, Message

### Middleware
- AuthMiddleware - JWT authentication
- RBACMiddleware - Role-based permissions
- CabinetAccessMiddleware - Cabinet-scoped access
- UserTypeMiddleware - User type restrictions
- Cors - CORS handling

### Helpers
- JWTHelper - Token generation and validation
- UserHelper - Extended user info retrieval

## Database Schema

The database uses SQLite with the following tables:
- users
- doctors
- patients
- assistants
- cabinets
- appointments
- consultations
- calendars
- messages

All tables include proper foreign keys, indexes, and constraints matching the Go backend schema.

## Permissions

The RBAC system includes permissions for:
- Cabinet management
- Doctor management
- Patient management
- Appointment management
- Consultation management
- Calendar management
- Assistant management
- Message management

Permissions are automatically assigned based on user types (superadmin, admin, doctor, assistant, patient).
