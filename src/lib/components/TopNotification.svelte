<script lang="ts">
  import { X } from "@lucide/svelte";
  import View from "./ui/View.svelte";

  let {
    title,
    color,
    hidden = $bindable(false),
  }: {
    title: string;
    color?: string;
    hidden?: boolean;
  } = $props();
</script>

<div
  class="notification"
  class:hidden
  style={`${color ? `background-color: ${color};` : ""}`}
>
  <View align="row" style="width: 100%; justify-content: space-between;">
    <div></div>

    <p class="message">
      {title}
    </p>

    <button class="close" onclick={() => (hidden = true)}>
      <X size="1.2rem" />
    </button>
  </View>
</div>

<style>
  .close {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;

    padding: 0.2rem;
  }

  .notification {
    color: white;
    text-align: center;
    background-color: var(--color-primary-darker);
    box-shadow: inset 0 -4px 12px rgba(var(--shadow-color-rgb), 0.1);

    width: 100%;
    height: var(--notification-height);

    display: flex;
    align-items: center;
    justify-content: space-between;

    overflow: hidden;
    transition: all var(--transition-duration) var(--transition-easing);
  }

  .message {
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: center;

    padding: 0.5rem 1rem;
    font-size: 0.8rem;
  }

  .hidden {
    height: 0;
  }

  @media screen and (max-width: 768px) {
    .message {
      padding: 0.2rem 0.5rem;
      font-size: 8px;
    }
  }
</style>
