<script lang="ts">
  let {
    children,
  }: {
    children: any;
  } = $props();
</script>

<main>
  {@render children?.()}
</main>

<style>
  main {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 16px;
  }
</style>
