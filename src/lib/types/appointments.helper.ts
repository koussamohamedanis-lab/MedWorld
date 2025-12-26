import type { Appointment } from './appointment';
import type { Doctor } from './users/doctor';
import type { Patient } from './users/patient';
import type { Cabinet } from './cabinet';

export const createAppointment = (
    id: number,
    doctorId: number,
    patientId: number,
    cabinetId: number,
    date: Date,
    status: 'pending' | 'confirmed' | 'cancelled' | 'completed',
    createdAt?: Date
): Appointment => ({
    id,
    doctorId,
    patientId,
    cabinetId,
    date,
    status,
    createdAt: createdAt || new Date(),
    updatedAt: undefined
});

export const createFutureAppointments = (
    startId: number,
    doctor: Doctor,
    patient: Patient,
    cabinet: Cabinet,
    count: number = 1
): Appointment[] => {
    const appointments: Appointment[] = [];
    const now = new Date();

    for (let i = 0; i < count; i++) {

        const futureDate = new Date(now.getFullYear(), now.getMonth() + 1 + i,
            15 + Math.floor(Math.random() * 10),
            9 + Math.floor(Math.random() * 6),
            0, 0);

        appointments.push(createAppointment(
            startId + i,
            doctor.id,
            patient.id,
            cabinet.id,
            futureDate,
            'pending'
        ));
    }

    return appointments;
};
