<script lang="ts">
    import { AllAPI } from "$lib/api";
    import Avatar from "$lib/components/ui/Avatar.svelte";
    import Input from "$lib/components/ui/Input.svelte";
    import Modal from "$lib/components/ui/Modal.svelte";
    import type { User } from "$lib/types/users";
    import { Plus } from "@lucide/svelte";

    let {
        isOpen = false,
        onClose,
        onSelect,
        type,
    }: {
        isOpen: boolean;
        onClose: () => void;
        onSelect: (email: string) => Promise<void>;
        type: "doctor" | "assistant";
    } = $props();

    let users: User<any>[] = $state([]);
    let searchQuery = $state("");
    let loading = $state(false);
    let selectingEmail = $state<string | null>(null);

    async function loadUsers() {
        loading = true;
        try {
            if (type === "doctor") {
                users = await AllAPI.listAllDoctors();
            } else {
                users = await AllAPI.listAllAssistants();
            }
        } catch (e) {
            console.error("Failed to load users", e);
        } finally {
            loading = false;
        }
    }

    $effect(() => {
        if (isOpen) {
            loadUsers();
            searchQuery = "";
        }
    });

    let filteredUsers = $derived(
        users.filter((u) => {
            const q = searchQuery.toLowerCase();
            return (
                u.firstName.toLowerCase().includes(q) ||
                u.lastName.toLowerCase().includes(q) ||
                u.email.toLowerCase().includes(q)
            );
        }),
    );

    async function handleSelect(email: string) {
        selectingEmail = email;
        await onSelect(email);
        selectingEmail = null;
    }
</script>

<Modal
    {isOpen}
    title={`Add Existing ${type === "doctor" ? "Doctor" : "Assistant"}`}
    {onClose}
>
    <div class="user-selector">
        <div class="search-bar">
            <Input
                category="input"
                type="text"
                placeholder="Search by name or email..."
                bind:value={searchQuery}
                theme="secondary"
            />
        </div>

        <div class="user-list">
            {#if loading}
                <div class="loading">Loading...</div>
            {:else if filteredUsers.length === 0}
                <div class="empty">No users found</div>
            {:else}
                {#each filteredUsers as user}
                    <button
                        class="user-row"
                        onclick={() => handleSelect(user.email)}
                        disabled={selectingEmail !== null}
                    >
                        <div class="user-info">
                            <Avatar
                                size="40px"
                                avatarUrl={user.avatarUrl}
                                alt={user.firstName}
                            />
                            <div class="details">
                                <span class="name"
                                    >{user.firstName} {user.lastName}</span
                                >
                                <span class="email">{user.email}</span>
                            </div>
                        </div>
                        <div class="action">
                            {#if selectingEmail === user.email}
                                <span class="spinner">...</span>
                            {:else}
                                <Plus size={20} />
                            {/if}
                        </div>
                    </button>
                {/each}
            {/if}
        </div>
    </div>
</Modal>

<style>
    .user-selector {
        display: flex;
        flex-direction: column;
        height: 60vh;
        max-height: 600px;
    }

    .search-bar {
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border-color, #eee);
    }

    .user-list {
        flex: 1;
        overflow-y: auto;
        padding-top: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .user-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem;
        border: none;
        background: transparent;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.2s;
        text-align: left;
        width: 100%;
    }

    .user-row:hover {
        background-color: var(--background-secondary, #f5f5f7);
    }

    .user-row:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .details {
        display: flex;
        flex-direction: column;
    }

    .name {
        font-weight: 500;
        color: var(--text-primary, #333);
        font-size: 1rem;
    }

    .email {
        font-size: 0.85rem;
        color: var(--text-secondary, #666);
    }

    .action {
        color: var(--color-primary, #007aff);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .loading,
    .empty {
        padding: 2rem;
        text-align: center;
        color: var(--text-secondary, #666);
    }

    .spinner {
        font-size: 0.8rem;
    }
</style>
