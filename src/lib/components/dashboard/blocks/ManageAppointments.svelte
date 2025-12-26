<script lang="ts">
  import { AllAPI, AppointmentAPI, ConsultationAPI, DoctorAPI } from "$lib/api";
  import Block from "$lib/components/ui/Block.svelte";
  import Button from "$lib/components/ui/Button.svelte";
  import IconButton from "$lib/components/ui/IconButton.svelte";
  import SearchInput from "$lib/components/ui/SearchInput.svelte";
  import { AppointmentStatus, type Appointment } from "$lib/types/appointment";
  import type { Cabinet } from "$lib/types/cabinet";
  import type { Permission } from "$lib/types/permission";
  import { Users, type IDoctor } from "$lib/types/users";
  import type { Consultation } from "$lib/types/consultation";
  import type { Assistant } from "$lib/types/users/assistant";
  import { Pen, PlusIcon, Timer, Trash } from "@lucide/svelte";
  import { onMount } from "svelte";
  import AddConsultation from "./AddConsultation.svelte";
  import AppointmentDetails from "./AppointmentDetails.svelte";

  let {
    user,
    permissions,
    cabinet = null,
  }: {
    user: Assistant | IDoctor;
    permissions: Permission[];
    cabinet: Cabinet | null;
  } = $props();

  async function cancelAppointment(id: number) {
    if (!confirm("Are you sure you want to cancel this appointment?")) return;

    try {
      await AppointmentAPI.updateStatus(id, AppointmentStatus.CANCELLED);
      appointment = appointment.map((apt) =>
        apt.id === id ? { ...apt, status: AppointmentStatus.CANCELLED } : apt
      );
    } catch (e) {
      console.error("Failed to cancel appointment:", e);
      alert("Failed to cancel appointment. Please try again.");
    }
  }

  let appointment: Appointment[] = $state([]);
  let realAppointments = $derived(
    appointment.filter((apt) => {
      if (
        apt.cabinet.name.toLowerCase().includes(query.toLowerCase()) ||
        apt.doctor.fullName.toLowerCase().includes(query.toLowerCase()) ||
        apt.date.toString().toLowerCase().includes(query.toLowerCase()) ||
        apt.patient.fullName.toLowerCase().includes(query.toLowerCase()) ||
        apt.status.toLowerCase().includes(query.toLowerCase())
      ) {
        return true;
      } else return false;
    })
  );

  let query: string = $state("");

  async function handleSaveConsultation(
    consultationData: Omit<Consultation, "id" | "createdAt" | "updatedAt">
  ) {
    try {
      const doctorId = (consultationData as any)?.doctor?.doctorId ?? (consultationData as any)?.doctorId;
      const patientId = (consultationData as any)?.patient?.patientId ?? (consultationData as any)?.patientId;
      const appointmentId = (consultationData as any)?.appointment?.id ?? (consultationData as any)?.appointmentId;

      await ConsultationAPI.create({
        doctorId,
        patientId,
        appointmentId,
        notes: consultationData.notes,
        prescriptions: consultationData.prescriptions,
        attachments: consultationData.attachments,
      } as any);

      alert("created.");
      currentAppointment = null;
    } catch (e) {
      console.error("Failed to create consultation:", e);
      alert("Failed to save consultation. Please try again.");
    }
  }

  onMount(async () => {
    try {
      if (user.type === Users.Assistant) {
        appointment = []; // Assistants logic seems unimplemented in detail or relies on defaults
      } else if (user.type === Users.Admin || user.type === Users.Doctor) {
        // @ts-ignore
        const doctorId = user.doctorId;
        if (doctorId) {
          const docAppts = await DoctorAPI.getAppointments(doctorId);
          if (!cabinet) {
            appointment = docAppts;
          } else {
            appointment = docAppts.filter(
              (appt) => appt.cabinet.id === cabinet.id
            );
          }
        }
      } else {
        appointment = await AllAPI.listAllAppointments();
      }
    } catch (e) {
      console.error("Failed to load appointments:", e);
      appointment = [];
    }
  });

  let currentAppointment: Appointment | null = $state(null);
  let viewingMode = $state(false);
