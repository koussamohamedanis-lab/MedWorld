<script lang="ts">
    import { Building } from "@lucide/svelte";
    import { gotoBlock } from "$lib/stores";
    import { getPermissionsFromUserType } from "$lib/types/permission";

    import type { Admin } from "$lib/types/users/admin";
    import type { Cabinet } from "$lib/types/cabinet";
    import type { SuperAdmin } from "$lib/types/users/superadmin";

    import Block from "$lib/components/ui/Block.svelte";
    import ManageCabinet from "./ManageCabinet.svelte";
    import CabinetSelector from "./cabinets/CabinetSelector.svelte";

    interface IProps {
        user: SuperAdmin | Admin;
    }

    let { user }: IProps = $props();
    let permissions = getPermissionsFromUserType(user.type);

    let selectedCabinet: Cabinet | null = $state(null);

    function selectCabinet(cabinet: Cabinet) {
        selectedCabinet = cabinet;
        gotoBlock("manage_cabinet");
    }
</script>

{#if selectedCabinet === null}
    <Block
        Icon={Building}
        group="manage_cabinet"
        title="Manage Cabinets"
        onBack={() => {
            selectedCabinet = null;
        }}
    >
        <CabinetSelector {user} onSelect={selectCabinet} />
    </Block>
{:else}
    <ManageCabinet
        {user}
        cabinet={selectedCabinet}
        onBack={() => {
            selectedCabinet = null;
        }}
    />
{/if}
