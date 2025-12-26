<script lang="ts">
    import { getPermissionsFromUserType } from "$lib/types/permission";

    import type { SuperAdmin } from "$lib/types/users/superadmin";
    import AddCabinet from "./blocks/AddCabinet.svelte";

    import ManageCabinet from "./blocks/ManageCabinet.svelte";
    import ManageCabinets from "./blocks/ManageCabinets.svelte";
    import Dashboard from "./Dashboard.svelte";

    interface IProps {
        superadmin: SuperAdmin;
        showAddCabinet?: boolean;
        onAddCabinetClose?: () => void;
    }

    let {
        superadmin,
        showAddCabinet = $bindable(false),
        onAddCabinetClose,
    }: IProps = $props();
    let permissions = getPermissionsFromUserType(superadmin.type);
    let showManageDoctors = $state(false);
</script>

<Dashboard>
    {#if permissions.find((e) => e.endsWith("_cabinet"))}
        <ManageCabinets user={superadmin}></ManageCabinets>
    {/if}

    {#if permissions.find((e) => e.endsWith("_cabinet"))}
        <AddCabinet user={superadmin} onClose={() => {}} onAdd={() => {}}
        ></AddCabinet>
    {/if}
</Dashboard>
