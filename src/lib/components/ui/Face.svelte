<script lang="ts">
  import { MetaTags } from "svelte-meta-tags";

  //@ts-ignore
  import AOS from "aos";
  import { SITE_DESCRIPTION, SITE_NAME } from "$lib";

  let {
    children,

    title,
    description,
    image = "",
  }: {
    children: any;
    title: string;
    description: string;
    image?: string;
  } = $props();

  $effect(() => {
    AOS.init({
      disable: "mobile",
      once: true,
      duration: 500,
    });
  });
</script>

<MetaTags
  title={title + " | " + SITE_NAME}
  description={description || SITE_DESCRIPTION}
  additionalMetaTags={[
    { property: "og:image", content: image },
    { name: "twitter:image", content: image },
  ]}
/>

{@render children?.()}
