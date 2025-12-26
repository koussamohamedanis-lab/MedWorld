import type { Cabinet } from "./cabinet";
import type { User, Users } from "./users";
import type { Doctor } from "./users/doctor";

export interface Calendar {
    id: number;
    cabinet: Cabinet;
    doctor: Doctor;
    availability: Array<{
        date: string; // 'YYYY-MM-DD'
        slots: string[]; // Array of time slots in 'HH:MM' format
    }>;

}


// Availiabvility example:
