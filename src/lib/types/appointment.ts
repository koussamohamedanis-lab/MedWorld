import type { User, Users } from "./users";
import type { Cabinet } from "./cabinet";
import type { Doctor } from "./users/doctor";
import type { Patient } from "./users/patient";
import type { Consultation } from "./consultation";

export enum AppointmentStatus {
    SCHEDULED = 'SCHEDULED',
    CONFIRMED = 'CONFIRMED',
    IN_PROGRESS = 'IN_PROGRESS',
    COMPLETED = 'COMPLETED',
    CANCELLED = 'CANCELLED',
    NO_SHOW = 'NO_SHOW'
}

export interface Appointment {
    id: number;
    date: Date;
    status: AppointmentStatus;

    patient: Patient;
    doctor: Doctor;
    cabinet: Cabinet;

    consultation?: Consultation;

    createdAt: Date;
    updatedAt?: Date;
}
