<script lang="ts">
  import type { Calendar } from "$lib/types/calendar";
  import Button from "$lib/components/ui/Button.svelte";

  let {
    calendar,
    loading,
    error,
    editedAvailability,
    saving,
    saveSuccess,
    onAddDate,
    onRemoveDate,
    onUpdateDate,
    onAddSlot,
    onRemoveSlot,
    onUpdateSlot,
    onSave,
  } = $props<{
    calendar: Calendar | null;
    loading: boolean;
    error: string | null;
    editedAvailability: Array<{ date: string; slots: string[] }>;
    saving: boolean;
    saveSuccess: boolean;
    onAddDate: () => void;
    onRemoveDate: (dateIndex: number) => void;
    onUpdateDate: (dateIndex: number, value: string) => void;
    onAddSlot: (dateIndex: number) => void;
    onRemoveSlot: (dateIndex: number, slotIndex: number) => void;
    onUpdateSlot: (dateIndex: number, slotIndex: number, value: string) => void;
    onSave: () => void;
  }>();
</script>

{#if loading}
  <p>Loading calendar...</p>
{:else if error}
  <p class="error">Error: {error}</p>
{:else if calendar}
  <div class="calendar-edit">
    {#if saveSuccess}
      <div class="success-message">Calendar saved successfully!</div>
    {/if}

    <div class="availability-section">
      <h4>Edit Available Slots</h4>

      <div class="availability-toolbar">
        <button type="button" class="add-date-btn" onclick={onAddDate}>+ Add Date</button>
      </div>

      {#if editedAvailability.length === 0}
        <p class="empty">No dates yet. Click “Add Date”.</p>
      {/if}

      {#each editedAvailability as availability, dateIndex}
        <div class="date-group">
          <div class="date-header">
            <div class="date-header-row">
              <label class="date-label">
                Date:
                <input
                  class="date-input"
                  type="date"
                  value={availability.date}
                  onchange={(e) => onUpdateDate(dateIndex, e.currentTarget.value)}
                />
              </label>
              <button
                type="button"
                class="remove-date-btn"
                onclick={() => onRemoveDate(dateIndex)}
              >
                Remove Date
              </button>
            </div>
          </div>
          <div class="slots-container">
            {#each availability.slots as slot, slotIndex}
              <div class="slot-input-group">
                <input
                  type="time"
                  value={slot}
                  onchange={(e) =>
                    onUpdateSlot(dateIndex, slotIndex, e.currentTarget.value)}
                />
                <button
                  type="button"
                  class="remove-btn"
                  onclick={() => onRemoveSlot(dateIndex, slotIndex)}
                >
                  Remove
                </button>
              </div>
            {/each}
            <button
              type="button"
              class="add-btn"
              onclick={() => onAddSlot(dateIndex)}
            >
              + Add Time Slot
            </button>
          </div>
        </div>
      {/each}
    </div>

    <div class="actions">
      <Button onClick={onSave} disabled={saving} category="primary">
        {saving ? "Saving..." : "Save Calendar"}
      </Button>
    </div>
  </div>
{:else}
  <p>No calendar found</p>
{/if}

<style>
  .calendar-edit {
    padding: 1rem;
  }

  .availability-toolbar {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 0.75rem;
  }

  .add-date-btn {
    padding: 0.5rem 1rem;
    background-color: var(--color-primary);
    color: white;
    border: none;
    border-radius: 0.25rem;
    cursor: pointer;
    font-size: 0.875rem;
  }

  .add-date-btn:hover {
    filter: brightness(0.95);
  }

  .empty {
    color: #475569;
    padding: 0.75rem 0;
  }

  .info-section {
    margin-bottom: 2rem;
    padding: 1rem;
    background-color: #f8fafc;
    border-radius: 0.5rem;
  }

  .info-section h3 {
    margin: 0.5rem 0;
    color: #1e293b;
  }

  .availability-section {
    margin: 2rem 0;
  }

  .availability-section h4 {
    margin-bottom: 1rem;
    color: #1e293b;
  }

  .date-group {
    margin-bottom: 1.5rem;
    padding: 1rem;
    background-color: #f8fafc;
    border-radius: 0.5rem;
    border-left: 4px solid var(--color-primary);
  }

  .date-header {
    margin-bottom: 1rem;
  }

  .date-header-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
  }

  .date-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: #1e293b;
  }

  .date-input {
    padding: 0.4rem 0.5rem;
    border: 1px solid #cbd5e1;
    border-radius: 0.25rem;
    font-size: 0.95rem;
  }

  .remove-date-btn {
    padding: 0.5rem 1rem;
    background-color: #ef4444;
    color: white;
    border: none;
    border-radius: 0.25rem;
    cursor: pointer;
    font-size: 0.875rem;
  }

  .remove-date-btn:hover {
    background-color: #dc2626;
  }

  .date-header label {
    font-weight: 600;
    color: #1e293b;
  }

  .slots-container {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }

  .slot-input-group {
    display: flex;
    gap: 0.5rem;
    align-items: center;
  }

  input[type="time"] {
    flex: 1;
    padding: 0.5rem;
    border: 1px solid #cbd5e1;
    border-radius: 0.25rem;
    font-size: 1rem;
  }

  .remove-btn {
    padding: 0.5rem 1rem;
    background-color: #ef4444;
    color: white;
    border: none;
    border-radius: 0.25rem;
    cursor: pointer;
    font-size: 0.875rem;
  }

  .remove-btn:hover {
    background-color: #dc2626;
  }

  .add-btn {
    padding: 0.5rem 1rem;
    background-color: #10b981;
    color: white;
    border: none;
    border-radius: 0.25rem;
    cursor: pointer;
    font-size: 0.875rem;
    align-self: flex-start;
  }

  .add-btn:hover {
    background-color: #059669;
  }

  .actions {
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #e2e8f0;
    display: flex;
    gap: 1rem;
  }

  .error {
    color: #dc2626;
    padding: 1rem;
    background-color: #fee2e2;
    border-radius: 0.5rem;
  }

  .success-message {
    color: #059669;
    padding: 1rem;
    background-color: #d1fae5;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
  }
</style>
