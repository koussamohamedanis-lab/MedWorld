<script lang="ts">
    import { currentBlock } from "$lib/stores";

    interface IProps {
        children: any;
    }

    let { children }: IProps = $props();
</script>

<!-- stop following me -->

<main class:content-shown={$currentBlock.length > 0}>
    {@render children?.()}
</main>

<style>
    :root {
        --col-size: 300px;
    }

    /* show buttons view */
    main {
        padding-top: 2rem;
        margin-bottom: 4rem;

        gap: 1rem;

        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(var(--col-size), 1fr));

        transition: all var(--transition-duration-slow) var(--transition-easing);
    }

    /* when a block in the dasboard is open */
    main.content-shown {
        --col-size: 100%;
        gap: 0rem;
    }
</style>
