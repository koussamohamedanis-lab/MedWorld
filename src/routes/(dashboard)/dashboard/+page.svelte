<script lang="ts">
    import AdminDashboard from "$lib/components/dashboard/AdminDashboard.svelte";
    import AssistantDashboard from "$lib/components/dashboard/AssistantDashboard.svelte";
    import CabinetSelector from "$lib/components/dashboard/blocks/cabinets/CabinetSelector.svelte";
    import DoctorDashboard from "$lib/components/dashboard/DoctorDashboard.svelte";
    import PatientDashboard from "$lib/components/dashboard/PatientDashboard.svelte";
    import SuperAdminDashboard from "$lib/components/dashboard/SuperAdminDashboard.svelte";
    import Avatar from "$lib/components/ui/Avatar.svelte";
    import View from "$lib/components/ui/View.svelte";

    import { currentBlock, currentCabinet, loadUser, user } from "$lib/stores";

    import {
        Users,
        UserTypeNames,
        type IDoctor,
        type User,
        type UserType,
    } from "$lib/types/users";

    import type { Admin } from "$lib/types/users/admin";
    import type { Doctor } from "$lib/types/users/doctor";
    import type { Patient } from "$lib/types/users/patient";
    import type { Assistant } from "$lib/types/users/assistant";
    import type { SuperAdmin } from "$lib/types/users/superadmin";

    import { scale } from "svelte/transition";
    import { ArrowLeft } from "@lucide/svelte";

    let loading = $state(true);

    $effect(() => {
        const checkAuth = async () => {
            await loadUser();
            loading = false;

            user.subscribe((currentUser) => {
                if (!loading && !currentUser) {
                    // he is not authenticated thne redirect to login
                    window.location.href = "/accounts/login";
                } else if (currentUser) {
                    // he is authenticated so store their ID
                    localStorage.setItem("userID", currentUser.id.toString());
                }
            });
        };

        checkAuth();
    });

    function getCabinetFromUser(user: User<any>) {
        if (!user) return ""

        const cabinetName = (user as any)?.cabinet?.name as string | undefined;
        const cabinetId = (user as any)?.cabinetId as number | undefined;

        switch (user.type) {
            case Users.Doctor:
            case Users.Admin:
            case Users.Assistant:
                return cabinetName || (cabinetId ? `#${cabinetId}` : "None");
            case Users.Patient:
                return "N/A";
            case Users.SuperAdmin:
                return "All";
            default:
                return cabinetName || (cabinetId ? `#${cabinetId}` : "");
        }
    }
</script>

{#if $user}
    <View style="padding-top: 2rem;" screen>
        <button
            class="welcome"
            class:action={$currentCabinet !== null}
            onclick={() => currentCabinet.set(null)}
        >
            {#if $currentCabinet !== null}
                <div class="icon" transition:scale>
                    <ArrowLeft size="2rem" />
                </div>
            {/if}

            <div class="wrapper">
                <Avatar
                    size="4rem"
                    avatarUrl={$user.avatarUrl}
                    alt={$user.firstName + " " + $user.lastName}
                    original
                />

                <div class="info">
                    <h1>
                        {$user.type === Users.Doctor ||
                        $user.type === Users.Admin
                            ? "Dr. "
                            : ""}
                        {$user.fullName}
                        {($user.type === Users.Doctor ||
                        $user.type === Users.Admin)
                            ? " (" + (($user as IDoctor).assistant?.fullName || "No assistant") + ")"
                            : ""}
                        {$user.type === Users.Admin ? " (Admin)" : ""}
                        {$user.type === Users.SuperAdmin ? " (Super-Admin)" : ""}
                        {$user.type === Users.Assistant
                            ? ` (Assistant of ${($user as Assistant).doctor?.fullName || ""})`
                            : ""}
                    </h1>

                    <p>
                        Cabinet: {getCabinetFromUser($user)}
                    </p>

                    {#if $currentCabinet}
                        <h3 class="cabinet">
                            At {$currentCabinet.name}
                        </h3>
                    {/if}
                </div>
            </div>
        </button>

        <!-- {#if $currentCabinet === null}
            <CabinetSelector
                user={$user}
                bind:selectedCabinet={$currentCabinet}
            /> -->

        {#if $user.type === Users.Doctor}
            <DoctorDashboard doctor={$user as Doctor} />
        {:else if $user.type === Users.Patient}
            <PatientDashboard patient={$user as Patient} />
        {:else if $user.type === Users.SuperAdmin}
            <SuperAdminDashboard superadmin={$user as SuperAdmin} />
        {:else if $user.type === Users.Admin}
            <AdminDashboard admin={$user as Admin} />
        {:else if $user.type === Users.Assistant}
            <AssistantDashboard assistant={$user as Assistant} />
        {:else}
            <h2 style="color: var(--error)">
                Unknown Type: {$user.type}
            </h2>
        {/if}

        <!-- Demo Helper disabled - use normal login to test different accounts -->
    </View>
{/if}

<style>
    .selection-wrapper {
        margin-bottom: 2rem;
    }

    .selection-wrapper h3 {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        font-weight: 300;
    }

    .selection {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .selection p {
        font-size: 2rem;
        font-family: monospace;
        color: gray;
    }

    .action {
        cursor: pointer;
    }

    .welcome {
        gap: 1rem;
        background-color: transparent;
        border: none;

        display: flex;
        align-items: center;

        padding-bottom: 2rem;
        margin-bottom: 0rem;

        border-bottom: 1px solid var(--border-color-light);

        text-align: left;
    }

    .welcome .wrapper {
        display: flex;

        align-items: center;
        gap: 1rem;
    }

    .welcome h1 {
        font-size: 3rem;
    }

    .welcome .info {
        display: flex;
        flex-direction: column;

        margin-left: 0.5rem;
    }

    .welcome .cabinet {
        font-size: 1.25rem;
        font-weight: 300;
        color: var(--text-secondary);
    }

    .action:hover .cabinet {
        text-decoration: underline;
    }

    .action .icon {
        transition: transform 0.2s ease-in-out;
    }
    .action:hover .icon {
        transform: translateX(-5px);
    }

    @media screen and (max-width: 1000px) {
        .info h1 {
            font-size: 1rem;
        }

        .info .cabinet {
            font-size: 1rem;
        }

        .welcome {
            margin-bottom: 0;
        }
    }
</style>
