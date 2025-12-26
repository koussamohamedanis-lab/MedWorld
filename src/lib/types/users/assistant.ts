import type { IDoctor, User, Users } from "./index";
import type { Appointment } from "../appointment";
import type { Cabinet } from "../cabinet";

export interface Assistant extends User<Users.Assistant> {
    cabinet: Cabinet;
    appointments: Appointment[];

    doctorId: number;
    doctor: IDoctor;
    planAppointment(): void;
    cancelAppointment(): void;
    recordPayment(patientId: number, amount: number): void;
}
