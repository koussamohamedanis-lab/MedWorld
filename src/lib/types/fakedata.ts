import type { Doctor } from './users/doctor';
import type { Patient } from './users/patient';
import type { Assistant } from './users/assistant';
import type { Admin } from './users/admin';
import type { SuperAdmin } from './users/superadmin';
import type { Cabinet } from './cabinet';
import type { Appointment } from './appointment';
import { AppointmentStatus } from './appointment';
import type { Consultation } from './consultation';
import type { Rating } from './rating';
import { Speciality } from './speciality';
import { Users } from './users';
import { PlanID, plans } from './plan';

// ==================== HELPER FUNCTIONS ====================
function createDoctor(
    id: number,
    firstName: string,
    lastName: string,
    speciality: Speciality,
    cabinet: Cabinet,
    consultationPrice: number = 2000
): Doctor {
    return {
        id,
        doctorId: id,
        firstName,
        lastName,
        email: `${firstName.toLowerCase()}.${lastName.toLowerCase()}@medworld.dz`,
        phoneNumber: '+213 5' + Math.random().toString().slice(2, 11),
        address: cabinet.location.address,
        gender: Math.random() > 0.5 ? 'male' : 'female',
        dateOfBirth: new Date(1970 + Math.floor(Math.random() * 25), Math.floor(Math.random() * 12), Math.floor(Math.random() * 28) + 1),
        type: Users.Doctor,
        avatarUrl: `https://api.dicebear.com/7.x/avataaars/svg?seed=${id}`,

        speciality,
        licenseNumber: `LIC-${id}`,
        careerStart: new Date(2010 + Math.floor(Math.random() * 10), 0, 1),
        cabinetId: cabinet.id,
        cabinet,
        consultationPrice,
        consultationDuration: 30,

        calendars: [],

        messages: [],
        reviews: [],
        consultations: [],
        assistant: undefined,

        createdAt: new Date(),

        fullName: `${firstName} ${lastName}`,

        getYearsOfExperience(): number {
            return new Date().getFullYear() - this.careerStart.getFullYear();
        },
    };
}

function createPatient(id: number, firstName: string, lastName: string): Patient {
    return {
        id,
        patientId: id,
        firstName,
        lastName,
        email: `${firstName.toLowerCase()}.${lastName.toLowerCase()}@patient.com`,
        phoneNumber: '+213 5' + Math.random().toString().slice(2, 11),
        address: 'Algiers, Algeria',
        gender: Math.random() > 0.5 ? 'male' : 'female',
        dateOfBirth: new Date(1970 + Math.floor(Math.random() * 40), Math.floor(Math.random() * 12), Math.floor(Math.random() * 28) + 1),
        type: Users.Patient,
        avatarUrl: `https://api.dicebear.com/7.x/avataaars/svg?seed=patient${id}`,

        emergencyContact: '+213 5' + Math.random().toString().slice(2, 11),
        bloodType: ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'][Math.floor(Math.random() * 8)] as any,
        weight: 50 + Math.floor(Math.random() * 50),
        appointments: [],
        consultations: [],
        prescriptions: [],
        reviews: [],
        medicalHistory: [],
        allergies: [],

        createdAt: new Date(),

        fullName: `${firstName} ${lastName}`,

        addReview(): void { },
    };
}

function createAssistant(id: number, firstName: string, lastName: string, cabinet: Cabinet): Assistant {
    return {
        id,
        firstName,
        lastName,
        email: `${firstName.toLowerCase()}.${lastName.toLowerCase()}@assistant.com`,
        phoneNumber: '+213 5' + Math.random().toString().slice(2, 11),
        address: cabinet.location.address,
        gender: Math.random() > 0.5 ? 'male' : 'female',
        dateOfBirth: new Date(1980 + Math.floor(Math.random() * 30), Math.floor(Math.random() * 12), Math.floor(Math.random() * 28) + 1),
        type: Users.Assistant,
        avatarUrl: `https://api.dicebear.com/7.x/avataaars/svg?seed=assistant${id}`,

        cabinet,
        appointments: [],
        doctorId: 0,
        doctor: {} as any,

        createdAt: new Date(),

        fullName: `${firstName} ${lastName}`,

        planAppointment(): void { },
        cancelAppointment(): void { },
        recordPayment(): void { },
    };
}

