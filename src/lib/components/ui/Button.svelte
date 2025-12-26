<script lang="ts">
  let {
    href,
    category = "primary",
    label = "",
    Icon = null,
    iconPosition = "left",
    disabled = false,
    autoWidth = false,
    onClick = () => {},
    children = null,
    color = "var(--color-primary)",
    isSubmit = false,

    // css
    large = false,
    uppercase = false,
    round = false,
    blink = false,

    ...rest
  }: {
    href?: string;
    category?:
      | "primary"
      | "secondary"
      | "third"
      | "gold"
      | "secondary-gold"
      | "cta"
      | "error";
    label?: string;
    Icon?: any;
    iconPosition?: "left" | "right";
    autoWidth?: boolean;
    onClick?: () => void;
    children?: any;
    disabled?: boolean;
    color?: string;

    round?: boolean;
    large?: boolean;
    uppercase?: boolean;
    blink?: boolean;

    isSubmit?: boolean;
  } & Record<string, any> = $props();
</script>

{#if href && !disabled}
  <a
    class="button {category}"
    class:large
    class:uppercase
    class:round
    class:blink
    class:auto-width={autoWidth}
    {href}
    {...rest}
    onclick={onClick}
  >
    {#if Icon && iconPosition === "left"}
      <Icon />
    {/if}

    {#if label}
      <span class="label">{label}</span>
    {/if}

    {@render children?.()}

    {#if Icon && iconPosition === "right"}
      <Icon />
    {/if}
  </a>
{:else}
  <button
    class="button {category}"
    class:large
    class:uppercase
    class:round
    class:blink
    class:auto-width={autoWidth}
    onclick={onClick}
    {disabled}
    {...rest}
  >
    {#if Icon && iconPosition === "left"}
      <Icon />
    {/if}

    {#if label}
      <span class="label">{label}</span>
    {/if}

    {@render children?.()}

    {#if Icon && iconPosition === "right"}
      <Icon />
    {/if}
  </button>
{/if}

<style>
  .button {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: var(--border-radius-md);
    font-family: var(--font-secondary);
    font-weight: 500;
    font-size: 0.95rem;
    text-decoration: none;
    cursor: pointer;
    transition: all var(--transition-normal);
    position: relative;
    overflow: hidden;

    flex: 1;
    direction: ltr;
  }

  .button.round {
    border-radius: 50px;
  }

  .button.blink .label {
    animation: margin-right 1s infinite;
  }

  .button.blink {
    animation: blink 2s infinite;
  }

  @keyframes blink {
    0% {
      opacity: 1;
      transform: scale(1);
    }
    50% {
      opacity: 0.5;
      transform: scale(1.02);
    }
    100% {
      opacity: 1;
      transform: scale(1);
    }
  }

  @keyframes margin-right {
    0% {
      margin-right: 0;
    }
    100% {
      margin-right: 0.2rem;
    }
  }

  .button.large {
    font-size: 1.1rem;
    padding: 1rem 2rem;
  }

  .button.uppercase {
    text-transform: uppercase;
  }

  .button[disabled] {
    cursor: not-allowed;
    opacity: 0.5;
  }

  .button:hover {
    transform: translateY(-2px);
  }

  .primary {
    background: var(--color-primary-dark);
    color: var(--white);
    box-shadow: 0 4px 12px rgba(var(--shadow-color-rgb), 0.3);
  }

  .primary:hover {
    background: var(--color-primary-light);
    box-shadow: 0 6px 16px rgba(var(--shadow-color-rgb), 0.2);
  }

  .secondary {
    background: transparent;
    color: var(--color-primary);
    border: 2px solid var(--color-primary);
  }

  .secondary:hover {
    color: var(--color-primary-dark);
    border-color: var(--white);
    background: var(--white);
  }

  .button.third {
    background: transparent;
    color: var(--color-primary-darker);
  }

  .button.third:hover {
    background: var(--color-primary-light);
  }

  .gold {
    background: var(--gold);
    color: var(--white);
    box-shadow: 0 4px 12px rgba(var(--shadow-color-rgb), 0.3);
  }

  .gold:hover {
    background: var(--gold-dark);
    box-shadow: 0 6px 16px rgba(var(--shadow-color-rgb), 0.2);
  }

  .secondary-gold {
    background: transparent;
    color: var(--gold);
    border: 2px solid var(--gold);
  }

  .secondary-gold:hover {
    color: var(--gold-dark);
    border-color: var(--white);
    background: var(--white);
  }

  .cta {
    font-size: 1.1rem;
    padding: 1rem 2rem;
  }

  .error {
    background: var(--error);
    color: var(--white);
    box-shadow: 0 4px 12px rgba(var(--shadow-color-rgb), 0.3);
  }

  @media (max-width: 768px) {
    .button.auto-width {
      width: 100%;
    }

    .button.large {
      font-size: 1rem;
      padding: 0.75rem 1.5rem;
    }
  }
</style>
