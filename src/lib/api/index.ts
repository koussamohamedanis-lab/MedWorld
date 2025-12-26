import { apiClient } from "./client";
import { Users, type IUser, type User } from "../types/users";
import type { Cabinet } from "../types/cabinet";
import type { Appointment } from "../types/appointment";
import type { Consultation } from "../types/consultation";
import type { Doctor } from "../types/users/doctor";
import type { Patient } from "../types/users/patient";
import type { Assistant } from "../types/users/assistant";

// ==================== HELPERS ====================
function transformCabinet(cabinet: any): Cabinet {
  if (!cabinet) return cabinet;

  const transformed = { ...cabinet };

  if (typeof transformed.location === 'string') {
    try {
      transformed.location = JSON.parse(transformed.location);
    } catch (e) {
      console.error('Error parsing cabinet location:', e);
    }
  }

  if (typeof transformed.openingHours === 'string') {
    try {
      transformed.openingHours = JSON.parse(transformed.openingHours);
    } catch (e) {
      console.error('Error parsing cabinet openingHours:', e);
    }
  }

  return transformed as Cabinet;
}

function transformCabinets(cabinets: any[]): Cabinet[] {
  return (cabinets || []).map(transformCabinet);
}

// ==================== CALENDAR API ====================
export const CalendarAPI = {
  async list(params?: { doctorId?: number; cabinetId?: number }): Promise<any[]> {
    const qs = new URLSearchParams();
    if (params?.doctorId) qs.set("doctorId", String(params.doctorId));
    if (params?.cabinetId) qs.set("cabinetId", String(params.cabinetId));
    const suffix = qs.toString() ? `?${qs.toString()}` : "";
    return (await apiClient.get<any[]>(`/calendars${suffix}`)) || [];
  },

  async create(payload: { doctorId: number; availability?: any[] }): Promise<any> {
    return apiClient.post("/calendars", {
      doctorId: payload.doctorId,
      availability: payload.availability || [],
    });
  },
};

// ==================== AUTH API ====================
export const AuthAPI = {
  async login(
    email: string,
    password: string
  ): Promise<{ user: User<any>; token: string }> {
    return apiClient.post("/auth/login", { email, password });
  },

  async register(userData: {
    firstName: string;
    lastName: string;
    email: string;
    password: string;
    phoneNumber?: string;
    address?: string;
    gender?: "male" | "female";
    dateOfBirth?: string;
    type: "patient" | "doctor" | "admin" | "assistant" | "superadmin";
  }): Promise<{ user: User<any>; token: string }> {
    return apiClient.post("/auth/register", userData);
  },

  async logout(): Promise<void> {
    return apiClient.post("/auth/logout");
  },

  async me(): Promise<{ user: User<any> }> {
    return apiClient.get("/auth/me");
  },
};

// ==================== ALL API ====================
export const AllAPI = {
  async listAllAppointments(): Promise<Appointment[]> {
    return (await apiClient.get<Appointment[]>("/all/appointments")) || [];
  },

  async listAllDoctors(): Promise<Doctor[]> {
    return (await apiClient.get<Doctor[]>("/all/doctors")) || [];
  },

  async listAllPatients(): Promise<Patient[]> {
    return (await apiClient.get<Patient[]>("/all/patients")) || [];
  },

  async listAllAssistants(): Promise<Assistant[]> {
    return (await apiClient.get<Assistant[]>("/all/assistants")) || [];
  },

  async listAllCabinets(): Promise<Cabinet[]> {
    const response = await apiClient.get<Cabinet[]>("/all/cabinets");
    return transformCabinets(response);
  },

  async listAllUsers(): Promise<User<any>[]> {
    return (await apiClient.get<User<any>[]>("/all/users")) || [];
  },

  async listAllConsultations(): Promise<Consultation[]> {
    return (await apiClient.get<Consultation[]>("/all/consultations")) || [];
  },
};

