<script lang="ts">
  import { fly, scale, slide } from "svelte/transition";
  import { portal } from "svelte-portal";

  export let isOpen: boolean = false;
  export let onClose: () => void;
  export let title: string = "";

  $: if (isOpen) {
    document.body.style.overflow = "hidden";
  } else {
    document.body.style.overflow = "unset";
  }

  function handleBackdropClick(event: MouseEvent) {
    if (event.target === event.currentTarget) {
      onClose();
    }
  }
</script>

{#if isOpen}
  <div class="modal-overlay" on:click={handleBackdropClick} use:portal={"body"}>
    <div class="modal-content" transition:slide>
      <div class="modal-header">
        <h2>{title}</h2>
        <button class="close-button" on:click={onClose}>×</button>
      </div>
      <div class="modal-body">
        <slot />
      </div>
    </div>
  </div>
{/if}

<style>
  .modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 1rem;
  }

  .modal-content {
    background: white;
    border-radius: 8px;
    padding: 0;
    width: 100%;
    max-width: 600px;
    max-height: 90vh;
    overflow: auto;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
  }

  .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
  }

  .modal-header h2 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #111827;
  }

  .close-button {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    color: #6b7280;
  }

  .close-button:hover {
    background: #f3f4f6;
    color: #374151;
  }

  .modal-body {
    padding: 1.5rem;
  }
</style>
