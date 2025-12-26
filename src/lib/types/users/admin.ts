import type { User, Users } from ".";
import type { Plan } from "../plan";
import type { Doctor } from "./doctor";

export interface Admin extends Doctor {
    type: Users.Admin,
    plan?: Plan;
}