// ==================== CABINET API ====================
export const CabinetAPI = {
  async list(): Promise<Cabinet[]> {
    const response = await apiClient.get<Cabinet[]>("/cabinets");
    return transformCabinets(response);
  },

  async getById(id: number): Promise<Cabinet | null> {
    try {
      const cabinet = await apiClient.get<Cabinet>(`/cabinets/${id}`);
      return transformCabinet(cabinet);
    } catch (error) {
      return null;
    }
  },

  async getDoctors(cabinetId: number): Promise<Doctor[]> {
    return (await apiClient.get<Doctor[]>(`/cabinets/${cabinetId}/doctors`)) || [];
  },

  async getAppointments(cabinetId: number): Promise<Appointment[]> {
    return (await apiClient.get<Appointment[]>(`/cabinets/${cabinetId}/appointments`)) || [];
  },

  async getAssistants(cabinetId: number): Promise<Assistant[]> {
    return (await apiClient.get<Assistant[]>(`/cabinets/${cabinetId}/assistants`)) || [];
  },

  async create(cabinet: Partial<Cabinet>): Promise<Cabinet> {
    const response = await apiClient.post<Cabinet>("/cabinets", cabinet);
    return transformCabinet(response);
  },

  async update(id: number, cabinet: Partial<Cabinet>): Promise<Cabinet> {
    const response = await apiClient.put<Cabinet>(`/cabinets/${id}`, cabinet);
    return transformCabinet(response);
  },

  async delete(id: number): Promise<void> {
    return apiClient.delete(`/cabinets/${id}`);
  },

  async addDoctor(id: number, email: string): Promise<void> {
    return apiClient.post(`/cabinets/${id}/doctors`, { email });
  },

  async createDoctor(
    id: number,
    payload: {
      firstName: string;
      lastName: string;
      email: string;
      password: string;
      phoneNumber?: string;
      address?: string;
      gender?: string;
      speciality?: string;
      licenseNumber?: string;
      consultationPrice?: number;
      consultationDuration?: number;
      yearsOfExperience?: number;
    },
  ): Promise<any> {
    return apiClient.post(`/cabinets/${id}/doctors/create`, payload);
  },

  async removeDoctor(id: number, doctorId: number): Promise<void> {
    return apiClient.delete(`/cabinets/${id}/doctors/${doctorId}`);
  },

  async setAdminDoctor(id: number, doctorId: number): Promise<any> {
    return apiClient.put(`/cabinets/${id}/doctors/${doctorId}/set-admin`, {});
  },

  async addAssistant(id: number, email: string, doctorId?: number): Promise<void> {
    return apiClient.post(`/cabinets/${id}/assistants`, {
      email,
      doctorId,
    });
  },

  async assignAssistant(id: number, assistantId: number, doctorId: number): Promise<void> {
    return apiClient.put(`/cabinets/${id}/assistants/${assistantId}/assign`, {
      doctorId,
    });
  },

  async createAssistant(
    id: number,
    payload: {
      firstName: string;
      lastName: string;
      email: string;
      password: string;
      phoneNumber?: string;
      address?: string;
      gender?: string;
      doctorId: number;
    },
  ): Promise<void> {
    return apiClient.post(`/cabinets/${id}/assistants/create`, payload);
  },

  async removeAssistant(id: number, assistantId: number): Promise<void> {
    return apiClient.delete(`/cabinets/${id}/assistants/${assistantId}`);
  },

  async bootstrapAdminDoctor(
    id: number,
    payload: {
      firstName: string;
      lastName: string;
      email: string;
      password: string;
      phoneNumber?: string;
      address?: string;
      gender?: string;
    },
  ): Promise<any> {
    return apiClient.post(`/cabinets/${id}/bootstrap-admin`, payload);
  },
};

// ==================== USER API ====================
export const UserAPI = {
  async UpdateProfile(user: User<any>, newUser: User<any>) {
    return apiClient.put(`/users/${user.id}`, newUser);
  },

  async getById(userID: User<any>["id"]): Promise<IUser | null> {
    try {
      return await apiClient.get(`/users/${userID}`);
    } catch (error) {
      return null;
    }
  },
};

