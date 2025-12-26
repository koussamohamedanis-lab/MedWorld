import type { User, Users } from "./index";
import type { Appointment } from "../appointment";
import type { Consultation } from "../consultation";
import type { Review } from "../reviews";
import type { Prescription } from "../prescription";

export type BloodType = "A+" | "A-" | "B+" | "B-" | "AB+" | "AB-" | "O+" | "O-"
export const bloodTypes: BloodType[] = ["A+", "A-", "B+", "B-", "AB+", "AB-", "O+", "O-"];

export interface Patient extends User<Users.Patient> {
    patientId: number;
    emergencyContact?: string;
    bloodType?: BloodType;
    appointments: Appointment[];
    consultations: Consultation[];
    prescriptions?: Prescription[];

    reviews: Review[];
    weight?: number;

    medicalHistory?: string[];
    allergies?: string[];

    addReview(review: Review): void;
}
