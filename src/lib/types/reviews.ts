import type { User, Users } from "./users"
import type { Doctor } from "./users/doctor";

export type Review = {
    from: User<Users.Patient>;
    to: Doctor | User<Users.Admin>;
    rating: number;
    message: string;
}
