<script lang="ts">
    import {
        blockBack,
        currentBlock,
        currentLevel,
        gotoBlock,
    } from "$lib/stores";
    import type { Group } from "$lib/types/permission";
    import { ArrowLeft } from "@lucide/svelte";
    import { onMount } from "svelte";

    function inBlock(block: Group) {
        return (
            $currentBlock.length > 0 &&
            $currentBlock[$currentBlock.length - 1] === block
        );
    }

    interface IProps {
        title?: string;
        group: Group;
        Icon?: any;
        children?: any;
        alwaysOpen?: boolean;
        onBack?: () => void;
    }

    let {
        title = "",
        children,
        group,
        Icon,
        alwaysOpen = false,
        onBack = () => {},
    }: IProps = $props();

    function back() {
        blockBack();
        onBack();
    }

    onMount(() => {
        if (alwaysOpen) {
            gotoBlock(group);
        }
    });
</script>

<section
    class="card"
    class:content={inBlock(group)}
    class:shrink={!inBlock(group) && $currentBlock.length > 0}
>
    <main class:shrink={!inBlock(group)}>
        <button class="back" onclick={() => back()}>
            <div class="back-icon">
                <ArrowLeft />
            </div>
            <h2>{title}</h2>
        </button>

        <div class="container">
            {@render children?.()}
        </div>
    </main>

    <button
        class="button"
        onclick={() => gotoBlock(group)}
        class:shrink={inBlock(group)}
    >
        {#if Icon}
            <Icon />
        {/if}

        <h3>
            {title}
        </h3>
    </button>
</section>

<style>
    main {
        max-height: 7000px; /* a value larger than any content */
        max-width: 100%;
        transition:
            max-height var(--transition-duration) var(--transition-easing),
            max-width var(--transition-duration) var(--transition-easing);
    }

    :not(.shrink) {
        transition: all var(--transition-duration) var(--transition-easing);
    }

    main.shrink {
        max-height: 0;
        max-width: 0;
        overflow: hidden;
    }

    .shrink:not(main),
    .shrink:not(main) :global(*) {
        width: 0 !important;
        height: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
        overflow: hidden !important;
        border: none !important;
    }

    section {
        padding: 0;
        margin: 0;
        display: grid;
    }

    section > :global(*) {
        grid-area: 1/1;
    }

    .card {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border-radius: 1rem;
        box-shadow:
            0 4px 6px -1px rgba(0, 0, 0, 0.1),
            0 2px 4px -1px rgba(0, 0, 0, 0.06);
        background-color: var(--color-primary-dark);

        transition: all var(--transition-duration) var(--transition-easing);
        width: 100%;
        height: 100%;
    }

    .card:not(.content) :global(*) {
        color: white;
    }

    .card:not(.content):hover {
        transform: translateY(-2px);
        box-shadow:
            0 10px 15px -3px rgba(0, 0, 0, 0.1),
            0 4px 6px -2px rgba(0, 0, 0, 0.05);
        background-color: var(--color-primary);
    }

    .card.content {
        background-color: transparent;
        padding: 0;
        box-shadow: none;
        border-radius: 0;
    }

    .button {
        padding: 1.5rem;
        cursor: pointer;
        background-color: transparent;
        border: none;
        width: 100%;
        height: 100%;
    }

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

    .card h3 {
        margin: 1rem 0 0 0;
        font-size: 1.5rem;
        font-weight: 400;
    }

    main {
        display: flex;
        flex-direction: column;
        gap: 0;
        width: 100%;
        height: 100%;
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

    @media (max-width: 768px) {
        .back h2 {
            font-size: 1.1rem;
            font-weight: 500;
        }

        .container {
            padding: 0;
            background-color: transparent;
            box-shadow: none;
            overflow-x: auto;
        }

        .container:hover {
            padding: 0;
            background-color: transparent;
            box-shadow: none;
            overflow-x: auto;
        }
    }
</style>
