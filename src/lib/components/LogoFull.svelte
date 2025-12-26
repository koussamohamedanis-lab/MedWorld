<script>
  import { SITE_NAME } from "$lib";
  import Logo from "./Logo.svelte";
</script>

<a class="logo" href="/">
  <div class="icon">
    <Logo rotate />
  </div>
  <h1 class="title">
    {SITE_NAME}
  </h1>
</a>

<style>
  .logo {
    height: 60px;

    display: flex;
    align-items: center;
    gap: 1rem;

    width: fit-content;
  }

  .logo * {
    font-family: var(--font-primary);
    color: var(--color-primary-dark);
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
</style>
