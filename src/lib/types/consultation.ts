import type { Appointment } from "./appointment";
import type { User, Users } from "./users";
import type { Doctor } from "./users/doctor";
import type { Patient } from "./users/patient";

export interface Consultation {
    id: number;
    doctor: Doctor;
    patient: Patient;
    appointment: Appointment;

    notes: string;
    prescriptions: string[];
    attachments?: string[]; // file URLs

    createdAt: Date;
    updatedAt?: Date;
}
