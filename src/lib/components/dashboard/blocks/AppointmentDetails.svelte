<script lang="ts">
  import type { Appointment } from "$lib/types/appointment";
  import Modal from "$lib/components/ui/Modal.svelte";
  import { AppointmentAPI } from "$lib/api";
  import { onMount } from "svelte";

  let {
    appointment,
    onClose,
  }: { appointment: Appointment; onClose: () => void } = $props();

  let loadedAppointment: Appointment | null = $state(null);
  let isLoading: boolean = $state(false);

  onMount(async () => {
    if (!appointment?.id) {
      loadedAppointment = appointment;
      return;
    }

    isLoading = true;
    try {
      loadedAppointment = (await AppointmentAPI.getById(appointment.id)) || appointment;
    } finally {
      isLoading = false;
    }
  });
</script>

<Modal isOpen={true} {onClose} title="Appointment Details">
  {#if isLoading || !loadedAppointment}
    <div class="detail-group">
      <p>Loading...</p>
    </div>
  {:else}
  <div class="detail-group">
    <label>Patient</label>
    <p>{loadedAppointment.patient.fullName}</p>
  </div>

  <div class="detail-group">
    <label>Doctor</label>
    <p>{loadedAppointment.doctor.fullName}</p>
  </div>

  <div class="detail-group">
    <label>Cabinet</label>
    <p>{loadedAppointment.cabinet.name}</p>
  </div>

  <div class="detail-group">
    <label>Date</label>
    <p>{new Date(loadedAppointment.date).toLocaleString()}</p>
  </div>

  <div class="detail-group">
    <label>Status</label>
    <p class="status {loadedAppointment.status.toLowerCase()}">
      {loadedAppointment.status}
    </p>
  </div>

  {#if loadedAppointment.consultation}
    <div class="detail-group">
      <label>Consultation Notes</label>
      <p>{loadedAppointment.consultation.notes || "-"}</p>
    </div>

    <div class="detail-group">
      <label>Prescriptions</label>
      {#if loadedAppointment.consultation.prescriptions?.length}
        <div class="list">
          {#each loadedAppointment.consultation.prescriptions as p}
            <p class="list-item">{p}</p>
          {/each}
        </div>
      {:else}
        <p>-</p>
      {/if}
    </div>

    <div class="detail-group">
      <label>Attachments</label>
      {#if loadedAppointment.consultation.attachments?.length}
        <div class="list">
          {#each loadedAppointment.consultation.attachments as a}
            {#if a.startsWith("blob:")}
              <p class="list-item">(local preview, not persisted)</p>
            {:else if a.startsWith("data:image/")}
              <a class="attachment-link" href={a} target="_blank" rel="noreferrer">
                <img class="attachment-image" src={a} alt="Attachment" />
              </a>
            {:else}
              <a class="attachment-link" href={a} target="_blank" rel="noreferrer">{a}</a>
            {/if}
          {/each}
        </div>
      {:else}
        <p>-</p>
      {/if}
    </div>
  {/if}
  {/if}
</Modal>

<style>
  .detail-group {
    margin-bottom: 1.5rem;
  }

  .detail-group label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  .detail-group p {
    margin: 0;
    color: #1f2937;
    font-size: 1rem;
  }

  .status {
    border-radius: 9999px;
    border: 1px solid;
    width: fit-content;
    padding: 0.25rem 1rem;
    text-transform: capitalize;
    display: inline-block;
  }

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

  .status.no_show {
    background: rgba(107, 114, 128, 0.128);
    border-color: rgba(107, 114, 128, 0.409);
    color: #374151;
  }

  .list {
    display: grid;
    gap: 0.5rem;
  }

  .list-item {
    margin: 0;
  }

  .attachment-link {
    color: #2563eb;
    text-decoration: underline;
    word-break: break-word;
  }

  .attachment-image {
    max-width: 100%;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
  }
</style>
