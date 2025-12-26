<script lang="ts">
  import { AllAPI, CabinetAPI } from "$lib/api";
  import Avatar from "$lib/components/ui/Avatar.svelte";
  import Block from "$lib/components/ui/Block.svelte";
  import Button from "$lib/components/ui/Button.svelte";
  import IconButton from "$lib/components/ui/IconButton.svelte";
  import UserSelectorModal from "$lib/components/dashboard/modals/UserSelectorModal.svelte";
  import type { Permission } from "$lib/types/permission";
  import type { IUser } from "$lib/types/users";
  import type { Admin } from "$lib/types/users/admin";
  import type { Doctor } from "$lib/types/users/doctor";
  import { Crown, Pen, Plus, Stethoscope, Trash2 } from "@lucide/svelte";
  import { onMount } from "svelte";

  let {
    user,
    permissions,
  }: {
    user: IUser;
    permissions: Permission[];
  } = $props();

  let doctors: Doctor[] = $state([]);
  let showAddModal = $state(false);

  async function loadDoctors() {
    if (
      user &&
      Array.isArray((user as any).doctors) &&
      (user as any).doctors.length > 0
    ) {
      doctors = (user as any).doctors;
    } else if (user.type === "admin" && (user as any).cabinetId) {
      doctors = await CabinetAPI.getDoctors((user as any).cabinetId);
    } else if (user.type === "superadmin") {
      doctors = await AllAPI.listAllDoctors();
    } else {
      console.log("ManageDoctors: User is not authorized to list all doctors.");
      doctors = [];
    }
  }

  onMount(async () => {
    await loadDoctors();
  });

  async function handleAddExisting(email: string) {
    try {
      // @ts-ignore
      await CabinetAPI.addDoctor((user as any).cabinetId, email);
      await loadDoctors();
      showAddModal = false;
    } catch (e) {
      console.error(e);
      alert("Failed to add doctor. Check email and try again.");
    }
  }

  async function handleRemove(doctorId: number) {
    if (
      !confirm("Are you sure you want to remove this doctor from the cabinet?")
    )
      return;
    try {
      // @ts-ignore
      await CabinetAPI.removeDoctor((user as any).cabinetId, doctorId);
      await loadDoctors();
    } catch (e) {
      console.error(e);
      alert("Failed to remove doctor.");
    }
  }

  async function handleSetAdmin(doctorId: number) {
    if (user.type !== "admin" || !(user as any).cabinetId) return;
    try {
      await CabinetAPI.setAdminDoctor((user as any).cabinetId, doctorId);
      await loadDoctors();
      alert("Admin doctor assigned successfully");
    } catch (e) {
      console.error(e);
      alert("Failed to set admin doctor");
    }
  }
</script>

<Block group="doctors" title="Manage Doctors" Icon={Stethoscope}>
  {#if doctors.length > 0}
    <table>
      <thead>
        <tr>
          <th>Avatar</th>
          <th>Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        {#each doctors as doctor}
          <tr>
            <td>
              <Avatar
                size="48px"
                avatarUrl={doctor.avatarUrl}
                alt={doctor.fullName}
              />
            </td>
            <td>{doctor.fullName}</td>
            <td>{doctor.email}</td>
            <td>{doctor.phoneNumber || "N/A"}</td>
            <td class="actions">
              {#if permissions.includes("edit_doctor")}
                <IconButton
                  href="/dashboard/users/{doctor.id}/modify"
                  label="Edit doctor account"
                  target="_blank"
                  Icon={Pen}
                ></IconButton>
              {/if}

              {#if permissions.includes("set_admin_doctor")}
                <IconButton
                  Icon={Crown}
                  label="Set as Admin"
                  color="var(--gold-dark)"
                  onClick={() => handleSetAdmin(doctor.doctorId || doctor.id)}
                ></IconButton>
              {/if}

              {#if user.type === "admin"}
                <IconButton
                  Icon={Trash2}
                  label="Remove from Cabinet"
                  type="error"
                  onClick={() => handleRemove(doctor.id)}
                />
              {/if}
            </td>
          </tr>
        {/each}
      </tbody>
    </table>
  {:else}
    <h3>No doctors found</h3>
  {/if}

  {#if permissions.includes("add_doctor")}
    <div class="add-actions">
      <Button
        label="Add a new Doctor"
        Icon={Plus}
        href={user.type === "admin" && (user as any).cabinetId
          ? `/dashboard/users/add?user_type=doctor&cabinet_id=${(user as any).cabinetId}`
          : "/dashboard/users/add?user_type=doctor"}
        category="primary"
        style="margin-top: 1.5rem; width: 100%"
      ></Button>

      <Button
        label="Add an existing Doctor"
        Icon={Plus}
        onClick={() => (showAddModal = true)}
        category="secondary"
        style="margin-top: 1.5rem; width: 100%"
      ></Button>
    </div>
  {/if}
</Block>

<UserSelectorModal
  isOpen={showAddModal}
  type="doctor"
  onClose={() => (showAddModal = false)}
  onSelect={handleAddExisting}
/>

<style>
  .add-actions {
    display: flex;
    gap: 1rem;
  }

  .actions {
    display: flex;
    gap: 0.5rem;
  }
</style>
