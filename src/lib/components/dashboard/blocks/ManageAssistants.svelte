<script lang="ts">
  import { CabinetAPI } from "$lib/api";
  import Avatar from "$lib/components/ui/Avatar.svelte";
  import Block from "$lib/components/ui/Block.svelte";
  import Button from "$lib/components/ui/Button.svelte";
  import IconButton from "$lib/components/ui/IconButton.svelte";
  import Modal from "$lib/components/ui/Modal.svelte";
  import type { Cabinet } from "$lib/types/cabinet";
  import type { Permission } from "$lib/types/permission";
  import type { Admin } from "$lib/types/users/admin";
  import type { Assistant } from "$lib/types/users/assistant";
  import type { Doctor } from "$lib/types/users/doctor";
  import { Link, Unlink, Users2 } from "@lucide/svelte";
  import { onMount } from "svelte";

  interface IProps {
    admin: Admin;
    permissions: Permission[];
    cabinet: Cabinet;
  }

  let { admin, permissions, cabinet }: IProps = $props();

  let assistants: Assistant[] = $state([]);
  let doctors: (Doctor | Admin)[] = $state([]);
  let isLinkModalOpen: boolean = $state(false);
  let selectedAssistant: Assistant | null = $state(null);
  let isLoading: boolean = $state(false);
  let isLoadingAssistants: boolean = $state(false);

  onMount(async () => {
    if (cabinet?.id) {
      await loadAssistantsAndDoctors();
    }
  });

  async function loadAssistantsAndDoctors() {
    isLoadingAssistants = true;
    try {
      const [loadedAssistants, loadedDoctors] = await Promise.all([
        CabinetAPI.getAssistants(cabinet.id),
        CabinetAPI.getDoctors(cabinet.id),
      ]);

      // FIX: Backend doesn't return cabinetId in assistant response, so set it manually
      assistants = loadedAssistants.map((asst: any) => ({
        ...asst,
        cabinetId: cabinet.id,
      }));

      doctors = loadedDoctors;
    } catch (error) {
      console.error("Error loading assistants or doctors:", error);
      alert("Error loading cabinet data");
    } finally {
      isLoadingAssistants = false;
    }
  }

  function openLinkModal(assistant: Assistant) {
    selectedAssistant = assistant;
    isLinkModalOpen = true;
  }

  function closeLinkModal() {
    isLinkModalOpen = false;
    selectedAssistant = null;
  }

  async function handleLinkAssistant(doctor: Doctor | Admin) {
    if (!selectedAssistant || !cabinet) return;

    try {
      isLoading = true;
      const targetDoctorId = (doctor as any).doctorId ?? doctor.id;
      // Call API to link assistant to doctor
      // Use assistantId (from the Assistant model), not id (from User model)
      await CabinetAPI.assignAssistant(
        cabinet.id,
        (selectedAssistant as any).assistantId,
        targetDoctorId,
      );
      selectedAssistant.doctor = doctor;
      selectedAssistant.doctorId = targetDoctorId;
      // Force reactivity by reassigning the array
      assistants = [...assistants];
      closeLinkModal();
    } catch (error) {
      alert("Error linking assistant to doctor");
      console.error(error);
    } finally {
      isLoading = false;
    }
  }

  async function handleUnlinkAssistant(assistant: Assistant) {
    if (
      !confirm(
        `Unlink ${assistant.fullName} from ${assistant.doctor?.fullName}?`,
      )
    ) {
      return;
    }

    try {
      isLoading = true;
      const cabinetIdFromAssistant = (assistant as any).cabinetId;
      console.log(
        "DEBUG: Full assistant object:",
        JSON.stringify(assistant, null, 2),
      );
      console.log("DEBUG: Cabinet object:", JSON.stringify(cabinet, null, 2));
      console.log("DEBUG: Sending unlink request with:", {
        cabinetId: cabinet.id,
        assistantId: assistant.id,
        doctorId: 0,
        assistantCabinetIdFromObject: cabinetIdFromAssistant,
      });

      if (cabinet) {
        // Backend expects: { "doctorId": 0 } to unlink
        // Use assistantId (from the Assistant model), not id (from User model)
        const response = await CabinetAPI.assignAssistant(
          cabinet.id,
          (assistant as any).assistantId,
          0,
        );
        console.log("DEBUG: Unlink API response:", response);
      }

      assistant.doctorId = 0;
      assistant.doctor = null as any;
      assistants = [...assistants];
      console.log("DEBUG: UI updated successfully");
    } catch (error: any) {
      console.error("DEBUG: Unlink error details:", {
        message: error?.message,
        status: error?.status,
        data: error?.data,
        fullError: error,
      });
      alert(
        `Error unlinking: ${error?.message || "Unknown error"}. Check console for details.`,
      );
    } finally {
      isLoading = false;
    }
  }
</script>

