import type { Consultation } from "./consultation";
import type { Medication } from "./medication";
import type { Doctor } from "./users/doctor";
import type { Patient } from "./users/patient";



export enum ConsultationType {
    FIRST_VISIT = 'FIRST_VISIT',
    FOLLOW_UP = 'FOLLOW_UP',
    EMERGENCY = 'EMERGENCY',
    ROUTINE_CHECKUP = 'ROUTINE_CHECKUP'
}

export enum PrescriptionStatus {
    ACTIVE = 'ACTIVE',
    COMPLETED = 'COMPLETED',
    CANCELLED = 'CANCELLED'
}

export enum Gender {
    MALE = 'MALE',
    FEMALE = 'FEMALE',
    OTHER = 'OTHER'
}

export interface Prescription {
    id: string;

    // Direct relationships
    consultation: Consultation;
    patient: Patient;
    doctor: Doctor;

    // Prescription details
    prescriptionDate: Date;
    status: PrescriptionStatus;

    // Medications
    medications: Medication[];

    // Instructions
    generalInstructions?: string;

    // Validity
    validUntil?: Date;
    refillsAllowed: number;
    refillsUsed: number;

    // Metadata
    createdAt: Date;
    updatedAt: Date;
}
