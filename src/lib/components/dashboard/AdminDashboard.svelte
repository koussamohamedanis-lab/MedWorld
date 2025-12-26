<script lang="ts">
    import {
        getPermissionsFromUserType,
        groupPermissions,
    } from "$lib/types/permission";
    import type { Admin } from "$lib/types/users/admin";
    import ManageAssistants from "./blocks/ManageAssistants.svelte";
    import ManagePatients from "./blocks/ManagePatients.svelte";
    import ManageDoctors from "./blocks/ManageDoctors.svelte";
    import Plan from "./blocks/Plan.svelte";

    import type { AnyUser } from "$lib/types/users";
    import Dashboard from "./Dashboard.svelte";
    import ManageCalendar from "./blocks/calendar/ManageCalendar.svelte";
    import ManageAppointments from "./blocks/ManageAppointments.svelte";
    import Messages from "./blocks/Messages.svelte";
    import { currentCabinet } from "$lib/stores";
    import type { Cabinet } from "$lib/types/cabinet";

    interface IProps {
        admin: Admin;
    }

    let { admin }: IProps = $props();
    let permissions = getPermissionsFromUserType(admin.type);
    let permissionGroups = $derived(groupPermissions(permissions));
</script>

<Dashboard>
    {#if permissions.find((permission) => permission.endsWith("_doctor"))}
        <ManageDoctors user={admin} {permissions} />
    {/if}

    {#if permissions.find((permission) => permission.endsWith("_assistant"))}
        <ManageAssistants {admin} {permissions} cabinet={admin.cabinet} />
    {/if}

    {#if permissions.find((e) => e.endsWith("_patient"))}
        <ManagePatients user={admin as AnyUser} cabinet={admin.cabinet} />
    {/if}

    {#if permissions.find((p) => p.endsWith("_calendar"))}
        <ManageCalendar cabinet={admin.cabinet} {permissions} user={admin} />
    {/if}

    {#if permissionGroups.includes("appointments")}
        <ManageAppointments
            user={admin}
            cabinet={$currentCabinet as Cabinet}
            {permissions}
        />
    {/if}

    {#if permissionGroups.includes("messages")}
        <Messages />
    {/if}

    {#if permissionGroups.includes("calendar")}
        <ManageCalendar
            user={admin}
            {permissions}
            cabinet={$currentCabinet as Cabinet}
        />
    {/if}

    <Plan />
</Dashboard>
