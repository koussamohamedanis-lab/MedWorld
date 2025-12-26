import { sveltekit } from "@sveltejs/kit/vite";
import { defineConfig } from "vite";


export default defineConfig({
  plugins: [sveltekit()],
  optimizeDeps: {
    include: ["@lucide/svelte"],
  },
  server: {
    allowedHosts: ["localhost", ".ngrok-free.app", "*.trycloudflare.com", "sounds-offers-assess-combination.trycloudflare.com"],
  }
});
