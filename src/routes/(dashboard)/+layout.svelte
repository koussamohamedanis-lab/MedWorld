<script lang="ts">
  import "../../app.css";

  // AOS Library
  import AOS from "aos";
  import "aos/dist/aos.css";

  import { onMount } from "svelte";
  import {
    ArrowRight,
    Crown,
    Hand,
    LogOut,
    MinusIcon,
    User,
  } from "@lucide/svelte";
  import { links, SITE_DESCRIPTION, SITE_NAME } from "$lib";

  import Logo from "$lib/components/Logo.svelte";
  import logo from "$lib/assets/logo.svg";
  import View from "$lib/components/ui/View.svelte";
  import Button from "$lib/components/ui/Button.svelte";
  import TopNotification from "$lib/components/TopNotification.svelte";
  import { scale } from "svelte/transition";
  import { user, loadUser } from "$lib/stores";
  import Avatar from "$lib/components/ui/Avatar.svelte";

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

  let profileOpen = $state(false);
  let dropdown: any = $state();
  let userCard: any;

  onMount(() => {
    scrollHandler();

    AOS.init();

    window.addEventListener("click", (e) => {
      if (
        dropdown &&
        !dropdown.contains(e.target) &&
        !userCard.contains(e.target)
      ) {
        profileOpen = false;
      }
    });

    // Load user from authentication
    loadUser();
  });
</script>

<svelte:head>
  <link rel="icon" href={logo} />
  <title
    >{SITE_NAME} - {$user
      ? `${$user.firstName} ${$user.lastName}`
      : "Dashboard"}</title
  >
</svelte:head>

<main class:full={isMenuOpen}>
  <div class="container" class:full={isFull}>
    <TopNotification
      title={"Offering the most modern cabinet management technology."}
    />

    <View>
      <nav>
        <a class="logo" href="/dashboard">
          <div class="icon">
            <Logo rotate />
          </div>

          <h1 class:invisible={!isFull} class:none={!isFull} class="title">
            {SITE_NAME}
          </h1>
        </a>

        <div class="actions">
          <button
            class="profile"
            bind:this={userCard}
            class:clicked={profileOpen}
            onclick={() => (profileOpen = !profileOpen)}
          >
            <span
              style="transform: rotate({profileOpen ? '-270deg' : '-180deg'})"
            >
              <ArrowRight />
            </span>

            <h3>
              {$user ? `${$user.firstName} ${$user.lastName}` : "Loading..."}
            </h3>

            <div class="avatar">
              <Avatar
                alt={$user ? `${$user.firstName} ${$user.lastName}` : "User"}
                avatarUrl={$user?.avatarUrl}
                size="48px"
                original
              />
            </div>
          </button>

          {#if profileOpen}
            <div class="dropdown" transition:scale bind:this={dropdown}>
              <Button
                category="secondary-gold"
                Icon={Crown}
                href="/dashboard/plans">Subscriptions</Button
              >

              <Button
                category="secondary"
                Icon={User}
                href="/dashboard/users/{$user?.id}">My Profile</Button
              >

              <Button category="error" Icon={LogOut} href="/accounts/logout"
                >Logout</Button
              >
            </div>
          {/if}
        </div>
      </nav>
    </View>
  </div>
</main>

{@render children?.()}

<style>
  * {
    transition: opacity var(--transition-duration) ease-in-out;
  }

  .actions {
    position: relative;
  }

  .dropdown {
    position: absolute;
    top: calc(100% + 20px);
    right: 0;
    min-height: 100px;
    width: 100%;
    background-color: rgba(240, 240, 240, 0.932);
    backdrop-filter: blur(20px);
    border-radius: 5px;
    overflow: hidden;
    box-shadow: 0px 10px 15px -3px rgba(0, 0, 0, 0.1);
    border: 1px solid rgb(221, 221, 221);

    display: flex;
    flex-direction: column;
    justify-content: space-around;
    align-items: center;

    padding: 15px;
  }

  :global(.dropdown *:not(:last-child)) {
    margin-bottom: 10px;
  }

  :global(.dropdown > *) {
    width: 100% !important;
    display: flex;
    justify-content: center;
    align-items: center;
  }

  :global(.dropdown * svg) {
    width: fit-content;
  }

  .profile,
  .profile * {
    transition: all 0.2s ease-in-out;
  }

  .profile {
    display: flex;
    justify-content: space-between;
    align-items: center;

    gap: 1rem;
    cursor: pointer;

    border-radius: 20px;
    background-color: var(--background);
    color: var(--text);

    border: 1px solid rgb(221, 221, 221);
    width: 100%;

    padding: 10px 10px 10px 15px;
  }

  .profile h3 {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 300;
  }

  .profile:hover,
  .clicked {
    background-color: rgb(243, 243, 243);
    transform: scale(105%);
  }

  .profile:hover .avatar {
    transform: rotateY(360deg);
  }

  .profile .avatar {
    transition: all 0.7s ease;
  }

  .profile:active {
    background-color: rgb(216, 216, 216);
    transform: scale(95%);
  }

  /* .clicked {
		background-color: rgb(216, 216, 216);
	} */

  .clicked .avatar {
    transform: rotateY(360deg) !important;
  }

  /* LOGO */
  .logo * {
    font-family: var(--font-cool);
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

  .title {
    font-size: 2.3rem;
    font-family: var(--font-brand);
    font-weight: 500;
    color: var(--color-primary-dark);
  }

  .logo {
    height: 60px;

    display: flex;
    align-items: center;
    gap: 1rem;
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

  @media screen and (max-width: 1050px) {
    nav {
      padding: 1rem 0.5rem 1rem 0;
    }
  }

  @media screen and (max-width: 768px) {
    .title {
      display: none;
    }

    .profile {
      margin-left: 1rem;
    }
    .profile h3 {
      font-size: 14px;
    }
  }
</style>
