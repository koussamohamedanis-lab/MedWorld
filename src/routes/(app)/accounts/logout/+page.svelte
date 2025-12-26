<script lang="ts">
    import { onMount } from "svelte";
    import { user } from "$lib/stores";
    import { AuthAPI } from "$lib/api";
    import Logo from "$lib/components/Logo.svelte";
    import { fade } from "svelte/transition";

    onMount(async () => {
        // Optional: Call backend logout
        try {
            await AuthAPI.logout();
        } catch (e) {
            console.error("Backend logout failed:", e);
        }

        // Clear local state
        localStorage.removeItem("authToken");
        localStorage.removeItem("user");
        localStorage.removeItem("userID");
        user.set(null);

        // Wait a bit for the animation to be seen
        setTimeout(() => {
            window.location.href = "/accounts/login";
        }, 1500);
    });
</script>

<main transition:fade>
    <div class="content">
        <div class="logo-wrapper">
            <Logo rotate />
        </div>
        <h1>Logging you out...</h1>
        <p>Thank you for using MedWorld. See you soon!</p>

        <div class="loader">
            <div class="bar"></div>
        </div>
    </div>
</main>

<style>
    main {
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: radial-gradient(circle at center, #ffffff 0%, #f0f4f8 100%);
        color: var(--color-primary-dark);
        text-align: center;
    }

    .content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1.5rem;
        max-width: 400px;
        padding: 2rem;
    }

    .logo-wrapper {
        height: 80px;
        margin-bottom: 1rem;
    }

    h1 {
        font-size: 2rem;
        font-family: var(--font-brand);
        font-weight: 500;
        margin: 0;
    }

    p {
        color: var(--text-secondary);
        font-size: 1.1rem;
        margin: 0;
    }

    .loader {
        width: 100%;
        height: 4px;
        background: rgba(0, 0, 0, 0.05);
        border-radius: 2px;
        overflow: hidden;
        margin-top: 1rem;
    }

    .bar {
        width: 40%;
        height: 100%;
        background: var(--color-primary);
        border-radius: 2px;
        animation: loading 1.5s infinite ease-in-out;
    }

    @keyframes loading {
        0% {
            transform: translateX(-100%);
        }
        100% {
            transform: translateX(250%);
        }
    }
</style>
