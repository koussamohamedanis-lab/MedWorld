<script lang="ts">
  import "../../app.css";

  import AOS from "aos";
  import "aos/dist/aos.css";

  import { onMount } from "svelte";
  import { Lightbulb } from "@lucide/svelte";
  import { links, SITE_DESCRIPTION, SITE_NAME } from "$lib";
  import { slide } from "svelte/transition";

  import Logo from "$lib/components/Logo.svelte";
  import logo from "$lib/assets/logo.svg";
  import View from "$lib/components/ui/View.svelte";
  import Button from "$lib/components/ui/Button.svelte";
  import TopNotification from "$lib/components/TopNotification.svelte";

  import { Hamburger } from "svelte-hamburgers";
  import { page } from "$app/state";
  let { children } = $props();

  let isFull = $state(false);
  let isMenuOpen = $state(false);

  function scrollHandler() {
    const handleScroll = () => {
      isFull = window.scrollY > 28;
    };

    window.addEventListener("scroll", handleScroll);
    handleScroll();

    return () => {
      window.removeEventListener("scroll", handleScroll);
    };
  }

  onMount(() => {
    scrollHandler();

    AOS.init();
  });
</script>

<svelte:head>
  <link rel="icon" href={logo} />
  <title>{SITE_NAME}</title>

  <script>
    (function () {
      if (!window.chatbase || window.chatbase("getState") !== "initialized") {
        window.chatbase = (...arguments) => {
          if (!window.chatbase.q) {
            window.chatbase.q = [];
          }
          window.chatbase.q.push(arguments);
        };
        window.chatbase = new Proxy(window.chatbase, {
          get(target, prop) {
            if (prop === "q") {
              return target.q;
            }
            return (...args) => target(prop, ...args);
          },
        });
      }
      const onLoad = function () {
        const script = document.createElement("script");
        script.src = "https://www.chatbase.co/embed.min.js";
        script.id = "6O2pwAicVvqck4xGjVNm7";
        script.domain = "www.chatbase.co";
        document.body.appendChild(script);
      };
      if (document.readyState === "complete") {
        onLoad();
      } else {
        window.addEventListener("load", onLoad);
      }
    })();
  </script>
</svelte:head>

<main class:full={isMenuOpen}>
  <div class="container" class:full={isFull}>
    <TopNotification
      title={"Offering the most modern cabinet management technology."}
    />

    <View>
      <nav>
        <a class="logo" href="/">
          <div class="icon">
            <Logo rotate />
          </div>
          <h1 class:invisible={!isFull} class:none={!isFull} class="title">
            {SITE_NAME}
          </h1>
        </a>

        <ul class="desktop items" class:invisible={!isFull}>
          {#each links as link}
            <li><a href={link.url}>{link.name}</a></li>
          {/each}
        </ul>

        <div class="actions">
          <div class="desktop desktop-actions">
            <div class:invisible={!isFull}>
              <Button
                category="third"
                href="/accounts/login"
                style="display: {page.url.pathname === '/accounts/login'
                  ? 'none'
                  : 'block'}">Log in</Button
              >
            </div>

            <Button
              Icon={Lightbulb}
              large
              href="/accounts/create"
              style="width: 100%; display: {page.url.pathname ===
              '/accounts/create'
                ? 'none'
                : 'block'}">Get started</Button
            >
          </div>

          <div class="mobile hamburger">
            <Hamburger bind:open={isMenuOpen} />
          </div>
        </div>
      </nav>
    </View>

    <menu class:open={isMenuOpen} transition:slide>
      <ul class="mobile-links">
        {#each links as link}
          <li><a href={link.url}>{link.name}</a></li>
        {/each}
      </ul>

      <div class="desktop-actions">
        <Button
          Icon={Lightbulb}
          large
          style="width: 100%;"
          href="/accounts/create">Get started</Button
        >
        <div class:invisible={!isFull}>
          <Button category="third" href="accounts/login">Log in</Button>
        </div>
      </div>
    </menu>
  </div>
</main>

{@render children?.()}

<style>
  * {
    transition: opacity var(--transition-duration) ease-in-out;
  }

  .mobile-links {
    list-style: none;
    padding: 0;
  }

  .mobile-links > li {
    width: fit-content;
    font-size: 1.7rem;
    font-weight: 200;
    font-family: var(--font-secondary);
  }

  /* MENU */
  menu {
    height: 0;
    opacity: 0;
    overflow: hidden;
    padding: 0 2rem;
    transition: all var(--transition-duration) var(--transition-easing);

    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  menu.open {
    height: auto;
    opacity: 1;
    padding: 1rem 2rem;
    padding-top: 0.5rem;
  }
  /* END-MENU */

  /* LOGO */
  .logo {
    height: 60px;

    display: flex;
    align-items: center;
    gap: 1rem;
  }

  .logo * {
    transition: all var(--transition-duration) var(--transition-easing);
  }

  .logo:hover .title {
    transform: scale(110%) translateX(10px);
  }

  .logo:hover .icon {
    transform: rotate(45deg) !important;
  }

  .logo .icon {
    height: 100%;
  }

  .logo .icon :global(*) {
    cursor: pointer;
  }
  /* END-LOGO */

  .desktop-actions {
    display: flex;
    flex-direction: row;
    gap: 1rem;

    align-items: center;
  }

  .title {
    font-size: 2.3rem;
    font-family: var(--font-brand);
    font-weight: 500;
    color: var(--color-primary-dark);
  }

  .items {
    display: flex;
    flex-direction: row;
    gap: 2rem;

    list-style: none;
  }

  .items li {
    font-size: 1.2rem;
  }

  /* MAIN PARENT */
  main {
    position: sticky;
    top: 0;
    left: 0;
    width: 100%;
    min-height: var(--nav-height);

    z-index: 1000;
    margin: 0;

    transition: all 0.4s ease-in-out;
  }

  /* NAVIGATION PARENT CONTAINER */
  .container {
    position: relative;
    background-color: rgba(255, 255, 255, 0);
  }

  .container.full {
    background-color: rgba(255, 255, 255, 0.51);
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.17);
    backdrop-filter: blur(15px);
  }
  /* END- NAVIGATION PARENT CONTAINER */

  /* NAVIGATION-BAR */
  nav {
    /* padding: 1rem 2rem; */
    padding: 1rem 0;

    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;

    width: 100%;
  }
  /* END-NAVIGATION-BAR */

  .mobile {
    display: none;
  }

  @media screen and (max-width: 1050px) {
    .mobile {
      display: block;
    }

    .desktop {
      display: none;
    }

    nav {
      padding: 1rem 0.5rem 1rem 0;
    }
  }
</style>
