<script lang="ts">
    import { currentCabinet } from "$lib/stores";

    import {
        getPermissionsFromUserType,
        type Permission,
    } from "$lib/types/permission";

    import Dashboard from "./Dashboard.svelte";
    import ManagePatients from "./blocks/ManagePatients.svelte";
    import PatientBookAppointment from "./blocks/PatientBookAppointment.svelte";
    import ManageAppointments from "./blocks/ManageAppointments.svelte";

    import type { Patient } from "$lib/types/users/patient";
    import type { Cabinet } from "$lib/types/cabinet";

    interface IProps {
        patient: Patient;
    }

    let { patient }: IProps = $props();
    let permissions = getPermissionsFromUserType(patient.type);
</script>

<Dashboard>
    {#if permissions.find((e) => e === "book_appointment")}
        <PatientBookAppointment patient={patient} />
    {/if}

    {#if permissions.find( (e) => (["view_appointment", "edit_appointment", "cancel_appointment"] as Permission[]).includes(e), )}
        <ManageAppointments
            cabinet={$currentCabinet as Cabinet}
            user={patient as any}
            {permissions}
        />
    {/if}

    {#if permissions.find((e) => e.endsWith("_patient"))}
        <ManagePatients user={patient} />
    {/if}
</Dashboard>

<style>
</style>
