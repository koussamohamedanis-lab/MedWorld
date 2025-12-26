<script lang="ts">
    let {
        value = $bindable([]),
        show = false,
    }: {
        value: string[];
        show?: boolean;
    } = $props();

    function remove(index: number) {
        value.splice(index, 1);
        value = [...value];
    }

    function edit(index: number) {
        const current = value[index];
        const updated = prompt("Edit value:", current);
        if (updated && updated.trim() !== "") {
            value[index] = updated.trim();
            value = [...value];
        }
    }

    function add() {
        const entry = prompt("Enter a value:");
        if (entry && !value.includes(entry)) {
            value = [...value, entry];
        }
    }
</script>

{#if show}
    <main class="chip-list">
        {#each value as entry, index}
            <div class="chip" title="Click to edit">
                {entry}
            </div>
        {/each}
    </main>
{:else}
    <main class="chip-list">
        {#each value as entry, index}
            <!-- svelte-ignore a11y_click_events_have_key_events -->
            <!-- svelte-ignore a11y_no_static_element_interactions -->
            <div class="chip" title="Click to edit" onclick={() => edit(index)}>
                {entry}
                <button
                    type="button"
                    class="chip-remove"
                    onclick={(e) => {
                        e.stopPropagation();
                        remove(index);
                    }}
                >
                    ×
                </button>
            </div>
        {/each}

        <button type="button" class="add-chip" onclick={add}>
            + Add Entry
        </button>
    </main>
{/if}

<style>
    .chip[title] {
        cursor: pointer;
    }
    .chip:hover {
        opacity: 0.9;
    }

    .chip-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 0.5rem;
    }

    .chip {
        background: var(--primary-color, var(--color-primary-dark));
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        width: fit-content;
    }

    .chip-remove {
        background: none;
        border: none;
        color: white;
        cursor: pointer;
        padding: 0;
        font-size: 1rem;
    }

    .add-chip {
        background: var(--success, #27ae60);
        color: white;
        border: none;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        cursor: pointer;
        font-size: 0.8rem;
    }
</style>
