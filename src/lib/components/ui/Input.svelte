<script lang="ts">
    interface Props {
        category?: "textarea" | "input" | "select" | "display";
        theme?: "primary" | "secondary" | "symbollic" | "nothing";
        type?: string;
        label?: string;
        value?: any;
        placeholder?: string;
        error?: string;
        required?: boolean;
        name?: string;
        showLabel?: boolean;
        showTooltip?: boolean;
        options?: { value: any; label: string }[];
        disabled?: boolean;
        rows?: number;
        min?: string;
        max?: string;
        focused?: boolean;
        validation?: (value: any) => string | null;
        onInput?: any;
        [key: string]: any;
    }

    let {
        category = "input",
        type = "text",
        label = "",
        showLabel = false,
        showTooltip = false,
        theme = "primary",
        value = $bindable(""),
        placeholder = "",
        error = $bindable(""),
        required = false,
        name = "",
        options = [],
        disabled = false,
        focused = $bindable(false),
        rows = 4,
        min = "",
        max = "",
        validation = undefined,
        onInput = () => {},
        ...rest
    }: Props = $props();

    label = label || placeholder || "Enter text here...";
    placeholder = placeholder || `${label}`;

    let inputElement:
        | HTMLInputElement
        | HTMLTextAreaElement
        | HTMLSelectElement = $state() as any;

    export function focus() {
        inputElement?.focus();
    }

    function validate() {
        if (required && !value) {
            error = "This field is required";
            return false;
        }

        if (validation) {
            const validationError = validation(value);
            if (validationError) {
                error = validationError;
                return false;
            }
        }

        error = "";
        return true;
    }

    function handleInput(e: Event) {
        const target = e.target as
            | HTMLInputElement
            | HTMLTextAreaElement
            | HTMLSelectElement;
        value = target.value;
        if (error) validate();
        onInput(e);
    }

    function handleBlur() {
        validate();
    }

    $effect(() => {
        inputElement?.addEventListener("focus", () => {
            focused = true;
        });
        inputElement?.addEventListener("blur", () => {
            focused = false;
        });
    });

    export { validate };
</script>

<main class:disabled class={theme === "symbollic" ? "symbollic" : ""}>
    {#if label && showLabel}
        <label for={label.toLowerCase().replace(/\s+/g, "-")}>
            {label}
            {#if required}<span class="required">*</span>{/if}
        </label>
    {/if}

    {#if category === "textarea"}
        <textarea
            bind:this={inputElement}
            class="input"
            id={label.toLowerCase().replace(/\s+/g, "-")}
            {value}
            class:primary={theme === "primary"}
            class:secondary={theme === "secondary"}
            class:nothing={theme === "nothing"}
            {placeholder}
            oninput={handleInput}
            onblur={handleBlur}
            class:error={!!error}
            {required}
            {name}
            {disabled}
            {rows}
            {...rest}
        ></textarea>
    {:else if category === "select"}
        <select
            bind:this={inputElement}
            class="input"
            id={label.toLowerCase().replace(/\s+/g, "-")}
            {value}
            class:primary={theme === "primary"}
            class:secondary={theme === "secondary"}
            class:none={theme === "nothing"}
            oninput={handleInput}
            onblur={handleBlur}
            class:error={!!error}
            {required}
            {name}
            {disabled}
            {...rest}
        >
            {#if placeholder}
                <option value="" disabled selected>{placeholder}</option>
            {/if}
            {#each options as option}
                <option value={option.value}>{option.label}</option>
            {/each}
        </select>
    {:else if category === "input"}
        <input
            bind:this={inputElement}
            class="input"
            id={label.toLowerCase().replace(/\s+/g, "-")}
            {type}
            class:primary={theme === "primary"}
            class:secondary={theme === "secondary"}
            class:none={theme === "nothing"}
            {value}
            {placeholder}
            oninput={handleInput}
            onblur={handleBlur}
            class:error={!!error}
            {required}
            {name}
            {disabled}
            {min}
            {max}
            {...rest}
        />
    {:else}
        <p>{value}</p>
    {/if}
    {#if error}
        <span class="error-message">{error}</span>
    {/if}
</main>

<style>
    main {
        margin-bottom: 1.5rem;
        text-align: left !important;
        width: 100%;
    }

    main.disabled {
        opacity: 0.7;
        pointer-events: none;
    }

    label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--text-color);
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .required {
        color: #e74c3c;
        margin-left: 0.25rem;
    }

    .input {
        width: 100%;
        font-family: var(--font-secondary);
        color: var(--text-color);
        transition: var(--transition);
        outline: none;
    }

    .input.primary {
        padding: 16px 0;
        border: none;
        font-size: 16px;
        background: transparent;
        border-bottom: 1px solid var(--border-color);
    }

    .input.primary:focus {
        outline: none;
        border-bottom-color: var(--text-color);
    }

    .input.primary::placeholder {
        color: var(--text-secondary);
    }

    .input.secondary {
        padding: 12px 16px;
        border: 2px solid var(--border-color);
        border-radius: 8px;
        font-size: 16px;
        background: var(--background-secondary);
    }

    .input.secondary:focus {
        outline: none;
        border-color: var(
            --text-color
        ); /* Changed from fixed color to variable */
    }

    .input.secondary::placeholder {
        color: var(--text-secondary);
    }

    .input.nothing {
        padding: 0;
        border: none;
        background: transparent;
        font-size: inherit;
    }

    .input.nothing:focus {
        outline: none;
    }

    .input.symbollic {
        padding: 12px 16px;
        font-size: 16px;
        background: var(--background-secondary);
        border: none !important;
        outline: none;
    }

    main.symbollic {
        margin-bottom: 0;
    }

    .input.error {
        border-color: #e74c3c !important;
    }

    .error-message {
        color: #e74c3c;
        font-size: 12px;
        margin-top: 0.5rem;
        display: block;
    }

    textarea.input {
        resize: vertical;
        min-height: 100px;
    }
</style>