function createAdmin(
    id: number,
    firstName: string,
    lastName: string,
    cabinet: Cabinet,
    doctors: Doctor[]
): Admin {
    const doctor = createDoctor(id, firstName, lastName, Speciality.CARDIOLOGY, cabinet);
    return {
        ...doctor,
        type: Users.Admin,
        plan: plans[PlanID.premium],
    };
}

// ==================== FAKE CABINETS ====================
export const fakeCabinets: Cabinet[] = [
    {
        id: 1,
        name: 'Heart Care Center',
        phone: '+213 21 123 4567',
        location: {
            address: '45 Rue Larbi Ben M\'hidi, Algiers, Algeria',
            latitude: 36.7372,
            longitude: 3.0588,
        },
        image: '/cabinet1.jpg',
        createdAt: new Date('2020-01-15'),

        accessHandicap: true,
        hasParking: true,
        hasWifi: true,
        acceptsUrgent: true,
        acceptsInsurance: true,

        openingHours: {
            sunday: { open: '08:00', close: '18:00' },
            monday: { open: '08:00', close: '20:00' },
            tuesday: { open: '08:00', close: '20:00' },
            wednesday: { open: '08:00', close: '20:00' },
            thursday: { open: '08:00', close: '20:00' },
            friday: { open: '09:00', close: '17:00' },
            saturday: { open: '09:00', close: '16:00' },
        },

        admin: {} as any,
        doctors: [],
        assistants: [],
        ratings: [],
    },
    {
        id: 2,
        name: 'General Medical Clinic',
        phone: '+213 21 987 6543',
        location: {
            address: '78 Avenue Didouche Mourad, Algiers, Algeria',
            latitude: 36.7538,
            longitude: 3.0588,
        },
        image: '/cabinet2.jpg',
        createdAt: new Date('2019-05-20'),

        accessHandicap: false,
        hasParking: true,
        hasWifi: true,
        acceptsUrgent: false,
        acceptsInsurance: true,

        openingHours: {
            sunday: { open: '09:00', close: '17:00' },
            monday: { open: '08:00', close: '18:00' },
            tuesday: { open: '08:00', close: '18:00' },
            wednesday: { open: '08:00', close: '18:00' },
            thursday: { open: '08:00', close: '18:00' },
            friday: { open: '10:00', close: '16:00' },
            saturday: { open: '10:00', close: '14:00' },
        },

        admin: {} as any,
        doctors: [],
        assistants: [],
        ratings: [],
    },
    {
        id: 3,
        name: 'Advanced Pediatric Hospital',
        phone: '+213 21 555 8901',
        location: {
            address: '12 Boulevard Emir Abdelkader, Algiers, Algeria',
            latitude: 36.7452,
            longitude: 3.0720,
        },
        image: '/cabinet3.jpg',
        createdAt: new Date('2018-03-10'),

        accessHandicap: true,
        hasParking: true,
        hasWifi: true,
        acceptsUrgent: true,
        acceptsInsurance: true,

        openingHours: {
            sunday: { open: '08:00', close: '20:00' },
            monday: { open: '08:00', close: '20:00' },
            tuesday: { open: '08:00', close: '20:00' },
            wednesday: { open: '08:00', close: '20:00' },
            thursday: { open: '08:00', close: '20:00' },
            friday: { open: '09:00', close: '19:00' },
            saturday: { open: '09:00', close: '17:00' },
        },

        admin: {} as any,
        doctors: [],
        assistants: [],
        ratings: [],
    },
];

// ==================== CREATE ADMINS ====================
export const fakeAdmins: Admin[] = [
    createAdmin(101, 'Karim', 'Youssef', fakeCabinets[0], []),
    createAdmin(102, 'Leila', 'Djaout', fakeCabinets[1], []),
    createAdmin(103, 'Ahmed', 'Benali', fakeCabinets[2], []),
];

