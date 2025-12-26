import type { Rating } from "./rating";
import type { User, Users } from "./users";
import type { Admin } from "./users/admin";
import type { Assistant } from "./users/assistant";
import type { Doctor } from "./users/doctor";

export type Location = {
    address: string;
    latitude: number;
    longitude: number;
}

export interface Cabinet {
    id: number;
    name: string;
    phone: string;
    createdAt: Date;

    // Links
    adminId: number;
    admin: Admin;
    doctors: (Admin | Doctor)[];
    assistants: Assistant[];

    ratings: Rating[];

    // General Information
    image?: string;


    // Amenities
    accessHandicap: boolean;
    hasParking?: boolean;
    hasWifi?: boolean;
    acceptsUrgent?: boolean;
    acceptsInsurance?: boolean;

    // General Settings
    openingHours: Record<string, { open: string; close: string }>;

    location: Location;

}

export type CabinetFillable = Pick<Cabinet,
    | "name"
    | "phone"
    | "location"
    | "accessHandicap"
    | "hasParking"
    | "hasWifi"
    | "acceptsUrgent"
    | "acceptsInsurance"
    | "openingHours"
>;

export function calculateDistance(
    cabinet: Cabinet,
    userLocation: { latitude: number; longitude: number, [key: string]: any }
): number {
    const toRad = (value: number) => (value * Math.PI) / 180;
    const R = 6371;
    const dLat = toRad(userLocation.latitude - cabinet.location.latitude);
    const dLon = toRad(userLocation.longitude - cabinet.location.longitude);
    const lat1 = toRad(cabinet.location.latitude);
    const lat2 = toRad(userLocation.latitude);
    const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.sin(dLon / 2) * Math.sin(dLon / 2) * Math.cos(lat1) * Math.cos(lat2);

    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
}

export function isCabinetOpen(cabinet: Cabinet, date: Date = new Date()): boolean {
    return Math.random() > 0.8;
}
