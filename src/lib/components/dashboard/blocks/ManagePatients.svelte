<script lang="ts">
    import { onMount } from "svelte";
    import { DoctorAPI } from "$lib/api";
    import { Eye, Pen, UserSquare } from "@lucide/svelte";

    import Block from "$lib/components/ui/Block.svelte";
    import Avatar from "$lib/components/ui/Avatar.svelte";
    import IconButton from "$lib/components/ui/IconButton.svelte";
    import SearchInput from "$lib/components/ui/SearchInput.svelte";

    import { type IUser, type User, Users } from "$lib/types/users";
    import type { Patient } from "$lib/types/users/patient";
    import type { Appointment } from "$lib/types/appointment";
    import type { Cabinet } from "$lib/types/cabinet";
    import ManageAssistants from "./ManageAssistants.svelte";
    import { getPermissionsFromUserType } from "$lib/types/permission";

    interface IProps {
        user: User<any>;
        cabinet?: Cabinet;
    }

    let patients: Patient[] = $state([]);
    let searchQuery: string = $state("");
    let { user, cabinet }: IProps = $props();

    onMount(async () => {
        console.log("ManagePatients: Mounting for user:", user);
        if ((user as any).doctorId) {
            console.log(
                "ManagePatients: Fetching patients for doctorId:",
                (user as any).doctorId,
            );
            // Fetch only patients for this doctor and cabinet (if specified)
            patients = await DoctorAPI.getPatients(
                (user as any).doctorId,
                cabinet?.id,
            );
            console.log("ManagePatients: Received patients:", patients);
        } else {
            console.error("ManagePatients: No doctorId found in user object.");
        }
    });

    let filteredPatients = $derived(
        patients.filter((patient) => {
            // Remove the local cabinet filter because the API already handles it
            // and we don't have the appointment data here to check locally.

            if (!searchQuery) return true;
            const searchTerm = searchQuery.toLowerCase();
            return (
                patient.fullName.toLowerCase().includes(searchTerm) ||
                patient.email.toLowerCase().includes(searchTerm) ||
                (patient.phoneNumber &&
                    patient.phoneNumber.includes(searchTerm))
            );
        }),
    );
</script>

<Block
    group="patients"
    title={cabinet !== undefined ? "Patients" : "Manage Patients"}
    Icon={UserSquare}
>
    <div class="search-container">
        <SearchInput
            bind:value={searchQuery}
            placeholder="Search patients..."
        />
    </div>

    {#if filteredPatients.length === 0}
        <h3>No patients found</h3>
    {:else}
        <table>
            <thead>
                <tr>
                    <th class="desktop">Avatar</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                {#each filteredPatients as patient}
                    <tr>
                        <td class="desktop">
                            <Avatar
                                size="48px"
                                avatarUrl={patient.avatarUrl}
                                alt={patient.fullName}
                            />
                        </td>
                        <td>{patient.fullName}</td>
                        <td>{patient.email}</td>
                        <td>{patient.phoneNumber || "N/A"}</td>
                        <td class="actions">
                            <IconButton
                                Icon={Eye}
                                label="View Patient"
                                target="_blank"
                                href="/dashboard/users/{patient.id}"
                            />

                            <IconButton
                                Icon={Pen}
                                type="secondary"
                                color="gray"
                                label="Edit Patient"
                                target="_blank"
                                href="/dashboard/users/{patient.id}/modify"
                            />
                        </td>
                    </tr>
                {/each}
            </tbody>
        </table>
    {/if}
</Block>

<style>
    h3 {
        font-size: 2rem;
        font-weight: 300;
        text-align: center;
    }

    .actions {
        display: flex;
        gap: 0.5rem;
    }
</style>