</script>

<Block group="appointments" title="Appointments" Icon={Timer}>
  {#if currentAppointment}
    {#if viewingMode}
      <AppointmentDetails
        appointment={currentAppointment}
        onClose={() => {
          currentAppointment = null;
          viewingMode = false;
        }}
      />
    {:else if permissions.includes("add_consultation")}
      <AddConsultation
        onSave={handleSaveConsultation}
        onBack={() => {
          currentAppointment = null;
          viewingMode = false;
        }}
        {currentAppointment}
        patient={currentAppointment?.patient}
        currentDoctor={currentAppointment?.doctor}
      />
    {/if}
  {:else}
    <SearchInput
      bind:value={query}
      placeholder="Search an Appointment/Patient..."
    />

    <br /><br />

    {#if realAppointments.length > 0}
      <table>
        <thead>
          <tr>
            <th>Date</th>
            <th>Patient</th>
            <th>Status</th>
            <th>Doctor</th>
            <th>Cabinet</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          {#each realAppointments as appointment}
            <tr>
              <td>{new Date(appointment.date).toDateString()}</td>
              <td>{appointment.patient.fullName}</td>
              <td
                ><p class="status {appointment.status.toLowerCase()}">
                  {appointment.status}
                </p></td
              >
              <td>{appointment.doctor.fullName}</td>
              <td>{appointment.cabinet.name}</td>
              <td class="actions">
                {#if appointment.status === AppointmentStatus.CONFIRMED && permissions.find( (e) => e.endsWith("_prescription") )}
                  <IconButton Icon={PlusIcon} label="Add Prescription" />
                {/if}
                {#if permissions.includes("edit_appointment")}
                  <IconButton
                    Icon={Pen}
                    onclick={() => {
                      currentAppointment = appointment;
                      viewingMode = false;
                    }}
                    label="Edit Appointment"
                  />
                {/if}
                {#if [AppointmentStatus.IN_PROGRESS, AppointmentStatus.SCHEDULED, AppointmentStatus.CONFIRMED].includes(appointment.status) && permissions.includes("cancel_appointment")}
                  <IconButton
                    type="error"
                    Icon={Trash}
                    onClick={() => cancelAppointment(appointment.id)}
                    label="Cancel Appointment"
                  />
                {/if}
                {#if permissions.includes("view_appointment")}
                  <IconButton
                    Icon={Timer}
                    color="orange"
                    onclick={() => {
                      currentAppointment = appointment;
                      viewingMode = true;
                    }}
                    label="View Appointment Details"
                  />
                {/if}
              </td>
            </tr>
          {/each}
        </tbody>
      </table>
    {:else}
      <p>No Appointments.</p>
    {/if}
  {/if}
</Block>

<style>
  .actions {
    display: flex;
    gap: 0.5rem;
  }

  .status {
    border-radius: 9999px;
    border: 1px solid;
    width: fit-content;
    padding: 0.25rem 1rem;
    text-transform: capitalize;
  }

  /* SCHEDULED = 'SCHEDULED',
    CONFIRMED = 'CONFIRMED',
    IN_PROGRESS = 'IN_PROGRESS',
    COMPLETED = 'COMPLETED',
    CANCELLED = 'CANCELLED',
    NO_SHOW = 'NO_SHOW' */

  .status.completed {
    background: rgba(172, 255, 47, 0.128);
    border-color: rgba(172, 255, 47, 0.409);
    color: #4b8300;
  }

  .status.confirmed {
    background: rgba(47, 255, 158, 0.128);
    border-color: rgba(4, 135, 102, 0.409);
    color: #036b4d;
  }

  .status.in_progress {
    background: rgba(255, 245, 47, 0.128);
    border-color: rgba(214, 230, 0, 0.409);
    color: #858e00;
  }

  .status.cancelled {
    background: rgba(255, 47, 47, 0.128);
    border-color: rgba(218, 40, 40, 0.409);
    color: #c53030;
  }

  .status.scheduled {
    background: rgba(44, 139, 234, 0.128);
    border-color: rgba(44, 139, 234, 0.409);
    color: #2c8bea;
  }
</style>
