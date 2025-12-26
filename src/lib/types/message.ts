import type { Cabinet } from "./cabinet";
import type { Consultation } from "./consultation";
import type { User, Users } from "./users/";
import type { Admin } from "./users/admin";
import type { Doctor } from "./users/doctor";

export type MessageContent = {
    message?: string;
    attachment: Consultation;
}

export type Message = {
    id:                 number;
    sender:             User<any>;
    receiver:           User<any>;
    createdAt:          Date;
    content:            string;
    isRead:             boolean;
}

