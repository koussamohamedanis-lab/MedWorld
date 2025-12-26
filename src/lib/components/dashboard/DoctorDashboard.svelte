<script lang="ts">
    import { currentCabinet } from "$lib/stores";

    import {
        getPermissionsFromUserType,
        groupPermissions,
    } from "$lib/types/permission";

    import Messages from "./blocks/Messages.svelte";
    import Dashboard from "./Dashboard.svelte";
    import ManageCalendar from "./blocks/calendar/ManageCalendar.svelte";
    import ManagePatients from "./blocks/ManagePatients.svelte";
    import ManageAssistants from "./blocks/ManageAssistants.svelte";
    import ManageAppointments from "./blocks/ManageAppointments.svelte";

    import type { Doctor } from "$lib/types/users/doctor";
    import type { Cabinet } from "$lib/types/cabinet";

    interface IProps {
        doctor: Doctor;
    }

    let { doctor }: IProps = $props();
    let permissions = getPermissionsFromUserType(doctor.type);
    let permissionGroups = groupPermissions(permissions);
</script>

<Dashboard>
    {#if permissionGroups.includes("appointments")}
        <ManageAppointments
            user={doctor}
            cabinet={$currentCabinet as Cabinet}
            {permissions}
        />
    {/if}

    {#if permissionGroups.includes("patients")}
        <ManagePatients user={doctor} cabinet={doctor.cabinet} />
    {/if}

    {#if permissionGroups.includes("assistants")}
        <ManageAssistants user={doctor} {permissions} />
    {/if}

    {#if permissionGroups.includes("messages")}
        <Messages />
    {/if}

    {#if permissionGroups.includes("calendar")}
        <ManageCalendar
            user={doctor}
            {permissions}
            cabinet={$currentCabinet as Cabinet}
        />
    {/if}
</Dashboard>
