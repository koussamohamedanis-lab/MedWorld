<script lang="ts">
  import Block from "$lib/components/ui/Block.svelte";
  import TopNotification from "$lib/components/TopNotification.svelte";
  import Input from "$lib/components/ui/Input.svelte";
  import type { Cabinet, Location } from "$lib/types/cabinet";
  import { getPermissionsFromUserType } from "$lib/types/permission";
  import type { SuperAdmin } from "$lib/types/users/superadmin";
  import {
    Plus,
    Save,
    MapPin,
    Clock,
    AlertTriangle,
    Stars,
    CreditCard,
    ParkingCircle,
    Wifi,
    AlarmSmoke,
    MessageCircle,
    Accessibility,
  } from "@lucide/svelte";
  import MapSelector from "./MapSelector.svelte";
  import { CabinetAPI } from "$lib/api";
  import { validate, validation, type Fillable } from "$lib/validation";
  import CheckBox from "$lib/components/ui/CheckBox.svelte";
  import CheckBoxGrid from "$lib/components/ui/CheckBoxGrid.svelte";

  interface IProps {
    user: SuperAdmin;
    onClose: () => void;
    onAdd: (cabinet: Cabinet) => void;
  }

  let { user, onClose, onAdd }: IProps = $props();
  console.log("AddCabinet: Component mounted");

  let permissions = getPermissionsFromUserType(user.type);
  let isSaving = $state(false);
  let showSuccessNotification = $state(false);
  let notificationMessage = $state("");
  let locationSelected = $state(false);

  let newCabinet: Partial<Cabinet> = $state({
    name: "",
    phone: "",
    createdAt: new Date(),
    doctors: [],
    assistants: [],
    ratings: [],
    accessHandicap: false,
    hasParking: false,
    hasWifi: false,
    acceptsUrgent: false,
    acceptsInsurance: false,
    hasOnlineConsultation: false,
    acceptsEmergency: false,
    location: {
      address: "",
      latitude: 36.737,
      longitude: 3.0588,
    },
    openingHours: {},
  });

  let workingHours: Record<string, { open: string; close: string }> = $state({
    Monday: { open: "09:00", close: "17:00" },
    Tuesday: { open: "09:00", close: "17:00" },
    Wednesday: { open: "09:00", close: "17:00" },
    Thursday: { open: "09:00", close: "17:00" },
    Friday: { open: "09:00", close: "17:00" },
    Saturday: { open: "09:00", close: "13:00" },
    Sunday: { open: "Closed", close: "Closed" },
  });

  function handleLocationChange(location: Location) {
    newCabinet.location = {
      address: location.address || "Selected location",
      latitude: location.latitude,
      longitude: location.longitude,
    };
    locationSelected = true;
    console.log("Location changed:", newCabinet.location);
  }

  async function handleSave() {
    console.log("===== SAVE CLICKED =====");

    if (!permissions.includes("add_cabinet")) {
      alert("You don't have permission to add a cabinet.");
      return;
    }

    validateField("name");
    validateField("phoneNumber");

    if (data.name.error || data.phoneNumber.error) {
      console.error("BLOCKED: Validation errors");
      return;
    }

    const name = data.name.value;
    const phone = data.phoneNumber.value;

    if (!name?.trim() || !phone?.trim()) {
      console.error("BLOCKED: Missing fields");
      alert("Please fill in the cabinet name and phone number.");
      return;
    }

    if (!locationSelected || !newCabinet.location?.address) {
      alert("Please select a location on the map or search for an address.");
      return;
    }

    isSaving = true;
    try {
      try {
        const cabinet = await CabinetAPI.create({
          name,
          phone,
          image: data.image.value,
          location: newCabinet.location,
          openingHours: workingHours,
          accessHandicap: newCabinet.accessHandicap,
          hasParking: newCabinet.hasParking,
          hasWifi: newCabinet.hasWifi,
          acceptsUrgent: newCabinet.acceptsUrgent,
          acceptsInsurance: newCabinet.acceptsInsurance,
        });

        alert(`Cabinet "${name}" added successfully!`);

        if (onAdd) onAdd(cabinet);
      } catch (err) {
        alert("Cannot create cabinet: " + err);
      }

      setTimeout(() => {
        onClose();
      }, 2000);
    } catch (error) {
      console.error("FATAL ERROR:", error);
      alert("Error saving cabinet: " + String(error));
    } finally {
      isSaving = false;
    }
  }
  let data: Fillable = $state({
    name: {
      value: "",
      error: "",
      validator: validation.name,
    },
    phoneNumber: {
      value: "",
      error: "",
      validator: validation.phoneNumber,
    },
    image: {
      value: "",
      error: "",
    },
  });

  function handleFileChange(e: Event) {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
      if (file.size > 5 * 1024 * 1024) {
        alert("Image size should be less than 5MB");
        return;
      }
      const reader = new FileReader();
      reader.onload = () => {
        data.image.value = reader.result as string;
      };
      reader.readAsDataURL(file);
    }
  }

  function validateField(fieldName: keyof typeof data) {
    const field = data[fieldName];
    const validationError = field.validator
      ? field.validator(String(field.value ?? ""))
      : "";
    field.error = validationError;
  }
