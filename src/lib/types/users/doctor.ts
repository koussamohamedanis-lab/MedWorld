import type { User, Users } from ".";
import type { Cabinet } from "../cabinet";
import type { Calendar } from "../calendar";
import type { Consultation } from "../consultation";
import type { Message } from "../message";
import type { Review } from "../reviews";
import type { Speciality } from "../speciality";
import type { Assistant } from "./assistant";

export interface Doctor extends User<any> {
    doctorId: number;

    // Speciality
    speciality: Speciality;
    licenseNumber: string;

    // Dates
    dateOfBirth: Date;
    careerStart: Date;

    // Messages
    messages: Message[];

    // Reviews
    reviews: Review[];

    // Calendars at different cabinets
    calendars: Calendar[];

    // Consultationss
    consultations: Consultation[];

    // Cabinets
    cabinetId: number;
    cabinet: Cabinet;

    // Assistant (exactly one in domain model)
    assistant?: Assistant;

    // Price of consultations (per doctor)
    consultationPrice: number;
    consultationDuration: number; // in minutes

    // To Calculate Years Of Experience
    getYearsOfExperience(): number;

}