// Update cabinets with their admins
fakeCabinets[0].admin = fakeAdmins[0];
fakeCabinets[1].admin = fakeAdmins[1];
fakeCabinets[2].admin = fakeAdmins[2];

// ==================== CREATE DOCTORS ====================
export const fakeDoctors: Doctor[] = [
    createDoctor(201, 'Dr. Mohammed', 'Saïdi', Speciality.CARDIOLOGY, fakeCabinets[0], 2500),
    createDoctor(202, 'Dr. Zahra', 'Bennani', Speciality.PEDIATRICS, fakeCabinets[0], 2000),
    createDoctor(203, 'Dr. Farid', 'Medjahed', Speciality.ORTHOPEDICS, fakeCabinets[1], 2200),
    createDoctor(204, 'Dr. Yasmine', 'Bouamama', Speciality.DERMATOLOGY, fakeCabinets[1], 1900),
    createDoctor(205, 'Dr. Imane', 'Mahrez', Speciality.PEDIATRICS, fakeCabinets[2], 2100),
    createDoctor(206, 'Dr. Hassan', 'Bouhadjar', Speciality.NEUROLOGY, fakeCabinets[2], 2400),
];

// Add doctors to cabinets
fakeCabinets[0].doctors = [fakeAdmins[0], ...fakeDoctors.filter((d) => d.cabinet.id === 1)];
fakeCabinets[1].doctors = [fakeAdmins[1], ...fakeDoctors.filter((d) => d.cabinet.id === 2)];
fakeCabinets[2].doctors = [fakeAdmins[2], ...fakeDoctors.filter((d) => d.cabinet.id === 3)];

// ==================== CREATE ASSISTANTS ====================
export const fakeAssistants: Assistant[] = [
    createAssistant(301, 'Amal', 'Berghout', fakeCabinets[0]),
    createAssistant(302, 'Nadia', 'Hamidou', fakeCabinets[1]),
    createAssistant(303, 'Jalal', 'Ouartchi', fakeCabinets[2]),
];

// Add assistants to cabinets
fakeCabinets[0].assistants = [fakeAssistants[0]];
fakeCabinets[1].assistants = [fakeAssistants[1]];
fakeCabinets[2].assistants = [fakeAssistants[2]];

// ==================== CREATE PATIENTS ====================
export const fakePatients: Patient[] = [
    createPatient(401, 'Ali', 'Benhamouda'),
    createPatient(402, 'Fatima', 'Boutaleb'),
    createPatient(403, 'Hassan', 'Cheikh'),
    createPatient(404, 'Samira', 'Djelloul'),
    createPatient(405, 'Karim', 'Eddaoudi'),
    createPatient(406, 'Leila', 'Fouad'),
    createPatient(407, 'Malik', 'Gaidi'),
    createPatient(408, 'Noor', 'Hamza'),
];

// ==================== CREATE APPOINTMENTS ====================
export const fakeAppointments: Appointment[] = [
    {
        id: 501,
        date: new Date(2025, 11, 20, 10, 0),
        status: AppointmentStatus.CONFIRMED,
        patient: fakePatients[0],
        doctor: fakeDoctors[0],
        cabinet: fakeCabinets[0],
        createdAt: new Date(),
    },
    {
        id: 502,
        date: new Date(2025, 11, 21, 14, 30),
        status: AppointmentStatus.SCHEDULED,
        patient: fakePatients[1],
        doctor: fakeDoctors[1],
        cabinet: fakeCabinets[0],
        createdAt: new Date(),
    },
    {
        id: 503,
        date: new Date(2025, 11, 22, 9, 0),
        status: AppointmentStatus.COMPLETED,
        patient: fakePatients[2],
        doctor: fakeDoctors[2],
        cabinet: fakeCabinets[1],
        createdAt: new Date(),
    },
    {
        id: 504,
        date: new Date(2025, 11, 23, 11, 30),
        status: AppointmentStatus.CANCELLED,
        patient: fakePatients[3],
        doctor: fakeDoctors[3],
        cabinet: fakeCabinets[1],
        createdAt: new Date(),
    },
];