</script>

<Block Icon={Plus} group="add_cabinet" title="Add New Cabinet">
  {#if showSuccessNotification}
    <TopNotification
      title={notificationMessage}
      color="#10b981"
      bind:hidden={showSuccessNotification}
    />
  {/if}

  <div class="form">
    <div class="form-section">
      <div class="section-header">
        <Plus size={20} />
        <h3>General Information</h3>
      </div>
      <div class="grid-2">
        <div class="image-upload-container">
          <label for="cabinet-image" class="image-label">
            Cabinet Picture (Optional)
          </label>
          <div class="image-preview-wrapper">
            {#if data.image.value}
              <img
                src={data.image.value as string}
                alt="Cabinet preview"
                class="image-preview"
              />
              <button
                type="button"
                class="remove-image"
                onclick={() => (data.image.value = "")}
                title="Remove image"
              >
                &times;
              </button>
            {:else}
              <div class="image-placeholder">
                <Plus size={32} />
                <span>Upload Picture</span>
              </div>
            {/if}
            <input
              id="cabinet-image"
              type="file"
              accept="image/*"
              class="image-input"
              onchange={handleFileChange}
            />
          </div>
        </div>

        <div class="inputs-column">
          <Input
            label="Cabinet Name"
            placeholder="e.g. Central Medical Clinic"
            bind:value={data.name.value}
            bind:error={data.name.error}
            validation={data.name.validator}
            theme="secondary"
            showLabel={true}
            required={true}
          />

          <Input
            label="Phone Number"
            placeholder="e.g. +213 555 123 456"
            bind:value={data.phoneNumber.value}
            bind:error={data.phoneNumber.error}
            validation={data.phoneNumber.validator}
            theme="secondary"
            showLabel={true}
            required={true}
            type="tel"
          />
        </div>
      </div>
    </div>

    <div class="form-section">
      <div class="section-header" style="padding-bottom: 0;">
        <MapPin size={20} />
        <h3>Cabinet Location</h3>
      </div>
      <div class="map-wrapper">
        <MapSelector
          location={newCabinet.location || {
            address: "",
            latitude: 36.737,
            longitude: 3.0588,
          }}
          onChange={handleLocationChange}
        />
      </div>
    </div>

    <div class="form-section">
      <div class="section-header">
        <Clock size={20} />
        <h3>Opening Hours</h3>
      </div>
      <div class="hours-table-container">
        <table class="hours-table">
          <thead>
            <tr>
              <th>Day</th>
              <th>Status</th>
              <th>Opening Time</th>
              <th>Closing Time</th>
            </tr>
          </thead>
          <tbody>
            {#each Object.entries(workingHours) as [day, hours]}
              <tr class:closed={hours.open === "Closed"}>
                <td class="day-cell">{day}</td>
                <td class="status-cell">
                  <button
                    type="button"
                    class="status-toggle"
                    class:is-closed={hours.open === "Closed"}
                    onclick={() => {
                      if (hours.open === "Closed") {
                        hours.open = "09:00";
                        hours.close = "17:00";
                      } else {
                        hours.open = "Closed";
                        hours.close = "Closed";
                      }
                    }}
                  >
                    {hours.open === "Closed" ? "Closed" : "Open"}
                  </button>
                </td>
                <td class="time-cell">
                  {#if hours.open !== "Closed"}
                    <Input
                      type="time"
                      bind:value={hours.open}
                      theme="secondary"
                    />
                  {:else}
                    <span class="closed-label">-</span>
                  {/if}
                </td>
                <td class="time-cell">
                  {#if hours.close !== "Closed"}
                    <Input
                      type="time"
                      bind:value={hours.close}
                      theme="secondary"
                    />
                  {:else}
                    <span class="closed-label">-</span>
                  {/if}
                </td>
              </tr>
            {/each}
          </tbody>
        </table>
      </div>
    </div>

    <div class="form-section">
      <div class="section-header">
        <Stars size={20} />
        <h3>Additional Details</h3>
      </div>

      <div>
        <CheckBoxGrid>
          <CheckBox
            title="Has Wifi"
            Icon={Wifi}
            bind:checked={newCabinet.hasWifi as boolean}
          />

          <CheckBox
            title="Has Parking"
            Icon={ParkingCircle}
            bind:checked={newCabinet.hasParking as boolean}
          />

          <CheckBox
            title="Accepts Urgent"
            Icon={AlarmSmoke}
            bind:checked={newCabinet.acceptsUrgent as boolean}
          />

          <CheckBox
            title="Has Handicap Access"
            Icon={Accessibility}
            bind:checked={newCabinet.accessHandicap as boolean}
          />

          <CheckBox
            title="Accepts Insurance"
            Icon={CreditCard}
            bind:checked={newCabinet.acceptsInsurance as boolean}
          />
        </CheckBoxGrid>
      </div>
    </div>

    <div class="actions">
      <button class="action-btn cancel-btn" onclick={onClose}>Cancel</button>
      <button
        class="action-btn save-btn"
        onclick={handleSave}
        disabled={isSaving || !locationSelected}
      >
        <Save size={18} />
        <span>{isSaving ? "Adding..." : "Add Cabinet"}</span>
      </button>
    </div>
  </div>
</Block>

<style>
  .form {
    display: flex;
    flex-direction: column;
    gap: 2.5rem;
    padding: 1rem 0;
  }

  .form-section {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
  }

  .section-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid var(--border-color-light);
    color: var(--color-primary-dark);
  }

  .section-header h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0;
  }

  .grid-2 {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 2rem;
    align-items: start;
  }

  .image-upload-container {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }

  .image-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  .image-preview-wrapper {
    position: relative;
    width: 200px;
    height: 150px;
    border: 2px dashed var(--border-color-light);
    border-radius: var(--border-radius-lg);
    overflow: hidden;
    background: var(--background-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .image-preview-wrapper:hover {
    border-color: var(--color-primary);
    background: var(--color-primary-alpha);
  }

  .image-preview {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .image-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-secondary);
  }

  .image-placeholder span {
    font-size: 0.8rem;
    font-weight: 500;
  }

  .image-input {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
  }

  .remove-image {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: rgba(239, 68, 68, 0.9);
    color: white;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 1.25rem;
    line-height: 1;
    z-index: 5;
    transition: all 0.2s ease;
  }

  .remove-image:hover {
    background: rgb(220, 38, 38);
    transform: scale(1.1);
  }

  .inputs-column {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }

  .map-wrapper {
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius-lg);
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  }

  .hours-table-container {
    background: var(--white);
    border: 1px solid var(--border-color-light);
    border-radius: var(--border-radius-lg);
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  }

  .hours-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
  }

  .hours-table th {
    background: var(--background-primary);
    padding: 1rem;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-secondary);
    border-bottom: 1px solid var(--border-color-light);
  }

  .hours-table td {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--border-color-light);
    vertical-align: middle;
  }

  .hours-table tr:last-child td {
    border-bottom: none;
  }

  .hours-table tr.closed {
    background: var(--background-secondary);
    opacity: 0.8;
  }

  .day-cell {
    font-weight: 600;
    color: var(--text-primary);
    width: 120px;
  }

  .status-cell {
    width: 100px;
  }

  .status-toggle {
    padding: 0.4rem 0.8rem;
    border-radius: 2rem;
    font-size: 0.75rem;
    font-weight: 600;
    border: 1px solid var(--color-primary);
    background: var(--color-primary-alpha);
    color: var(--color-primary);
    cursor: pointer;
    transition: all 0.2s ease;
    width: 70px;
    text-align: center;
  }

  .status-toggle.is-closed {
    border-color: var(--text-secondary);
    background: var(--background-third);
    color: var(--text-secondary);
  }

  .status-toggle:hover {
    transform: scale(1.05);
  }

  .time-cell {
    width: 150px;
  }

  .time-cell :global(main) {
    margin-bottom: 0 !important;
  }

  .closed-label {
    color: var(--text-secondary);
    font-style: italic;
    font-size: 0.9rem;
  }

  .actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 1rem;
    padding-top: 2rem;
    border-top: 1px solid var(--border-color-light);
  }

  button.action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.85rem 2rem;
    border: none;
    border-radius: var(--border-radius-md);
    font-family: var(--font-secondary);
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .cancel-btn {
    background: var(--background-secondary);
    color: var(--text-secondary);
  }

  .cancel-btn:hover {
    background: var(--background-third);
    color: var(--text-primary);
  }

  .save-btn {
    background: var(--color-primary-dark);
    color: var(--white);
    box-shadow: 0 4px 12px rgba(var(--shadow-color-rgb), 0.2);
  }

  .save-btn:hover:not(:disabled) {
    background: var(--color-primary);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(var(--shadow-color-rgb), 0.3);
  }

  .save-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }

  @media (max-width: 768px) {
    .grid-2 {
      grid-template-columns: 1fr;
    }

    .hours-table-container {
      overflow-x: auto;
    }

    .map-wrapper {
      height: 400px;
    }
  }
</style>
