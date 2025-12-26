<script lang="ts">
  import type { Calendar } from "$lib/types/calendar";

  let {
    calendar,
    loading,
    error,
  }: { calendar: Calendar | null; loading: boolean; error: string | null } =
    $props();
</script>

{#if loading}
  <p>Loading calendar...</p>
{:else if error}
  <p class="error">Error: {error}</p>
{:else if calendar}
  <div class="calendar-view">
    <div class="info-section">
      <h3>
        Calendar: {(calendar.doctor as any).user?.firstName}
        {(calendar.doctor as any).user?.lastName}
      </h3>
    </div>

    <div class="availability-section">
      <h4>Available Slots</h4>
      <table>
        <thead>
          <tr>
            <th>Date</th>
            <th>Time Slots</th>
          </tr>
        </thead>

        <tbody>
          {#each calendar.availability as availability}
            <tr>
              <td>{new Date(availability.date).toDateString()}</td>
              <td>
                <div class="slots">
                  {#each availability.slots as slot}
                    <span class="slot">{slot}</span>
                  {/each}
                </div>
              </td>
            </tr>
          {/each}
        </tbody>
      </table>
    </div>
  </div>
{:else}
  <p>No calendar found</p>
{/if}

<style>
  .calendar-view {
    padding: 1rem;
  }

  .info-section {
    margin-bottom: 2rem;
    padding: 1rem;
    background-color: #f8fafc;
    border-radius: 0.5rem;
  }

  .info-section h3 {
    color: #1e293b;
  }

  .availability-section {
    margin-top: 2rem;
  }

  .availability-section h4 {
    margin-bottom: 1rem;
    color: #1e293b;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
  }

  th {
    text-align: left;
    padding: 0.75rem;
    border-bottom: 2px solid #e2e8f0;
    font-weight: 600;
    color: #334155;
  }

  td {
    padding: 0.75rem;
    border-bottom: 1px solid #e2e8f0;
  }

  tr:hover {
    background-color: #f8fafc;
  }

  .slots {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
  }

  .slot {
    display: inline-block;
    background-color: var(--color-primary);
    color: white;
    padding: 0.5rem 0.75rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
  }

  .error {
    color: #dc2626;
    padding: 1rem;
    background-color: #fee2e2;
    border-radius: 0.5rem;
  }
</style>