// Add appointments to patients
fakePatients[0].appointments = [fakeAppointments[0]];
fakePatients[1].appointments = [fakeAppointments[1]];
fakePatients[2].appointments = [fakeAppointments[2]];
fakePatients[3].appointments = [fakeAppointments[3]];

// ==================== CREATE CONSULTATIONS ====================
export const fakeConsultations: Consultation[] = [
    {
        id: 601,
        doctor: fakeDoctors[0],
        patient: fakePatients[0],
        appointment: fakeAppointments[0],
        notes: 'Patient shows signs of high blood pressure. Recommended diet changes and exercise.',
        prescriptions: ['Lisinopril 10mg daily', 'Aspirin 100mg daily'],
        attachments: [],
        createdAt: new Date(),
    },
    {
        id: 602,
        doctor: fakeDoctors[1],
        patient: fakePatients[1],
        appointment: fakeAppointments[1],
        notes: 'Child has mild fever. Prescribed paracetamol and rest.',
        prescriptions: ['Paracetamol 250mg every 6 hours'],
        attachments: [],
        createdAt: new Date(),
    },
];

// Add consultations
fakePatients[0].consultations = [fakeConsultations[0]];
fakePatients[1].consultations = [fakeConsultations[1]];
fakeAppointments[0].consultation = fakeConsultations[0];
fakeAppointments[1].consultation = fakeConsultations[1];

// ==================== CREATE RATINGS ====================
export const fakeRatings: Rating[] = [
    {
        id: 701,
        patient: fakePatients[0],
        cabinet: fakeCabinets[0],
        date: new Date(2025, 10, 15),
        equippement: {
            disponibility: true,
            rating: 4.5,
        },
        userExperience: {
            reception: 4,
            hygiene: 5,
            waitTime: 3,
            communication: 4,
            professionalism: 5,
            emplacement: 4,
        },
        review: 'Great clinic with excellent staff and modern equipment.',
    },
    {
        id: 702,
        patient: fakePatients[1],
        cabinet: fakeCabinets[0],
        date: new Date(2025, 10, 10),
        equippement: {
            disponibility: true,
            rating: 4,
        },
        userExperience: {
            reception: 3,
            hygiene: 4,
            waitTime: 2,
            communication: 4,
            professionalism: 4,
            emplacement: 3,
        },
        review: 'Good service but a bit crowded.',
    },
    {
        id: 703,
        patient: fakePatients[2],
        cabinet: fakeCabinets[1],
        date: new Date(2025, 10, 5),
        equippement: {
            disponibility: true,
            rating: 3.5,
        },
        userExperience: {
            reception: 3,
            hygiene: 3,
            waitTime: 4,
            communication: 3,
            professionalism: 4,
            emplacement: 4,
        },
        review: 'Average experience. Could improve waiting times.',
    },
];

// Add ratings to cabinets
fakeCabinets[0].ratings = [fakeRatings[0], fakeRatings[1]];
fakeCabinets[1].ratings = [fakeRatings[2]];

// ==================== CREATE SUPERADMIN ====================
export const fakeSuperAdmin: SuperAdmin = {
    id: 1,
    firstName: 'Houria',
    lastName: 'Aichi',
    email: 'houria.aichi@medworld.dz',
    phoneNumber: '+213 555 222 000',
    address: 'Algiers, Algeria',
    gender: 'female',
    dateOfBirth: new Date('1985-06-15'),
    type: Users.SuperAdmin,
    avatarUrl: 'https://api.dicebear.com/7.x/avataaars/svg?seed=superadmin',

    createdCabinets: fakeCabinets,

    fullName: `Houria Aichi`,

    createCabinet(): void { },
    deleteCabinet(): void { },
    sendGlobalNotification(): void { },
};
