<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Assistant;
use App\Models\Cabinet;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Calendar;
use App\Models\Message;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $userCount = User::count();
        if ($userCount > 50) {
            return;
        }

        $this->seedSuperAdmin();
        $cabinets = $this->seedCabinets();
        $doctors = $this->seedDoctors($cabinets);
        $this->seedAssistants($cabinets);
        $patients = $this->seedPatients();
        $this->seedAppointmentsAndConsultations($doctors, $patients, $cabinets);
        $this->seedCalendars($doctors);
        $this->seedMessages();
    }

    private function seedSuperAdmin()
    {
        if (!User::where('email', 'admin@medworld.com')->exists()) {
            User::create([
                'firstName' => 'Super',
                'lastName' => 'Admin',
                'email' => 'admin@medworld.com',
                'password' => 'admin123',
                'phoneNumber' => '+1234567890',
                'gender' => 'male',
                'dateOfBirth' => '1980-01-01',
                'type' => 'superadmin',
                'address' => 'MedWorld HQ',
            ]);
        }
    }

    private function seedCabinets()
    {
        $cabinetData = [
            ['name' => 'Central Medical Clinic', 'address' => '123 Healthcare Blvd', 'lat' => 40.7128, 'lng' => -74.0060],
            ['name' => 'Northside Family Practice', 'address' => '45 North Way', 'lat' => 40.7580, 'lng' => -73.9855],
            ['name' => 'West End Specialists', 'address' => '88 West End Ave', 'lat' => 40.7899, 'lng' => -73.9799],
        ];

        $openingHours = [
            'monday' => ['open' => '09:00', 'close' => '17:00'],
            'tuesday' => ['open' => '09:00', 'close' => '17:00'],
            'wednesday' => ['open' => '09:00', 'close' => '17:00'],
            'thursday' => ['open' => '09:00', 'close' => '17:00'],
            'friday' => ['open' => '09:00', 'close' => '16:00'],
        ];

        $cabinets = [];
        $superAdmin = User::where('type', 'superadmin')->first();

        foreach ($cabinetData as $data) {
            $location = ['address' => $data['address'], 'latitude' => $data['lat'], 'longitude' => $data['lng']];
            $cabinet = Cabinet::firstOrCreate(
                ['name' => $data['name']],
                [
                    'phone' => '+1555' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT),
                    'location' => json_encode($location),
                    'openingHours' => json_encode($openingHours),
                    'accessHandicap' => true,
                    'hasParking' => true,
                    'hasWifi' => true,
                    'acceptsUrgent' => true,
                    'acceptsInsurance' => true,
                    'adminId' => $superAdmin->id,
                ]
            );
            $cabinets[] = $cabinet;
        }

        return $cabinets;
    }

    private function seedDoctors($cabinets)
    {
        $this->createSpecificDoctor('Sarah', 'Johnson', 'admin.doctor@medworld.com', 'admin123', 'admin', $cabinets[0]->id, 'General Practice');
        $this->createSpecificDoctor('John', 'Smith', 'doctor@medworld.com', 'doctor123', 'doctor', $cabinets[0]->id, 'Cardiology');

        $specialties = ['Cardiology', 'General Practice', 'Dermatology', 'Pediatrics', 'Neurology'];
        $firstNames = ['John', 'Sarah', 'Emily', 'Michael', 'David', 'Jessica', 'Robert', 'Jennifer', 'William', 'Elizabeth', 'James', 'Linda'];
        $lastNames = ['Smith', 'Johnson', 'Brown', 'Williams', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez', 'Hernandez', 'Lopez'];

        for ($i = 0; $i < 10; $i++) {
            $email = "doctor{$i}@medworld.com";
            if (!in_array($email, ['admin.doctor@medworld.com', 'doctor@medworld.com'])) {
                $this->createSpecificDoctor(
                    $firstNames[array_rand($firstNames)],
                    $lastNames[array_rand($lastNames)],
                    $email,
                    'password',
                    $i % 3 === 0 ? 'admin' : 'doctor',
                    $cabinets[array_rand($cabinets)]->id,
                    $specialties[array_rand($specialties)]
                );
            }
        }

        return Doctor::with('user')->get()->all();
    }

    private function createSpecificDoctor($firstName, $lastName, $email, $password, $type, $cabinetId, $specialty)
    {
        if (User::where('email', $email)->exists()) {
            return;
        }

        $user = User::create([
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'password' => $password,
            'phoneNumber' => '+1555' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT),
            'gender' => ['male', 'female'][rand(0, 1)],
            'dateOfBirth' => now()->subYears(rand(30, 50)),
            'type' => $type,
            'address' => rand(1, 999) . ' Doctor Lane',
        ]);

        Doctor::create([
            'userId' => $user->id,
            'speciality' => $specialty,
            'careerStart' => now()->subYears(rand(5, 20)),
            'consultationPrice' => rand(50, 250),
            'consultationDuration' => 30,
            'cabinetId' => $cabinetId,
        ]);
    }

    private function seedAssistants($cabinets)
    {
        $this->createSpecificAssistant('Emma', 'Brown', 'assistant@medworld.com', 'assistant123', $cabinets[0]->id);

        $firstNames = ['Emma', 'Olivia', 'Ava', 'Isabella', 'Sophia', 'Mia'];
        $lastNames = ['Charlotte', 'Amelia', 'Harper', 'Evelyn', 'Abigail', 'Emily'];

        for ($i = 0; $i < 5; $i++) {
            $email = "assistant{$i}@medworld.com";
            if ($email !== 'assistant@medworld.com') {
                $this->createSpecificAssistant(
                    $firstNames[array_rand($firstNames)],
                    $lastNames[array_rand($lastNames)],
                    $email,
                    'password',
                    $cabinets[array_rand($cabinets)]->id
                );
            }
        }
    }

    private function createSpecificAssistant($firstName, $lastName, $email, $password, $cabinetId)
    {
        if (User::where('email', $email)->exists()) {
            return;
        }

        $user = User::create([
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'password' => $password,
            'phoneNumber' => '+1555' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT),
            'gender' => ['male', 'female'][rand(0, 1)],
            'dateOfBirth' => now()->subYears(rand(25, 40)),
            'type' => 'assistant',
            'address' => rand(1, 999) . ' Assistant Way',
        ]);

        $doctor = Doctor::where('cabinetId', $cabinetId)->first();

        Assistant::create([
            'userId' => $user->id,
            'cabinetId' => $cabinetId,
            'doctorId' => $doctor ? $doctor->id : null,
        ]);
    }

    private function seedPatients()
    {
        $this->createSpecificPatient('Alice', 'Williams', 'patient@medworld.com', 'patient123');

        $firstNames = ['James', 'Mary', 'Robert', 'Patricia', 'John', 'Jennifer', 'Michael', 'Linda', 'David', 'Elizabeth', 'William', 'Barbara', 'Richard', 'Susan', 'Joseph', 'Jessica', 'Thomas', 'Sarah', 'Charles', 'Karen'];
        $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin'];

        for ($i = 0; $i < 20; $i++) {
            $email = "patient{$i}@medworld.com";
            if ($email !== 'patient@medworld.com') {
                $this->createSpecificPatient(
                    $firstNames[array_rand($firstNames)],
                    $lastNames[array_rand($lastNames)],
                    $email,
                    'password'
                );
            }
        }

        return Patient::with('user')->get()->all();
    }

    private function createSpecificPatient($firstName, $lastName, $email, $password)
    {
        if (User::where('email', $email)->exists()) {
            return;
        }

        $user = User::create([
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'password' => $password,
            'phoneNumber' => '+1555' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT),
            'gender' => ['male', 'female'][rand(0, 1)],
            'dateOfBirth' => now()->subYears(rand(20, 70)),
            'type' => 'patient',
            'address' => rand(1, 999) . ' Patient Rd',
        ]);

        Patient::create([
            'userId' => $user->id,
            'emergencyContact' => '+1555' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT),
            'bloodType' => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'][rand(0, 7)],
            'weight' => rand(50, 100),
            'medicalHistory' => json_encode(rand(0, 1) === 0 ? [] : ['Hypertension', 'Diabetes']),
            'allergies' => json_encode(rand(0, 1) === 0 ? [] : ['Penicillin']),
        ]);
    }

    private function seedAppointmentsAndConsultations($doctors, $patients, $cabinets)
    {
        $statuses = [Appointment::STATUS_SCHEDULED, Appointment::STATUS_COMPLETED, Appointment::STATUS_CANCELLED, Appointment::STATUS_NO_SHOW];

        foreach ($patients as $patient) {
            $count = rand(1, 3);
            for ($i = 0; $i < $count; $i++) {
                $doctor = $doctors[array_rand($doctors)];
                $cabinetId = $doctor->cabinetId ?: $cabinets[0]->id;

                $daysOffset = rand(-365, 30);
                $date = now()->addDays($daysOffset);
                $status = $daysOffset > 0 ? Appointment::STATUS_SCHEDULED : $statuses[array_rand($statuses)];

                if ($daysOffset <= 0 && $status === Appointment::STATUS_SCHEDULED) {
                    $status = Appointment::STATUS_COMPLETED;
                }

                $appointment = Appointment::create([
                    'date' => $date,
                    'status' => $status,
                    'patientId' => $patient->id,
                    'doctorId' => $doctor->id,
                    'cabinetId' => $cabinetId,
                ]);

                if ($status === Appointment::STATUS_COMPLETED) {
                    Consultation::create([
                        'doctorId' => $doctor->id,
                        'patientId' => $patient->id,
                        'appointmentId' => $appointment->id,
                        'notes' => 'Routine checkup. Patient stable.',
                        'prescriptions' => json_encode(['Generic Medicine 500mg']),
                    ]);
                }
            }
        }
    }

    private function seedCalendars($doctors)
    {
        $baseDate = now();

        foreach ($doctors as $doctor) {
            $availability = [];
            for ($i = 1; $i <= 5; $i++) {
                $availability[] = [
                    'date' => $baseDate->copy()->addDays($i)->format('Y-m-d'),
                    'slots' => ['09:00', '09:30', '10:00', '11:00', '14:00', '15:00', '16:00'],
                ];
            }

            Calendar::create([
                'doctorId' => $doctor->id,
                'cabinetId' => $doctor->cabinetId,
                'availability' => json_encode($availability),
            ]);
        }
    }

    private function seedMessages()
    {
        $doctorUser = User::where('email', 'doctor@medworld.com')->first();
        $adminUser = User::where('email', 'admin.doctor@medworld.com')->first();

        if ($doctorUser && $adminUser) {
            Message::create([
                'senderId' => $adminUser->id,
                'receiverId' => $doctorUser->id,
                'content' => 'Welcome to the team, Dr. Smith!',
                'created_at' => now()->subDay(),
            ]);

            Message::create([
                'senderId' => $doctorUser->id,
                'receiverId' => $adminUser->id,
                'content' => 'Thank you! Glad to be here.',
                'created_at' => now()->subHours(23),
            ]);

            Message::create([
                'senderId' => $adminUser->id,
                'receiverId' => $doctorUser->id,
                'content' => 'Please check your calendar for the upcoming week.',
                'created_at' => now()->subHours(2),
            ]);
        }
    }
}
