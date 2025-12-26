import type { User, Users } from "./index";
import type { Cabinet } from "../cabinet";

export interface SuperAdmin extends User<Users.SuperAdmin> {
    createdCabinets: Cabinet[];

    // Methods
    createCabinet(cabinet: Cabinet): void;
    deleteCabinet(cabinetId: number): void;
    sendGlobalNotification(message: string): void;
}
