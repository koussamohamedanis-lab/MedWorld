<script lang="ts">
    import SearchInput from "$lib/components/ui/SearchInput.svelte";
    import type { Cabinet } from "$lib/types/cabinet";
    import {
        cabinets as cabinetsStore,
        loadAllCabinets,
        isLoadingCabinets,
    } from "$lib/stores/data";
    import type { IUser } from "$lib/types/users";
    import type { Admin } from "$lib/types/users/admin";
    import type { Doctor } from "$lib/types/users/doctor";
    import { onMount } from "svelte";
    import CabinetCard from "./CabinetCard.svelte";
    import { scale } from "svelte/transition";

    let {
        onSelect = (cabinet: Cabinet) => {},
        user,
    }: {
        onSelect: (cabinet: Cabinet) => void;
        user: IUser;
    } = $props();

    let search: string = $state("");

    onMount(async () => {
        if ($cabinetsStore.length === 0) {
            await loadAllCabinets();
        }
    });

    let displayedCabinets = $derived.by(() => {
        let list = $cabinetsStore;
        console.log("CabinetSelector: Filtering with search:", search);

        // 1. Filter by Permissions
        if (user.type !== "superadmin") {
            const cabinetId = (user as any).cabinet?.id;
            if (cabinetId) {
                list = list.filter((c) => c.id === cabinetId);
            }
        }

        // 2. Filter by Search
        const query = search.toLowerCase().trim();
        if (query) {
            list = list.filter((cabinet) => {
                const nameMatch = cabinet.name?.toLowerCase().includes(query);
                const addressMatch = cabinet.location?.address
                    ?.toLowerCase()
                    .includes(query);
                const doctorMatch = cabinet.doctors?.some((d) => {
                    const fullName =
                        `${d.firstName || ""} ${d.lastName || ""}`.toLowerCase();
                    const speciality = d.speciality?.toLowerCase() || "";
                    return (
                        fullName.includes(query) || speciality.includes(query)
                    );
                });
                return nameMatch || addressMatch || doctorMatch;
            });
        }

        return list;
    });
</script>

<div class="cabinet-selector">
    <div class="search-container">
        <SearchInput
            placeholder="Search cabinets..."
            bind:value={search}
            class="search-input"
        />
    </div>

    {#if displayedCabinets.length === 0}
        <pre
            style="text-align: center; font-size: 2rem;">No cabinets found.</pre>
    {:else}
        <div class="cabinets-grid">
            {#each displayedCabinets as cabinet (cabinet.id)}
                <button onclick={() => onSelect(cabinet)} transition:scale>
                    <CabinetCard {cabinet} />
                </button>
            {/each}
        </div>
    {/if}
</div>

<style>
    button {
        background-color: transparent;
        /*é */
        border: none;
        outline: none;
    }

    .cabinet-selector {
        width: 100%;
        margin-bottom: 1rem;
    }

    .search-container {
        width: 100%;
        margin-bottom: 1rem;
    }

    .cabinets-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    @media (min-width: 768px) {
        .cabinets-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (min-width: 1024px) {
        .cabinets-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }
</style>
