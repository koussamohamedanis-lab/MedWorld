import type { Admin } from "./admin";
import type { Assistant } from "./assistant";
import type { Doctor } from "./doctor";
import type { Patient } from "./patient";
import type { SuperAdmin } from "./superadmin";

export type UserType = 'superadmin' | 'admin' | 'doctor' | 'assistant' | 'patient';

export const enum Users {
  SuperAdmin = 'superadmin',
  Admin = 'admin',
  Doctor = 'doctor',
  Assistant = 'assistant',
  Patient = 'patient'
}

export const UserTypeNames: Record<Users, string> = {
  [Users.SuperAdmin]: 'Super Admin',
  [Users.Admin]: 'Admin Doctor',
  [Users.Doctor]: 'Doctor',
  [Users.Assistant]: 'Assistant',
  [Users.Patient]: 'Patient',
};

export interface User<T extends UserType> {
  address: string;
  gender: 'male' | 'female';
  dateOfBirth: Date;
  id: number;
  firstName: string;
  lastName: string;
  createdAt?: Date;

  type: T;

  email: string;
  password?: string;
  phoneNumber: string;
  avatarUrl?: string;

  fullName: string;
}

export type AnyUser = User<any>;

export type IUser = AnyUser & (SuperAdmin | Admin | Doctor | Assistant | Patient);
export type IDoctor = AnyUser & (Admin | Doctor);

export type IMixedUser = AnyUser & SuperAdmin & Admin & Doctor & Assistant & Patient;

export function getTypeFromString(type: UserType): Users {
  switch (type) {
    case 'admin':
      return Users.Admin;
    case "superadmin": return Users.SuperAdmin;
    case "doctor": return Users.Doctor;
    case "assistant": return Users.Assistant;
    case "patient": return Users.Patient;
  }
}
