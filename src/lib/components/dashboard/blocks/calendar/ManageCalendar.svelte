<script lang="ts">
  import Block from "$lib/components/ui/Block.svelte";
  import type { Permission } from "$lib/types/permission";
  import type { Assistant } from "$lib/types/users/assistant";
  import { onMount } from "svelte";
  import type { Calendar } from "$lib/types/calendar";
  import IconButton from "$lib/components/ui/IconButton.svelte";
  import { CalendarIcon, Eye, Pen } from "@lucide/svelte";
  import { CalendarAPI, CabinetAPI } from "$lib/api/index";
  import Button from "$lib/components/ui/Button.svelte";
  import type { Doctor } from "$lib/types/users/doctor";
  import type { Admin } from "$lib/types/users/admin";
  import { Users } from "$lib/types/users";
  import type { Cabinet } from "$lib/types/cabinet";

  let {
    user,
    permissions,
    cabinet,
  }: {
    user: Doctor | Admin | Assistant;
    permissions: Permission[];
    cabinet: Cabinet;
  } = $props();

  let calendars: Calendar[] = $state([]);
  let filteredCalendars = $derived(
    // calendars.filter((cal) => cal.cabinet.id === cabinet.id),
    calendars,
  );

  const effectiveCabinet = $derived(() => {
    if (cabinet) return cabinet;

    // Fallback: derive cabinet from user when dashboard didn't provide currentCabinet yet.
    if ((user as any)?.cabinet) return (user as any).cabinet as Cabinet;
    if (user.type === Users.Assistant) {
      const assistant = user as Assistant;
      return (assistant.doctor as any)?.cabinet as Cabinet;
    }

    return null;
  });

  async function loadCalendars() {
    try {
      if (!effectiveCabinet()?.id) {
        calendars = [];
        return;
      }

      const data = await CalendarAPI.list({ cabinetId: effectiveCabinet()!.id });
      calendars = (data as any) || [];
    } catch (e) {
      calendars = [];
    }
  }

  async function createCalendarForFirstDoctor() {
    try {
      if (!effectiveCabinet()?.id) {
        alert("Please select a cabinet first.");
        return;
      }

      const doctors = await CabinetAPI.getDoctors(effectiveCabinet()!.id);
      const first = (doctors as any[]).find((d) => d.doctorId);
      if (!first?.doctorId) {
        alert("No doctors found to create a calendar for.");
        return;
      }
      await CalendarAPI.create({ doctorId: first.doctorId, availability: [] });
      await loadCalendars();
    } catch (e: any) {
      alert(e?.message || "Failed to create calendar");
    }
  }

  onMount(() => {
    loadCalendars();
  });
</script>

<Block group="calendar" title={`Calendar`} Icon={CalendarIcon}>
  {#if effectiveCabinet()?.id && filteredCalendars.length === 0 && permissions.includes("edit_calendar")}
    <Button category="primary" label="Create Calendar" onClick={createCalendarForFirstDoctor} />
  {/if}

  <table>
    <thead>
      <tr>
        <th>Day</th>
        <th>Available Slots</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      {#each filteredCalendars as calendar}
        {#if (calendar.availability || []).length === 0}
          <tr>
            <td colspan="2">No availability configured yet.</td>
            <td>
              <div class="actions">
                {#if permissions.includes("view_calendar")}
                  <IconButton Icon={Eye} label="View Calendar" href={`/dashboard/calendar/${calendar.id}`} />
                {/if}

                {#if permissions.includes("edit_calendar")}
                  <IconButton
                    Icon={Pen}
                    color="orange"
                    label="Edit Calendar"
                    href={`/dashboard/calendar/${calendar.id}/edit`}
                  />
                {/if}
              </div>
            </td>
          </tr>
        {:else}
          {#each calendar.availability as availability}
            <tr>
              <td>{availability.date}</td>

              <td>
                <div class="slots">
                  {#each availability.slots as slot}
                    <p>
                      {slot}
                    </p>
                  {/each}
                </div>
              </td>

              <td>
                <div class="actions">
                  {#if permissions.includes("view_calendar")}
                    <IconButton Icon={Eye} label="View Calendar" href={`/dashboard/calendar/${calendar.id}`} />
                  {/if}

                  {#if permissions.includes("edit_calendar")}
                    <IconButton
                      Icon={Pen}
                      color="orange"
                      label="Edit Calendar"
                      href={`/dashboard/calendar/${calendar.id}/edit`}
                    />
                  {/if}
                </div>
              </td>
            </tr>
          {/each}
        {/if}
      {/each}
    </tbody>
  </table>

  {#if !effectiveCabinet()?.id}
    <p class="no-calendars">Select a cabinet to manage calendars.</p>
  {:else if filteredCalendars.length === 0}
    <p class="no-calendars">No calendars found for this cabinet.</p>
  {/if}
</Block>

<style>
  .no-calendars {
    text-align: center;
    padding: 2rem;
    color: #718096;
    font-size: 1.1rem;
  }

  .slots {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
  }

  .slots p {
    background-color: var(--color-primary);
    padding: 0.5rem 1rem;
    width: fit-content;
    color: white;
    border-radius: 1rem;
  }

  table .actions {
    display: flex;
    gap: 0.5rem;
  }

  tr:hover {
    background-color: #f8fafc;
  }
</style>
