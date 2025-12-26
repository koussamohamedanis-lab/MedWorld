<script lang="ts">
    interface IProps {
        size?: string;
        color?: string;
    }

    let { size = '40px', color = 'var(--primary)' }: IProps = $props();
</script>

<div class="spinner" style="--size: {size}; --color: {color}"></div>

<style>
    .spinner {
        width: var(--size);
        height: var(--size);
        border: 3px solid rgba(0, 0, 0, 0.1);
        border-top-color: var(--color);
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }
</style>
