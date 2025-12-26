<script lang="ts">
  import type { HTMLAttributes } from "svelte/elements";

  interface Props extends HTMLAttributes<HTMLDivElement> {
    children?: import("svelte").Snippet;
    align?: "row" | "column" | "default";
    center?: boolean;
    screen?: boolean;
    main?: boolean;
  }

  let {
    children,
    align = "column",
    center = false,
    screen = false,
    main = false,
    ...rest
  }: Props = $props();
</script>

<div
  class="view"
  class:center
  class:screen
  class:main
  data-align={align}
  {...rest}
>
  {@render children?.()}
</div>

<style>
  .view {
    width: 100%;
    max-width: var(--container-max);
    margin: 0 auto;
    padding: 0 24px;
    min-height: calc(100vh - var(--header-height) - var(--footer-height));
    display: flex;
    flex-direction: column;

    transition: height var(--transition-duration) var(--transition-easing);
  }

  .view.main {
    padding: 2rem 0;
  }

  @media (max-width: 768px) {
    .view {
      padding: 0 20px;
    }
  }

  div[data-align] {
    display: flex;
  }

  div[data-align="row"] {
    flex-direction: row;
  }

  div[data-align="column"] {
    flex-direction: column;
  }
  .center {
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .screen {
    min-height: calc(100vh - var(--nav-height));
  }
</style>
