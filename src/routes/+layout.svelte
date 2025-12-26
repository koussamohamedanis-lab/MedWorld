<script lang="ts">
    import "../app.css";

    import AOS from "aos";
    import "aos/dist/aos.css";

    import View from "$lib/components/ui/View.svelte";
    import Logo from "$lib/components/Logo.svelte";

    import { fade } from "svelte/transition";
    import { onMount } from "svelte";
    import { navigating, page } from "$app/state";
    import { links, SITE_DESCRIPTION, SITE_NAME } from "$lib";
    import { loadAllData } from "$lib/stores/data";

    let { children } = $props();

    function scrollHandler() {
        const handleScroll = () => {
            // isFull = window.scrollY > 28;
        };

        window.addEventListener("scroll", handleScroll);
        handleScroll();

        return () => {
            window.removeEventListener("scroll", handleScroll);
        };
    }

    onMount(async () => {
        scrollHandler();
        AOS.init();
    });
</script>

{#if navigating.to}
    <div class="loading-spinner" transition:fade>
        <div class="icon">
            <Logo rotate />
        </div>
    </div>
{/if}

{@render children?.()}

<footer>
    <View center>
        <div class="footer-content">
            <div class="footer-brand">
                <div class="footer-logo">
                    <Logo />
                </div>
                <h1 class="footer-name">
                    {SITE_NAME}
                </h1>
                <p class="slogan">
                    {SITE_DESCRIPTION}
                </p>
            </div>

            <div class="footer-links">
                <h3>Quick Links</h3>
                <ul>
                    {#each links as link}
                        <li><a href={link.url}>{link.name}</a></li>
                    {/each}
                </ul>
            </div>

            <div class="footer-contact">
                <h3>Contact Us</h3>
                <ul>
                    <li>
                        <a href="tel:+213553238410">+213 (0) 553 238 410</a>
                    </li>

                    <li>
                        <a href="mailto:contact@medworld.com"
                            >contact@medworld.com</a
                        >
                    </li>

                    <li>
                        <a href="https://wa.me/+213553238410">WhatsApp</a>
                    </li>
                </ul>
            </div>
        </div>
    </View>
</footer>

<style>
    .loading-spinner {
        z-index: 99999;
        position: fixed;
        top: 0;
        left: 0;
        width: 100dvw;
        height: 100dvh;
        background-color: rgba(255, 255, 255, 0.714);
        backdrop-filter: blur(20px);

        display: flex;
        justify-content: center;
        align-items: center;
    }

    .icon {
        width: 10rem;
        height: 10rem;
    }

    footer {
        background: var(--color-primary-darker);
        padding: 4rem 0;
    }

    footer * {
        color: var(--background-primary);
    }

    .footer-content {
        width: 100%;

        display: grid;
        grid-template-columns: 2fr 1fr 1fr;
        gap: 4rem;
    }

    .footer-logo {
        width: 120px;
        margin-bottom: 1rem;
    }

    .footer-name {
        margin-bottom: 1rem;
        font-weight: 100;
        font-family: var(--font-brand);
        font-weight: 100;
    }

    .slogan {
        font-size: 1.2rem;
        max-width: 300px;
        line-height: 1.6;
    }

    .footer-links,
    .footer-contact {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .footer-links h3,
    .footer-contact h3 {
        font-size: 1.4rem;
        margin-bottom: 1.5rem;
        font-weight: 500;
    }

    .footer-links ul,
    .footer-contact ul {
        list-style: none;
        padding: 0;
    }

    .footer-links li,
    .footer-contact li {
        margin-bottom: 0.8rem;
    }

    .footer-links a,
    .footer-contact a {
        color: var(--background-primary);

        text-decoration: none;
        transition: color 0.2s ease;
    }

    .footer-links a:hover,
    .footer-contact a:hover {
        color: var(--color-primary);
    }

    @media screen and (max-width: 768px) {
        .footer-content {
            grid-template-columns: 1fr;
            gap: 2rem;
        }
    }
</style>
