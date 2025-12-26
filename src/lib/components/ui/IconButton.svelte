<script lang="ts">
    // @ts-ignore
    import tippy from "sveltejs-tippy";
    import type { Snippet } from "svelte";
    import type { HTMLAttributes } from "svelte/elements";

    interface Props extends HTMLAttributes<HTMLDivElement> {
        type?: "primary" | "secondary" | "error";
        href?: string;
        label?: string;
        target?: "_blank" | "_self" | "_parent" | "_top" | "framename";
        autoWidth?: boolean;
        Icon?: any;
        color?: string;
        onClick?: () => void;
        onclick?: () => void;
        disabled?: boolean;
        children?: Snippet;
    }

    let {
        type = "primary",
        href = "",
        label = "",
        autoWidth = false,
        target = "_self",
        Icon,
        color = "",
        onClick,
        onclick,
        disabled = false,
        children,
        ...rest
    }: Props = $props();

    let tooltipProps = {
        content: label,
        placement: "top",
    };
</script>

{#if href}
    <a
        class="button"
        use:tippy={tooltipProps}
        aria-label="Panier"
        class:primary={type === "primary"}
        class:secondary={type === "secondary"}
        class:error={type === "error"}
        class:auto-width={autoWidth}
        style={color ? `background-color: ${color} !important;` : ""}
        {href}
        {target}
        {...rest}
    >
        {@render children?.()}

        {#if Icon}
            <Icon />
        {/if}
    </a>
{:else}
    <button
        class="button {type}"
        aria-label="Panier"
        use:tippy={tooltipProps}
        class:auto-width={autoWidth}
        onclick={onclick || onClick}
        class:primary={type === "primary"}
        class:secondary={type === "secondary"}
        class:error={type === "error"}
        style={color ? `background-color: ${color} !important;` : ""}
        {disabled}
        {...rest}
    >
        {@render children?.()}

        {#if Icon}
            <Icon />
        {/if}
    </button>
{/if}

<style>
    :root {
        --border-icon: 2px;
        --padding-icon: calc(1rem - var(--border-icon));
    }

    .button {
        text-decoration: none;
        padding: var(--padding-icon);
        border-radius: var(--border-radius-md);
        transition: all var(--transition-normal);
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        color: var(--white);
        padding: var(--padding-icon);
        border-radius: var(--border-radius-md);
        cursor: pointer;
        transition: all var(--transition-normal);
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
    }

    .button:hover {
        background: var(--color-secondary);
        color: var(--text-primary);
        transform: translateY(-2px);
        filter: brightness(1.1);
    }

    .button.primary {
        background: var(--color-primary);
        color: var(--white);
    }

    .button.primary:hover {
        background: var(--color-primary-dark);
        color: var(--white);
    }

    .button.secondary {
        background: var(--color-secondary);
        color: var(--white);
    }

    .button.secondary:hover {
        background: var(--primary-dark);
        color: var(--white);
    }

    .button.error {
        background: var(--error);
        color: var(--white);
    }

    .button.error:hover {
        background: var(--error-dark);
        color: var(--white);
    }

    /*
	@media screen and (max-width: 768px) {
		.button.selected {
		}
	} */
</style>
