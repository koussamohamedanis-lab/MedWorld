<script lang="ts">
    import { Search } from "@lucide/svelte";
    import Input from "$lib/components/ui/Input.svelte";
    import type { HTMLAttributes } from "svelte/elements";
    import { onMount } from "svelte";

    interface IProps extends HTMLAttributes<HTMLElement> {
        placeholder?: string;
        value?: string;
        searchInput?: any;
        onSearch?: (() => any) | ((e: string) => any);
    }

    let {
        placeholder = "Rechercher un livre...",
        onSearch = (e: any) => {},
        value = $bindable(""),
        searchInput = $bindable(),
        ...rest
    }: IProps = $props();

    let focused = $state(false);
</script>

<!-- svelte-ignore a11y_click_events_have_key_events -->
<!-- svelte-ignore a11y_no_noninteractive_element_interactions -->
<main onclick={() => searchInput?.focus()} class:focused {...rest}>
    <Input
        type="text"
        {placeholder}
        bind:this={searchInput}
        bind:focused
        bind:value
        theme="symbollic"
    />

    <button onclick={() => onSearch(value)}>
        <Search />
    </button>
</main>

<style>
    :root {
        --padding-search: 2rem;
    }

    main {
        width: 100%;
        display: inline-flex;
        align-items: center;

        background-color: var(--background-primary);
        border: 1px solid var(--color-primary);
        border-radius: 20px;

        cursor: text;

        height: 60px;
        padding: 0.3rem 0;
        transition: all var(--transition-duration) var(--transition-easing);
    }

    :global(main .input) {
        flex: 1;
        width: 100%;
        padding-left: 1.5rem;
        font-size: 1.2rem;
        background-color: transparent;
        border: none;
    }

    :global(main .input:focus) {
        outline: none;
    }

    main:focus-within,
    main.focused {
        border: 3px solid var(--color-primary);
    }

    :global(main .input::placeholder) {
        font-family: var(--font-secondary);
    }

    button {
        display: grid;
        place-items: center;
        cursor: pointer;

        height: 100%;
        aspect-ratio: 1 / 1;
        border-radius: 50%;
        margin: calc(var(--padding-search) / 4);

        border: none;
        background-color: var(--background-primary);
        color: var(--color-primary-dark);

        transition: all var(--transition-duration) var(--transition-easing);
    }

    button:hover {
        transform: scale(1.05) rotate(5deg);
    }

    @media screen and (max-width: 600px) {
        :root {
            --padding-search: 1rem;
        }

        :global(main .input) {
            font-size: 0.875rem;
        }
    }
</style>
