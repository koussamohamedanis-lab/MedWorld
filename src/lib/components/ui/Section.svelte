<script lang="ts">
    import { currentBlock } from "$lib/stores";
    import type { Group } from "$lib/types/permission";
    import { ArrowLeft } from "@lucide/svelte";

    interface IProps {
        title?: string;
        children?: any;
    }

    let { title = "", children }: IProps = $props();
</script>

<main>
    <button class="back">
        <h2>{title}</h2>
    </button>

    <div class="container">
        {#if children}
            {@render children()}
        {/if}
    </div>
</main>

<style>
    .back {
        display: flex;
        align-items: center;
        gap: 1rem;
        background: none;
        border: none;
        cursor: pointer;
        margin-bottom: 1.5rem;
        padding: 0;
    }

    .back h2 {
        font-family: var(--font-secondary);
        font-size: 2rem;
        font-weight: 400;
        margin: 0;
    }

    .back-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s ease;
    }

    .back:hover .back-icon {
        transform: translateX(-4px);
    }

    .back:hover h2 {
        text-decoration: underline;
    }

    main {
        display: flex;
        flex-direction: column;
        gap: 0;
    }

    h2 {
        font-family: var(--font-secondary);
        margin: 0;
        font-size: 2.5rem;
        font-weight: 300;
    }

    .container {
        background-color: rgba(255, 255, 255, 0.8);
        border-radius: 1rem;
        padding: 2rem;
        box-shadow:
            0 4px 6px -1px rgba(0, 0, 0, 0.1),
            0 2px 4px -1px rgba(0, 0, 0, 0.06);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.18);
        transition:
            transform 0.2s ease,
            box-shadow 0.2s ease;
    }

    .container:hover {
        transform: translateY(-2px);
        box-shadow:
            0 10px 15px -3px rgba(0, 0, 0, 0.1),
            0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
</style>
