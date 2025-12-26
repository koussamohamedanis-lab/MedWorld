import { writable, type Writable } from 'svelte/store';
import type { Doctor } from '../types/users/doctor';
import type { Cabinet } from '../types/cabinet';
import type { Patient } from '../types/users/patient';
import type { Assistant } from '../types/users/assistant';
import type { Appointment } from '../types/appointment';
import type { Consultation } from '../types/consultation';
import { AllAPI, DoctorAPI, CabinetAPI, PatientAPI, AppointmentAPI, ConsultationAPI } from '../api';

// ==================== DATA STORES ====================

/**
 * All doctors available in the system
 */
export const doctors: Writable<Doctor[]> = writable([]);

/**
 * All cabinets available in the system
 */
export const cabinets: Writable<Cabinet[]> = writable([]);

/**
 * All patients (for doctors/admins to view)
 */
export const patients: Writable<Patient[]> = writable([]);

/**
 * All assistants
 */
export const assistants: Writable<Assistant[]> = writable([]);

/**
 * All appointments
 */
export const appointments: Writable<Appointment[]> = writable([]);

/**
 * All consultations
 */
export const consultations: Writable<Consultation[]> = writable([]);

/**
 * Loading states
 */
export const isLoadingDoctors: Writable<boolean> = writable(false);
export const isLoadingCabinets: Writable<boolean> = writable(false);
export const isLoadingPatients: Writable<boolean> = writable(false);
export const isLoadingAssistants: Writable<boolean> = writable(false);
export const isLoadingAppointments: Writable<boolean> = writable(false);
export const isLoadingConsultations: Writable<boolean> = writable(false);

/**
 * Error states
 */
export const doctorError: Writable<string | null> = writable(null);
export const cabinetError: Writable<string | null> = writable(null);
export const patientError: Writable<string | null> = writable(null);
export const assistantError: Writable<string | null> = writable(null);
export const appointmentError: Writable<string | null> = writable(null);
export const consultationError: Writable<string | null> = writable(null);

// ==================== LOAD FUNCTIONS ====================

/**
 * Load all doctors from the backend
 */
export async function loadAllDoctors() {
    isLoadingDoctors.set(true);
    doctorError.set(null);
    try {
        const data = await AllAPI.listAllDoctors();
        doctors.set(data);
    } catch (error: any) {
        doctorError.set(error.message || 'Failed to load doctors');
        doctors.set([]);
    } finally {
        isLoadingDoctors.set(false);
    }
}

/**
 * Load all cabinets from the backend
 */
export async function loadAllCabinets() {
    isLoadingCabinets.set(true);
    cabinetError.set(null);
    try {
        const data = await CabinetAPI.list();
        console.log("Cabinets:", data);
        cabinets.set(data);
    } catch (error: any) {
        cabinetError.set(error.message || 'Failed to load cabinets');
        cabinets.set([]);
    } finally {
        isLoadingCabinets.set(false);
    }
}

/**
 * Load all patients from the backend
 */
export async function loadAllPatients() {
    isLoadingPatients.set(true);
    patientError.set(null);
    try {
        const data = await AllAPI.listAllPatients();
        patients.set(data);
    } catch (error: any) {
        patientError.set(error.message || 'Failed to load patients');
        patients.set([]);
    } finally {
        isLoadingPatients.set(false);
    }
}

/**
 * Load all assistants from the backend
 */
export async function loadAllAssistants() {
    isLoadingAssistants.set(true);
    assistantError.set(null);
    try {
        const data = await AllAPI.listAllAssistants();
        assistants.set(data);
    } catch (error: any) {
        assistantError.set(error.message || 'Failed to load assistants');
        assistants.set([]);
    } finally {
        isLoadingAssistants.set(false);
    }
}

/**
 * Load all appointments from the backend
 */
export async function loadAllAppointments() {
    isLoadingAppointments.set(true);
    appointmentError.set(null);
    try {
        const data = await AllAPI.listAllAppointments();
        appointments.set(data);
    } catch (error: any) {
        appointmentError.set(error.message || 'Failed to load appointments');
        appointments.set([]);
    } finally {
        isLoadingAppointments.set(false);
    }
}

/**
 * Load all consultations from the backend
 */
export async function loadAllConsultations() {
    isLoadingConsultations.set(true);
    consultationError.set(null);
    try {
        const data = await AllAPI.listAllConsultations();
        consultations.set(data);
    } catch (error: any) {
        consultationError.set(error.message || 'Failed to load consultations');
        consultations.set([]);
    } finally {
        isLoadingConsultations.set(false);
    }
}

/**
 * Load all essential data for the app
 */
export async function loadAllData() {
    // We should not load everything globally anymore as it causes 403s for regular users.
    // Components should load what they need based on their own permissions.
    console.log("loadAllData: Skipping global load to avoid 403 errors. Components will load data as needed.");
}

// ==================== SEARCH FUNCTIONS ====================

/**
 * Search doctors by criteria
 */
export async function searchDoctors(filters: {
    speciality?: string;
    cabinetId?: number;
    priceMax?: number;
    priceMin?: number;
    available?: boolean;
}): Promise<Doctor[]> {
    try {
        return await DoctorAPI.search(filters);
    } catch (error) {
        console.error('Error searching doctors:', error);
        return [];
    }
}
