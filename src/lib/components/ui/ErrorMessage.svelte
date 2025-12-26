<script lang="ts">
    interface IProps {
        message?: string;
        error?: Error | any;
    }

    let { message, error }: IProps = $props();

    const errorMessage = $derived(
        message ||
        (error?.message || 'An error occurred')
    );
</script>

<div class="error-message">
    <div class="error-icon">⚠️</div>
    <div class="error-content">
        <h3>Error</h3>
        <p>{errorMessage}</p>
    </div>
</div>

<style>
    .error-message {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: rgba(220, 53, 69, 0.1);
        border: 1px solid rgba(220, 53, 69, 0.3);
        border-radius: var(--radius);
        color: #dc3545;
    }

    .error-icon {
        font-size: 2rem;
    }

    .error-content h3 {
        margin: 0 0 0.25rem 0;
        font-size: 1rem;
        font-weight: 600;
    }

    .error-content p {
        margin: 0;
        font-size: 0.9rem;
        opacity: 0.9;
    }
</style>
