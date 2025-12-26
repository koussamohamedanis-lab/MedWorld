import type { Consultation } from './consultation';

export const findConsultationsByDoctorId = (consultations: Consultation[], doctorId: number): Consultation[] =>
    consultations.filter(cons => cons.doctorId === doctorId);

export const findConsultationsByPatientId = (consultations: Consultation[], patientId: number): Consultation[] =>
    consultations.filter(cons => cons.patientId === patientId);

export const findConsultationsByCabinetId = (consultations: Consultation[], cabinetId: number): Consultation[] =>
    consultations.filter(cons => cons.appointmentId === cabinetId);
