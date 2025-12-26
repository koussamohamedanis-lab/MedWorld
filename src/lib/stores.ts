import { get, writable, type Writable } from "svelte/store";
import type { IUser, User } from "./types/users";
import type { Group } from "./types/permission";
import type { Cabinet, Location } from "./types/cabinet";
import { UserAPI, AuthAPI } from "./api";

function normalizeUser(u: any): any {
    if (!u) return u;

    // Ensure patient optional fields exist to avoid undefined access in UI.
    if (u.type === "patient") {
        return {
            ...u,
            appointments: Array.isArray(u.appointments) ? u.appointments : [],
            consultations: Array.isArray(u.consultations) ? u.consultations : [],
            prescriptions: Array.isArray(u.prescriptions) ? u.prescriptions : [],
            reviews: Array.isArray(u.reviews) ? u.reviews : [],
            medicalHistory: Array.isArray(u.medicalHistory) ? u.medicalHistory : [],
            allergies: Array.isArray(u.allergies) ? u.allergies : [],
            weight: typeof u.weight === "number" ? u.weight : 0,
            emergencyContact: typeof u.emergencyContact === "string" ? u.emergencyContact : "",
            bloodType: u.bloodType ?? "",
        };
    }

    return u;
}

export const user: Writable<User<any> | null> = writable(null);

export async function loadUser() {
    try {
        // First, check if we have a valid token
        const token = localStorage.getItem('authToken');
        const storedUser = localStorage.getItem('user');

        if (!token) {
            // No token, user is not authenticated
            user.set(null);
            return null;
        }

        // If we have a stored user, set it initially to avoid flicker
        if (storedUser) {
            try {
                user.set(normalizeUser(JSON.parse(storedUser)));
            } catch (e) {
                console.error('Error parsing stored user:', e);
            }
        }

        // Try to fetch current user from API using the token to ensure it's still valid
        const result = await AuthAPI.me();
        if (result && result.user) {
            user.set(normalizeUser(result.user));
            // Update localStorage
            localStorage.setItem('user', JSON.stringify(normalizeUser(result.user)));
            localStorage.setItem('userID', result.user.id.toString());
            return result.user;
        } else {
            // API call failed or user not found, clear auth
            user.set(null);
            localStorage.removeItem('authToken');
            localStorage.removeItem('user');
            localStorage.removeItem('userID');
            return null;
        }
    } catch (error) {
        // Error loading user, clear auth state
        user.set(null);
        localStorage.removeItem('authToken');
        localStorage.removeItem('user');
        localStorage.removeItem('userID');
        console.error('Error loading user:', error);
        return null;
    }
}

export const currentBlock: Writable<Array<Group>> = writable([]);
export const currentLevel: Writable<number> = writable(0);

export function blockBack() {
    currentBlock.update(e => {
        const copy = [...(e || [])];
        copy.pop();
        return copy;
    });

    currentLevel.update(c => --c);
}

export function gotoBlock(block: Group) {
    currentBlock.update(e => [...e, block]);
    currentLevel.update(c => ++c);
}

export function isInBlock(block: Group): boolean {
    return block.length > 0 && get(currentBlock)[get(currentBlock).length - 1] === block;
}

export function gotoBlockAt(depth: number, block: Group) {
    currentBlock.update(e => {
        const copy = [...(e || [])];
        copy[depth] = block;
        copy.length = depth + 1;
        return copy;
    });
}

export function popTo(depth: number) {
    currentBlock.update(e => {
        const copy = [...(e || [])];
        copy.length = Math.max(0, depth);
        return copy;
    });
}


export const currentCabinet = writable<Cabinet | null>(null);

export const userLocation: Writable<Location> = writable({
    // Algiers
    address: "Algiers, Algeria",
    latitude: 36.7538,
    longitude: 3.0588,
})

//ik but this code is for messagestore so i put it here
// You should use: let messages = ($user as Doctor).messages // gives Message[]
//  {#each messages as message}
// h1 {message.sender.name}
// You
// {message.content}