// ==================== DOCTOR API ====================
export const DoctorAPI = {
  async list(): Promise<Doctor[]> {
    return (await apiClient.get<Doctor[]>("/doctors")) || [];
  },

  async getById(id: number): Promise<Doctor | null> {
    try {
      return await apiClient.get(`/doctors/${id}`);
    } catch (error) {
      return null;
    }
  },

  async getAppointments(doctorId: number): Promise<Appointment[]> {
    return (await apiClient.get<Appointment[]>(`/doctors/${doctorId}/appointments`)) || [];
  },

  async getConsultations(doctorId: number): Promise<Consultation[]> {
    return (await apiClient.get<Consultation[]>(`/doctors/${doctorId}/consultations`)) || [];
  },

  async getAvailability(doctorId: number, date: string): Promise<string[]> {
    const res = await apiClient.get<any>(`/doctors/${doctorId}/availability?date=${encodeURIComponent(date)}`);
    return (res as any)?.availableSlots || [];
  },

  async getPatients(
    doctorId: User<any>["id"],
    cabinetId?: Cabinet["id"]
  ): Promise<Patient[]> {
    let endpoint = `/doctors/${doctorId}/patients`;
    if (cabinetId !== undefined) {
      endpoint += `?cabinet_id=${cabinetId}`;
    }
    return (await apiClient.get<Patient[]>(endpoint)) || [];
  },

  async search(filters: {
    speciality?: string;
    cabinetId?: number;
    priceMax?: number;
    priceMin?: number;
    available?: boolean;
  }): Promise<Doctor[]> {
    const params = new URLSearchParams();
    if (filters.speciality) params.append("speciality", filters.speciality);
    if (filters.cabinetId)
      params.append("cabinet_id", filters.cabinetId.toString());
    if (filters.priceMax !== undefined)
      params.append("price_max", filters.priceMax.toString());
    if (filters.priceMin !== undefined)
      params.append("price_min", filters.priceMin.toString());
    if (filters.available !== undefined)
      params.append("available", filters.available.toString());

    const endpoint = `/doctors/search/filter?${params.toString()}`;
    return (await apiClient.get<Doctor[]>(endpoint)) || [];
  },
};

// ==================== PATIENT API ====================
export const PatientAPI = {
  async list(): Promise<Patient[]> {
    return (await apiClient.get<Patient[]>("/patients")) || [];
  },

  async getById(id: number): Promise<Patient | null> {
    try {
      return await apiClient.get(`/patients/${id}`);
    } catch (error) {
      return null;
    }
  },

  async getAppointments(patientId: number): Promise<Appointment[]> {
    return (await apiClient.get<Appointment[]>(`/patients/${patientId}/appointments`)) || [];
  },

  async getConsultations(patientId: number): Promise<Consultation[]> {
    return (await apiClient.get<Consultation[]>(`/patients/${patientId}/consultations`)) || [];
  },
};

// ==================== APPOINTMENT API ====================
export const AppointmentAPI = {
  async list(): Promise<Appointment[]> {
    return (await apiClient.get<Appointment[]>("/appointments")) || [];
  },

  async getById(id: number): Promise<Appointment | null> {
    try {
      return await apiClient.get(`/appointments/${id}`);
    } catch (error) {
      return null;
    }
  },

  async create(
    appointment: Omit<Appointment, "id" | "createdAt" | "updatedAt">
  ): Promise<Appointment> {
    return apiClient.post("/appointments", appointment);
  },

  async updateStatus(
    id: number,
    status: Appointment["status"]
  ): Promise<Appointment> {
    return apiClient.put(`/appointments/${id}`, { status });
  },

  async delete(id: number): Promise<void> {
    return apiClient.delete(`/appointments/${id}`);
  },
};

// ==================== CONSULTATION API ====================
export const ConsultationAPI = {
  async list(): Promise<Consultation[]> {
    return (await apiClient.get<Consultation[]>("/consultations")) || [];
  },

  async getById(id: number): Promise<Consultation | null> {
    try {
      return await apiClient.get(`/consultations/${id}`);
    } catch (error) {
      return null;
    }
  },

  async create(
    consultation: Omit<Consultation, "id" | "createdAt" | "updatedAt">
  ): Promise<Consultation> {
    return apiClient.post("/consultations", consultation);
  },

  async update(
    id: number,
    updates: Partial<Consultation>
  ): Promise<Consultation> {
    return apiClient.put(`/consultations/${id}`, updates);
  },
};

// ==================== SEARCH API ====================
export const SearchAPI = {
  async searchDoctors(query: string): Promise<Doctor[]> {
    const allDoctors = await DoctorAPI.list();
    const q = query.toLowerCase();
    return allDoctors.filter(
      (d) =>
        d.firstName?.toLowerCase().includes(q) ||
        d.lastName?.toLowerCase().includes(q) ||
        d.speciality?.toLowerCase().includes(q)
    );
  },

  async searchPatients(query: string): Promise<Patient[]> {
    const allPatients = await PatientAPI.list();
    const q = query.toLowerCase();
    return allPatients.filter(
      (p) =>
        p.firstName?.toLowerCase().includes(q) ||
        p.lastName?.toLowerCase().includes(q) ||
        p.email?.toLowerCase().includes(q)
    );
  },

  async searchCabinets(query: string): Promise<Cabinet[]> {
    const allCabinets = await CabinetAPI.list();
    const q = query.toLowerCase();
    return allCabinets.filter(
      (c) =>
        c.name?.toLowerCase().includes(q) ||
        c.location?.address?.toLowerCase().includes(q)
    );
  },
};
