<script lang="ts">
  import { Stethoscope, Plus, X, Paperclip, ArrowLeft } from "@lucide/svelte";
  import Modal from "$lib/components/ui/Modal.svelte";
  import Button from "$lib/components/ui/Button.svelte";
  import Input from "$lib/components/ui/Input.svelte";
  import IconButton from "$lib/components/ui/IconButton.svelte";
  import type { Patient } from "$lib/types/users/patient";
  import type { Consultation } from "$lib/types/consultation";
  import type { Doctor } from "$lib/types/users/doctor";
  import type { Appointment } from "$lib/types/appointment";
  import type { User, Users } from "$lib/types/users";
  import { validation, type Fillable } from "$lib/validation";
  import { gotoBlock } from "$lib/stores";

  // Ctrl D
  // or whatever on mac + D

  let {
    patient,
    currentAppointment,
    currentDoctor,
    onSave = () => {},
    onBack = () => {},
  }: {
    patient: Patient;
    currentDoctor: Doctor;
    currentAppointment: Appointment;
    onSave: (
      consultation: Omit<Consultation, "id" | "createdAt" | "updatedAt">
    ) => void;
    onBack: () => void;
  } = $props();

  let isOpen: boolean = $state(false);

  let notes: string = $state("");
  let prescriptions: string[] = $state([""]);
  let attachments: string[] = $state([]);

  function openModal() {
    isOpen = true;
  }

  function closeModal() {
    isOpen = false;
    resetForm();
  }

  function handleBack() {
    if (isOpen) {
      closeModal();
    }
    onBack();
  }

  function resetForm() {
    notes = "";
    prescriptions = [""];
    attachments = [];
  }

  function addPrescription() {
    prescriptions = [...prescriptions, ""];
  }

  function removePrescription(index: number) {
    if (prescriptions.length > 1) {
      prescriptions = prescriptions.filter((_, i) => i !== index);
    } else {
      // If it's the last one, just clear it
      prescriptions = [""];
    }
  }

  function updatePrescription(index: number, value: string) {
    prescriptions = prescriptions.map((prescription, i) =>
      i === index ? value : prescription
    );
  }

  function handleFileUpload(event: Event) {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
      const files = Array.from(input.files);
      files.forEach((file) => {
        const reader = new FileReader();
        reader.onload = () => {
          const result = reader.result as string;
          if (result) {
            attachments = [...attachments, result];
          }
        };
        reader.readAsDataURL(file);
      });
      input.value = ""; // Reset input
    }
  }

  function removeAttachment(index: number) {
    attachments = attachments.filter((_, i) => i !== index);
  }

  function handleSave() {
    // filter out empty prescriptions
    const nonEmptyPrescriptions = prescriptions.filter(
      (prescription) => prescription.trim() !== ""
    );

    const consultationData: Omit<
      Consultation,
      "id" | "createdAt" | "updatedAt"
    > = {
      doctor: currentDoctor,
      patient: patient as Patient,
      appointment: currentAppointment || createDefaultAppointment(),
      notes: notes.trim(),
      prescriptions: nonEmptyPrescriptions,
      attachments: attachments.length > 0 ? attachments : undefined,
    };

    onSave(consultationData);
    closeModal();
  }

  function createDefaultAppointment(): Appointment {
    return {
      id: 0,
      date: new Date(),
    } as Appointment;
  }

  const isFormValid = $derived(!!notes.trim());

  let data: Fillable = $state({
    prescriptions: {
      value: "",
      error: "",
      validator: validation.isValidPrescription,
    },
  });
</script>