<Block group="assistants" title="Manage Assistants" Icon={Users2}>
  {#if !permissions.includes("assign_assistant")}
    <div class="empty-state">
      <Users2 size={48} />
      <h3>Permission Denied</h3>
      <p>You don't have permission to manage assistants.</p>
    </div>
  {:else if isLoadingAssistants}
    <div class="empty-state">
      <Users2 size={48} />
      <h3>Loading...</h3>
      <p>Fetching assistants...</p>
    </div>
  {:else if assistants.length === 0}
    <div class="empty-state">
      <Users2 size={48} />
      <h3>No Assistants</h3>
      <p>There are no assistants in this cabinet yet.</p>
    </div>
  {:else}
    <div class="assistants-list">
      {#each assistants as assistant (assistant.id)}
        <div class="assistant-card">
          <div class="assistant-info">
            <Avatar
              size="48px"
              avatarUrl={assistant.avatarUrl}
              alt={assistant.fullName}
            />
            <div class="details">
              <h4>{assistant.fullName}</h4>
              <p class="email">{assistant.email}</p>
              {#if assistant.doctor}
                <p class="doctor-link">
                  Linked to: <strong>{assistant.doctor.fullName} ({assistant.doctor.email})</strong>
                </p>
              {:else}
                <p class="no-link">Not linked to any doctor</p>
              {/if}
            </div>
          </div>

          <div class="actions">
            {#if assistant.doctor && assistant.doctorId > 0}
              {#if permissions.includes("assign_assistant")}
                <IconButton
                  Icon={Unlink}
                  label={isLoading ? "Unlinking..." : "Unlink"}
                  type="error"
                  disabled={isLoading}
                  onclick={() => handleUnlinkAssistant(assistant)}
                />
              {/if}
            {/if}

            {#if permissions.includes("assign_assistant")}
              <Button
                Icon={Link}
                label={isLoading ? "Linking..." : "Link"}
                category="primary"
                disabled={isLoading}
                onClick={() => openLinkModal(assistant)}
              />
            {/if}
          </div>
        </div>
      {/each}
    </div>
  {/if}
</Block>

<Modal
  isOpen={isLinkModalOpen}
  title={selectedAssistant
    ? `Link ${selectedAssistant.fullName}`
    : "Link Assistant"}
  onClose={closeLinkModal}
>
  <div class="link-modal-content">
    {#if doctors.length === 0}
      <p class="no-doctors">No doctors available in this cabinet.</p>
    {:else}
      <p class="modal-description">
        Select a doctor to link this assistant to:
      </p>
      <div class="doctors-list">
        {#each doctors as doctor (doctor.id)}
          <button
            class="doctor-option"
            onclick={() => handleLinkAssistant(doctor)}
            disabled={isLoading}
          >
            <Avatar
              size="40px"
              avatarUrl={doctor.avatarUrl}
              alt={doctor.fullName}
            />
            <div class="doctor-details">
              <p class="doctor-name">{doctor.fullName}</p>
              {#if "speciality" in doctor}
                <p class="doctor-speciality">{doctor.speciality}</p>
              {/if}
            </div>
          </button>
        {/each}
      </div>
    {/if}
  </div>
</Modal>

<style>
  .empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem;
    color: #6b7280;
    text-align: center;
  }

  .empty-state h3 {
    margin: 1rem 0 0.5rem;
    color: #111827;
    font-size: 1.125rem;
  }

  .empty-state p {
    margin: 0;
    color: #9ca3af;
  }

  .assistants-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  .assistant-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem;
    background: #f9fafb;
    border-radius: 0.75rem;
    border: 1px solid #e5e7eb;
    transition: all 0.2s ease;
  }

  .assistant-card:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
  }

  .assistant-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex: 1;
  }

  .details {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
  }

  .details h4 {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: #111827;
  }

  .email {
    margin: 0;
    font-size: 0.875rem;
    color: #6b7280;
  }

  .doctor-link {
    margin: 0;
    font-size: 0.875rem;
    color: #059669;
    font-weight: 500;
  }

  .no-link {
    margin: 0;
    font-size: 0.875rem;
    color: #d97706;
  }

  .actions {
    display: flex;
    gap: 0.75rem;
    align-items: center;
  }

  .link-modal-content {
    padding: 1.5rem;
  }

  .modal-description {
    margin: 0 0 1.5rem;
    color: #374151;
    font-size: 0.95rem;
  }

  .no-doctors {
    text-align: center;
    color: #6b7280;
    padding: 2rem;
  }

  .doctors-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }

  .doctor-option {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: none;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: left;
  }

  .doctor-option:hover:not(:disabled) {
    background: #f3f4f6;
    border-color: #3b82f6;
  }

  .doctor-option:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  .doctor-details {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
  }

  .doctor-name {
    margin: 0;
    font-weight: 600;
    color: #111827;
    font-size: 0.95rem;
  }

  .doctor-speciality {
    margin: 0;
    font-size: 0.85rem;
    color: #6b7280;
  }
</style>
