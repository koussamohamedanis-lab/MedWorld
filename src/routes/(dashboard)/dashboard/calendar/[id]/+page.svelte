<script lang="ts">
  import type { Calendar } from "$lib/types/calendar";
  import { apiClient } from "$lib/api/client";
  import { page } from "$app/stores";
  import CalendarViewContent from "$lib/components/calendar/CalendarViewContent.svelte";
  import View from "$lib/components/ui/View.svelte";

  let calendar: Calendar | null = $state(null);
  let loading = $state(true);
  let error = $state<string | null>(null);

  async function loadCalendar() {
    try {
      const id = $page.params.id;
      calendar = await apiClient.get<Calendar>(`/calendars/${id}`);
    } catch (err) {
      error = err instanceof Error ? err.message : "An error occurred";
      console.error("Calendar load error:", err);
    } finally {
      loading = false;
    }
  }

  $effect(() => {
    loadCalendar();
  });
</script>

<View>
  <CalendarViewContent {calendar} {loading} {error} />
</View>