<div>
  <h3>
    {patient.fullName}'s Appointment ({new Date().toDateString()})
  </h3>
  <br />
  <Button Icon={ArrowLeft} category="secondary" color="blue" label="Back" onClick={handleBack} />

  <Button
    Icon={Stethoscope}
    type="primary"
    color="blue"
    label="Add Consultation"
    onclick={openModal}
  />

  <Modal
    {isOpen}
    onClose={closeModal}
    title={`Add Consultation - ${patient.fullName}`}
  >
    <div class="consultation-form">
      <!-- Notes Section -->
      <Input
        category="textarea"
        label="Clinical Notes *"
        bind:value={notes}
        placeholder="Enter detailed clinical notes, observations, diagnosis, and treatment plan..."
        rows={6}
        required
      />

      <!-- Prescriptions Section -->
      <div class="prescriptions-section">
        <div class="section-header">
          <label class="section-label">Prescriptions</label>
          <Button
            type="secondary"
            size="small"
            onclick={addPrescription}
            Icon={Plus}
          >
            Add Prescription
          </Button>
        </div>

        <div class="prescriptions-list">
          {#each prescriptions as prescription, index}
            <div class="prescription-item">
              <Input
                bind:value={prescriptions[index]}
                placeholder="Enter medication, dosage, and instructions..."
              />
              {#if prescriptions.length > 1}
                <button
                  class="remove-button"
                  onclick={() => removePrescription(index)}
                  type="button"
                >
                  <X size={16} />
                </button>
              {/if}
            </div>
          {/each}
        </div>
      </div>

      <!-- Attachments Section -->
      <div class="attachments-section">
        <div class="section-header">
          <label class="section-label">Attachments</label>
          <div class="file-upload-wrapper">
            <input
              type="file"
              id="file-upload"
              multiple
              accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
              onchange={handleFileUpload}
              style="display: none;"
            />
            <Button
              type="secondary"
              size="small"
              onclick={() => document.getElementById("file-upload")?.click()}
              Icon={Paperclip}
            >
              Add Files
            </Button>
          </div>
        </div>

        {#if attachments.length > 0}
          <div class="attachments-list">
            {#each attachments as attachment, index}
              <div class="attachment-item">
                <Paperclip size={16} />
                <span class="attachment-name">
                  {attachment.split("/").pop() || `Attachment ${index + 1}`}
                </span>
                <button
                  class="remove-button"
                  onclick={() => removeAttachment(index)}
                  type="button"
                >
                  <X size={14} />
                </button>
              </div>
            {/each}
          </div>
        {:else}
          <p class="no-attachments">No attachments added</p>
        {/if}
      </div>

      <div class="form-actions">
        <Button type="secondary" onclick={closeModal}>Cancel</Button>
        <Button onclick={handleSave} disabled={!isFormValid}>
          Save Consultation
        </Button>
      </div>
    </div>
  </Modal>
</div>

<style>
  .consultation-form {
    display: flex;
    flex-direction: column;
    gap: 2rem;
  }

  .section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
  }

  .section-label {
    font-weight: 600;
    color: #374151;
    font-size: 1rem;
  }

  .prescriptions-list,
  .attachments-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  .prescription-item {
    display: flex;
    gap: 0.5rem;
    align-items: center;
  }

  .prescription-item :global(.input-container) {
    flex: 1;
  }

  .attachment-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
  }

  .attachment-name {
    flex: 1;
    font-size: 0.875rem;
    color: #475569;
  }

  .remove-button {
    background: none;
    border: none;
    color: #6b7280;
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .remove-button:hover {
    background: #f3f4f6;
    color: #374151;
  }

  .no-attachments {
    color: #6b7280;
    font-style: italic;
    text-align: center;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 6px;
    margin: 0;
  }

  .file-upload-wrapper {
    display: flex;
    align-items: center;
  }

  .form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 1rem;
    padding-top: 2rem;
    border-top: 1px solid #e5e7eb;
  }

  @media (max-width: 640px) {
    .section-header {
      flex-direction: column;
      align-items: flex-start;
      gap: 0.75rem;
    }

    .prescription-item {
      flex-direction: column;
      align-items: stretch;
    }

    .prescription-item .remove-button {
      align-self: flex-end;
    }
  }
</style>
