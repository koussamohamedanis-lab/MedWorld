<script lang="ts">
  import { CircleQuestionMark } from "@lucide/svelte";

  let {
    Icon = CircleQuestionMark,
    title = "",
    checked = $bindable(false),
  }: {
    checked: boolean;
    Icon: any;
    title: string;
  } = $props();
</script>

<label class="feature-label" class:checked>
  <input type="checkbox" bind:checked />
  <span class="feature-icon"><Icon /></span>
  <span class="feature-text">{title}</span>
</label>

<style>
  .feature-label {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: white;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: all var(--transition-duration) var(--transition-easing);
  }

  .feature-label:hover {
    transform: translateY(-2px);
  }

  .feature-label.checked {
    color: white;
    background-color: var(--color-primary);
  }

  .feature-icon {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 1.5rem;
    height: 1.5rem;
    flex-shrink: 0;
  }

  .feature-text {
    font-size: 0.875rem;
    color: var(--text-color);
  }

  input[type="checkbox"] {
    width: 1.125rem;
    height: 1.125rem;
    border-radius: 0.25rem;
    border: 2px solid var(--color-primary);
    appearance: none;
    cursor: pointer;
    position: relative;
    transition: all 0.2s ease;
    flex-shrink: 0;
  }

  input[type="checkbox"]:checked {
    background-color: var(--color-primary);
    border-color: var(--color-primary);
  }

  input[type="checkbox"]:checked::after {
    content: "✓";
    color: white;
    font-size: 0.75rem;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
  }

  input[type="checkbox"]:focus {
    outline: none;
    box-shadow: 0 0 0 2px var(--color-primary-alpha);
  }
</style>
