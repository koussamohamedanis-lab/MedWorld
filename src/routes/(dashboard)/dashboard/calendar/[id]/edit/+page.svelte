<script lang="ts">
  import type { Calendar } from "$lib/types/calendar";
  import { apiClient } from "$lib/api/client";
  import { page } from "$app/stores";
  import CalendarEditContent from "$lib/components/calendar/CalendarEditContent.svelte";
  import View from "$lib/components/ui/View.svelte";

  let calendar: Calendar | null = $state(null);
  let loading = $state(true);
  let error = $state<string | null>(null);
  let saving = $state(false);
  let saveSuccess = $state(false);

  // Form state
  let editedAvailability = $state<
    Array<{
      date: string;
      slots: string[];
    }>
  >([]);

  function buildDefaultWeekAvailability(): Array<{ date: string; slots: string[] }> {
    const today = new Date();
    const pad = (n: number) => String(n).padStart(2, "0");
    const toYmd = (d: Date) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
    const defaultSlots = ["09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00"];
    return Array.from({ length: 7 }, (_, i) => {
      const d = new Date(today);
      d.setDate(today.getDate() + i);
      return {
        date: toYmd(d),
        slots: [...defaultSlots],
      };
    });
  }

  async function loadCalendar() {
    try {
      const id = $page.params.id;
      calendar = await apiClient.get<Calendar>(`/calendars/${id}`);
      const availability = (calendar as any)?.availability || [];
      editedAvailability = JSON.parse(JSON.stringify(availability));
      if ((editedAvailability || []).length === 0) {
        editedAvailability = buildDefaultWeekAvailability();
      }
    } catch (err) {
      error = err instanceof Error ? err.message : "An error occurred";
      console.error("Calendar load error:", err);
    } finally {
      loading = false;
    }
  }

  async function saveCalendar() {
    saving = true;
    error = null;
    saveSuccess = false;

    try {
      const id = $page.params.id;
      await apiClient.put(`/calendars/${id}`, {
        availability: editedAvailability,
      });

      saveSuccess = true;
      setTimeout(() => {
        saveSuccess = false;
      }, 3000);
    } catch (err) {
      error = err instanceof Error ? err.message : "An error occurred";
      console.error("Calendar save error:", err);
    } finally {
      saving = false;
    }
  }

  function addSlot(dateIndex: number) {
    editedAvailability[dateIndex].slots.push("");
    editedAvailability = editedAvailability;
  }

  function addDate() {
    const next = buildDefaultWeekAvailability()[0];
    editedAvailability = [...editedAvailability, { date: next.date, slots: [] }];
  }

  function removeDate(dateIndex: number) {
    editedAvailability.splice(dateIndex, 1);
    editedAvailability = editedAvailability;
  }

  function updateDate(dateIndex: number, value: string) {
    editedAvailability[dateIndex].date = value;
    editedAvailability = editedAvailability;
  }

  function removeSlot(dateIndex: number, slotIndex: number) {
    editedAvailability[dateIndex].slots.splice(slotIndex, 1);
    editedAvailability = editedAvailability;
  }

  function updateSlot(dateIndex: number, slotIndex: number, value: string) {
    editedAvailability[dateIndex].slots[slotIndex] = value;
    editedAvailability = editedAvailability;
  }

  $effect(() => {
    loadCalendar();
  });
</script>

<View>
  <CalendarEditContent
    {calendar}
    {loading}
    {error}
    {editedAvailability}
    {saving}
    {saveSuccess}
    onAddDate={addDate}
    onRemoveDate={removeDate}
    onUpdateDate={updateDate}
    onAddSlot={addSlot}
    onRemoveSlot={removeSlot}
    onUpdateSlot={updateSlot}
    onSave={saveCalendar}
  />
</View>
