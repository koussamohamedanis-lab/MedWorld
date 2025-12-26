<script lang="ts">
    import Block from "$lib/components/ui/Block.svelte";
    import Button from "$lib/components/ui/Button.svelte";
    import Input from "$lib/components/ui/Input.svelte";

    import { fade } from "svelte/transition";
    import { getPermissionsFromUserType } from "$lib/types/permission";

    import type { Admin } from "$lib/types/users/admin";
    import type { Cabinet, Location } from "$lib/types/cabinet";
    import type { SuperAdmin } from "$lib/types/users/superadmin";
    import { Users } from "$lib/types/users";

    import {
        Building,
        Save,
        Trash,
        WifiOff,
        Wifi,
        ParkingCircle,
        AlertTriangle,
        CreditCard,
        Accessibility,
        Plus,
        X,
        MapPin,
    } from "@lucide/svelte";
    import MapSelector from "./MapSelector.svelte";

    import { extract, validate, validation } from "$lib/validation";
    import { CabinetAPI } from "$lib/api";
    import { cabinets } from "$lib/stores/data";
    import { get } from "svelte/store";
    import type { Fillable } from "$lib/validation";
    import { onMount } from "svelte";

    interface IProps {
        user: SuperAdmin | Admin;
        cabinet: Cabinet;
        onBack?: () => void;
        [key: string]: any;
    }

    let { user, cabinet, onBack, ...rest }: IProps = $props();
    let permissions = getPermissionsFromUserType(user.type);

    let activeTab = $state("general");
    let isSaving = $state(false);
    let showDeleteConfirm = $state(false);

    let doctorsCount = $state<number | null>(null);
    let isBootstrappingAdmin = $state(false);
    let bootstrapAdmin = $state({
        firstName: "",
        lastName: "",
        email: "",
        password: "",
        phoneNumber: "",
        address: "",
        gender: "male",
    });

    const defaultHours: Record<string, { open: string; close: string }> = {
        monday: { open: "09:00", close: "17:00" },
        tuesday: { open: "09:00", close: "17:00" },
        wednesday: { open: "09:00", close: "17:00" },
        thursday: { open: "09:00", close: "17:00" },
        friday: { open: "09:00", close: "17:00" },
        saturday: { open: "09:00", close: "13:00" },
        sunday: { open: "Closed", close: "Closed" },
    };

    const initializeOpeningHours = () => {
        const hours: Record<string, any> = {};
        let cabinetHours =
            typeof cabinet.openingHours === "string"
                ? JSON.parse(cabinet.openingHours)
                : cabinet.openingHours || {};

        if (!cabinetHours) cabinetHours = {};

        [
            "monday",
            "tuesday",
            "wednesday",
            "thursday",
            "friday",
            "saturday",
            "sunday",
        ].forEach((day) => {
            // Check for both capitalized and lowercase keys
            const dayKey = day.charAt(0).toUpperCase() + day.slice(1);
            const value =
                cabinetHours[dayKey] || cabinetHours[day] || defaultHours[day];

            hours[day] = {
                value: { ...value },
                error: "",
                validator: (v: any) => "",
            };
        });

        return hours;
    };

    let data: Fillable = $state({
        name: {
            value: cabinet.name || "",
            error: "",
            validator: validation.name,
        },
        phone: {
            value: cabinet.phone || "",
            error: "",
            validator: validation.phoneNumber,
        },
        image: {
            value: cabinet.image || "",
            error: "",
            validator: validation.nothing,
        },
        location: {
            value: (() => {
                const loc =
                    typeof cabinet.location === "string"
                        ? JSON.parse(cabinet.location)
                        : { ...cabinet.location };
                return loc || { address: "", latitude: 0, longitude: 0 };
            })(),
            error: "",
            validator: validation.nothing,
        },
        accessHandicap: {
            value: !!cabinet.accessHandicap,
            error: "",
            validator: validation.nothing,
        },
        hasParking: {
            value: !!cabinet.hasParking,
            error: "",
            validator: validation.nothing,
        },
        hasWifi: {
            value: !!cabinet.hasWifi,
            error: "",
            validator: validation.nothing,
        },
        acceptsUrgent: {
            value: !!cabinet.acceptsUrgent,
            error: "",
            validator: validation.nothing,
        },
        acceptsInsurance: {
            value: !!cabinet.acceptsInsurance,
            error: "",
            validator: validation.nothing,
        },
        openingHours: initializeOpeningHours(),
    });

    function handleLocationChange(location: Location) {
        data.location.value = {
            address: location.address || data.location.value.address,
            latitude: location.latitude,
            longitude: location.longitude,
        };
    }

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

    function isValidTime(time: string) {
        return /^([0-1]\d|2[0-3]):([0-5]\d)$/.test(time);
    }

    function isValidOpeningHours(hours: {
        open: string;
        close: string;
    }): boolean {
        if (hours.open === "Closed" && hours.close === "Closed") return true;
        if (!isValidTime(hours.open) || !isValidTime(hours.close)) return false;

        const [openHour, openMinute] = hours.open.split(":").map(Number);
        const [closeHour, closeMinute] = hours.close.split(":").map(Number);

        if (openHour > closeHour) return false;
        if (openHour === closeHour && openMinute >= closeMinute) return false;

        return true;
    }

    function cancel() {
        onBack?.();
    }

    async function handleSave() {
        let error = validate(data);
        if (error) return alert(error);

        if (!permissions.includes("edit_cabinet")) return;

        isSaving = true;
        try {
            let readyData = extract(data);
            await CabinetAPI.update(cabinet.id, readyData as any);

            // Reactive update of the stores
            cabinets.update((all) =>
                all.map((c) =>
                    c.id === cabinet.id
                        ? ({ ...c, ...readyData } as Cabinet)
                        : c,
                ),
            );

            alert("Cabinet updated successfully!");
        } catch (error) {
            console.error(error);
            alert("Error saving changes");
        } finally {
            isSaving = false;
        }
    }

    async function handleDelete() {
        if (!permissions.includes("remove_cabinet")) return;

        if (!showDeleteConfirm) {
            showDeleteConfirm = true;
            return;
        }

        try {
            await CabinetAPI.delete(cabinet.id);
            alert("Cabinet deleted successfully!");
            if (onBack) onBack();
        } catch (error) {
            console.error(error);
            alert("Error deleting cabinet");
        } finally {
            showDeleteConfirm = false;
        }
    }

    async function loadDoctorsCount() {
        try {
            const list = await CabinetAPI.getDoctors(cabinet.id);
            doctorsCount = list.length;
        } catch (e) {
            doctorsCount = 0;
        }
    }

    onMount(async () => {
        await loadDoctorsCount();
    });

    async function handleBootstrapAdminDoctor() {
        if (user.type !== Users.SuperAdmin) return;
        if (isBootstrappingAdmin) return;

        if (!bootstrapAdmin.firstName || !bootstrapAdmin.lastName || !bootstrapAdmin.email || !bootstrapAdmin.password) {
            alert("firstName, lastName, email and password are required");
            return;
        }

        isBootstrappingAdmin = true;
        try {
            await CabinetAPI.bootstrapAdminDoctor(cabinet.id, bootstrapAdmin);

            const refreshed = await CabinetAPI.getById(cabinet.id);
            if (refreshed) {
                cabinet = refreshed;
                cabinets.update((all) => all.map((c) => (c.id === cabinet.id ? refreshed : c)));
            }

            await loadDoctorsCount();
            alert("Admin doctor created successfully!");
        } catch (e) {
            console.error(e);
            alert("Failed to create admin doctor");
        } finally {
            isBootstrappingAdmin = false;
        }
    }
</script>

<Block
    Icon={Building}
    group="manage_cabinet"
    title="Manage {cabinet?.name || 'Cabinet'}"
    {...rest}
>
    {#if !cabinet || !permissions.includes("view_cabinet")}
        <div class="empty-state" transition:fade>
            <Building size={48} />
            <h3>Cannot find/view cabinet</h3>
            <p>
                You don't have permission to view this cabinet or it doesn't
                exist.
            </p>
        </div>
    {:else}
        <nav class="tabs">
            <button
                class="tab-button"
                class:active={activeTab === "general"}
                onclick={() => (activeTab = "general")}>General</button
            >
            <button
                class="tab-button"
                class:active={activeTab === "features"}
                onclick={() => (activeTab = "features")}>Features</button
            >
            <button
                class="tab-button"
                class:active={activeTab === "schedule"}
                onclick={() => (activeTab = "schedule")}>Schedule</button
            >
        </nav>

        <div class="tab-content">
            {#if activeTab === "general"}
                <div class="form-section" transition:fade>
                    <h3>Basic Information</h3>

                    {#if user.type === Users.SuperAdmin && !cabinet.adminId && doctorsCount === 0}
                        <div class="form-section">
                            <h3>Create Initial Admin Doctor</h3>

                            <Input
                                placeholder="First name..."
                                label="First Name"
                                showLabel
                                bind:value={bootstrapAdmin.firstName}
                                validation={validation.name}
                                required
                            />
                            <Input
                                placeholder="Last name..."
                                label="Last Name"
                                showLabel
                                bind:value={bootstrapAdmin.lastName}
                                validation={validation.name}
                                required
                            />
                            <Input
                                placeholder="Email..."
                                label="Email"
                                showLabel
                                bind:value={bootstrapAdmin.email}
                                validation={validation.email}
                                required
                            />
                            <Input
                                placeholder="Password..."
                                label="Password"
                                showLabel
                                bind:value={bootstrapAdmin.password}
                                validation={validation.password}
                                required
                            />
                            <Input
                                placeholder="Phone number..."
                                label="Phone"
                                showLabel
                                bind:value={bootstrapAdmin.phoneNumber}
                                validation={validation.phoneNumber}
                            />
                            <Input
                                placeholder="Address..."
                                label="Address"
                                showLabel
                                bind:value={bootstrapAdmin.address}
                                validation={validation.nothing}
                            />

                            <Button
                                category="primary"
                                label={isBootstrappingAdmin ? "Creating..." : "Create Admin Doctor"}
                                disabled={isBootstrappingAdmin}
                                onclick={handleBootstrapAdminDoctor}
                            />
                        </div>
                    {/if}

                    <div class="image-management">
                        <label class="image-label" for="cabinet-image-input"
                            >Cabinet Picture</label
                        >
                        <div class="image-preview-wrapper">
                            {#if data.image.value}
                                <img
                                    src={data.image.value}
                                    alt="Cabinet"
                                    class="image-preview"
                                />
                                <button
                                    class="remove-image"
                                    onclick={() => (data.image.value = "")}
                                    >&times;</button
                                >
                            {:else}
                                <div class="image-placeholder">
                                    <Plus size={32} />
                                    <span>Upload picture</span>
                                </div>
                            {/if}
                            <input
                                id="cabinet-image-input"
                                type="file"
                                accept="image/*"
                                class="image-input"
                                onchange={handleFileChange}
                            />
                        </div>
                    </div>

                    <Input
                        placeholder="Cabinet name..."
                        label="Name"
                        showLabel
                        bind:value={data.name.value}
                        bind:error={data.name.error}
                        validation={data.name.validator}
                        required
                    />
                    <Input
                        placeholder="Phone number..."
                        label="Phone"
                        showLabel
                        bind:value={data.phone.value}
                        bind:error={data.phone.error}
                        validation={data.phone.validator}
                        type="tel"
                        required
                    />
                    <div class="location-management">
                        <div class="section-header">
                            <MapPin size={20} />
                            <h3>Cabinet Location</h3>
                        </div>
                        <div class="map-wrapper-outline">
                            <MapSelector
                                location={data.location.value}
                                onChange={handleLocationChange}
                            />
                        </div>
                    </div>
                </div>
            {:else if activeTab === "features"}
                <div class="form-section" transition:fade>
                    <h3>Cabinet Features</h3>
                    <div class="features-grid">
                        <label class="feature-item">
                            <input
                                type="checkbox"
                                bind:checked={data.acceptsInsurance.value}
                            />
                            <CreditCard size={20} />
                            <span>Accepts Insurance</span>
                        </label>
                        <label class="feature-item">
                            <input
                                type="checkbox"
                                bind:checked={data.acceptsUrgent.value}
                            />
                            <AlertTriangle size={20} />
                            <span>Accepts Urgent Cases</span>
                        </label>
                        <label class="feature-item">
                            <input
                                type="checkbox"
                                bind:checked={data.accessHandicap.value}
                            />
                            <Accessibility size={20} />
                            <span>Wheelchair Accessible</span>
                        </label>
                        <label class="feature-item">
                            <input
                                type="checkbox"
                                bind:checked={data.hasParking.value}
                            />
                            <ParkingCircle size={20} />
                            <span>Has Parking</span>
                        </label>
                        <label class="feature-item">
                            <input
                                type="checkbox"
                                bind:checked={data.hasWifi.value}
                            />
                            {#if data.hasWifi.value}
                                <Wifi size={20} />
                            {:else}
                                <WifiOff size={20} />
                            {/if}
                            <span>Free Wi-Fi</span>
                        </label>
                    </div>
                </div>
            {:else if activeTab === "schedule"}
                <div class="form-section" transition:fade>
                    <h3>Working Hours</h3>
                    <div class="schedule-grid">
                        {#each ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"] as day}
                            <div class="day-schedule">
                                <h4>{day}</h4>
                                <div class="schedule-header-actions">
                                    <button
                                        type="button"
                                        class="status-toggle"
                                        class:is-closed={data.openingHours[
                                            day.toLowerCase()
                                        ].value.open === "Closed"}
                                        onclick={() => {
                                            const dayKey = day.toLowerCase();
                                            if (
                                                data.openingHours[dayKey].value
                                                    .open === "Closed"
                                            ) {
                                                data.openingHours[
                                                    dayKey
                                                ].value.open = "09:00";
                                                data.openingHours[
                                                    dayKey
                                                ].value.close = "17:00";
                                            } else {
                                                data.openingHours[
                                                    dayKey
                                                ].value.open = "Closed";
                                                data.openingHours[
                                                    dayKey
                                                ].value.close = "Closed";
                                            }
                                        }}
                                    >
                                        {data.openingHours[day.toLowerCase()]
                                            .value.open === "Closed"
                                            ? "Closed"
                                            : "Open"}
                                    </button>
                                </div>
                                <div
                                    class="time-inputs"
                                    class:content-hidden={data.openingHours[
                                        day.toLowerCase()
                                    ].value.open === "Closed"}
                                >
                                    <Input
                                        type="time"
                                        label="Opens"
                                        showLabel
                                        bind:value={
                                            data.openingHours[day.toLowerCase()]
                                                .value.open
                                        }
                                        bind:error={
                                            data.openingHours[day.toLowerCase()]
                                                .error
                                        }
                                        validation={(e: string) => {
                                            if (
                                                isValidOpeningHours({
                                                    open: e,
                                                    close: data.openingHours[
                                                        day.toLowerCase()
                                                    ].value.close,
                                                })
                                            ) {
                                                return "";
                                            }
                                            return "Invalid opening hours";
                                        }}
                                    />
                                    <Input
                                        type="time"
                                        label="Closes"
                                        showLabel
                                        bind:value={
                                            data.openingHours[day.toLowerCase()]
                                                .value.close
                                        }
                                        bind:error={
                                            data.openingHours[day.toLowerCase()]
                                                .error
                                        }
                                        validation={(e: string) => {
                                            if (
                                                isValidOpeningHours({
                                                    open: data.openingHours[
                                                        day.toLowerCase()
                                                    ].value.open,
                                                    close: e,
                                                })
                                            ) {
                                                return "";
                                            }
                                            return "Invalid opening hours";
                                        }}
                                    />
                                </div>
                                {#if data.openingHours[day.toLowerCase()].value.open === "Closed"}
                                    <p class="closed-info">
                                        This cabinet is closed on {day}
                                    </p>
                                {/if}
                            </div>
                        {/each}
                    </div>
                </div>
            {/if}
        </div>

        <div class="actions">
            <Button
                category="secondary"
                Icon={X}
                label={"Back"}
                disabled={isSaving}
                onclick={cancel}
            />

            {#if permissions.includes("remove_cabinet")}
                <Button
                    category="error"
                    Icon={Trash}
                    label={showDeleteConfirm
                        ? "Confirm Delete"
                        : `Delete ${cabinet.name}`}
                    onclick={handleDelete}
                />
            {/if}
            {#if permissions.includes("edit_cabinet")}
                <Button
                    category="primary"
                    Icon={Save}
                    label={isSaving ? "Saving..." : "Save Changes"}
                    disabled={isSaving}
                    onclick={handleSave}
                />
            {/if}
        </div>
    {/if}
</Block>

<style>
    .tabs {
        display: flex;
        gap: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border-color);
        margin-bottom: 2rem;
    }

    .tab-button {
        background: none;
        border: none;
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
        color: var(--text-muted);
        cursor: pointer;
        border-radius: 0.5rem;
        transition: all 0.2s ease;
    }

    .tab-button:hover {
        background: var(--background-hover);
        color: var(--text-color);
    }

    .tab-button.active {
        background: var(--color-primary);
        color: white;
    }

    .form-section {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .form-section h3 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
    }

    .form-section h4 {
        margin: 1rem 0 0.5rem;
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text-muted);
    }

    .image-management {
        margin-bottom: 1.5rem;
    }

    .image-label {
        display: block;
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .location-management {
        margin-top: 1rem;
    }

    .section-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--border-color-light);
        color: var(--color-primary-dark);
        margin-bottom: 1rem;
    }

    .map-wrapper-outline {
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .image-preview-wrapper {
        position: relative;
        width: 200px;
        height: 150px;
        border: 2px dashed var(--border-color);
        border-radius: 0.75rem;
        overflow: hidden;
        background: var(--background-secondary);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
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
        color: var(--text-muted);
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
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 1rem;
    }

    .feature-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem;
        background: var(--background-secondary);
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .feature-item:hover {
        border-color: var(--color-primary);
        transform: translateY(-2px);
    }

    .feature-item input[type="checkbox"] {
        width: 1.125rem;
        height: 1.125rem;
        cursor: pointer;
    }

    .schedule-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 1.5rem;
    }

    .day-schedule {
        background: var(--background-secondary);
        padding: 1rem;
        border-radius: 0.75rem;
        border: 1px solid var(--border-color);
    }

    .day-schedule h4 {
        margin: 0;
        color: var(--text-color);
    }

    .schedule-header-actions {
        margin: 0.5rem 0 1rem;
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
        width: 100%;
        text-align: center;
    }

    .status-toggle.is-closed {
        border-color: var(--text-muted);
        background: var(--background-hover);
        color: var(--text-muted);
    }

    .status-toggle:hover {
        transform: scale(1.02);
    }

    .time-inputs {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .time-inputs.content-hidden {
        display: none;
    }

    .closed-info {
        font-size: 0.8rem;
        color: var(--text-muted);
        font-style: italic;
        margin: 0.5rem 0 0;
    }

    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 3rem;
        text-align: center;
        color: var(--text-muted);
    }

    .empty-state h3 {
        margin: 1rem 0 0.5rem;
        font-size: 1.1rem;
        font-weight: 600;
    }

    .empty-state p {
        margin: 0;
        font-size: 0.9rem;
    }

    .actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border-color);
    }

    @media (max-width: 768px) {
        .tabs {
            overflow-x: auto;
            margin: 0 -1rem;
            padding: 0 1rem 0.5rem;
        }

        .tab-button {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            white-space: nowrap;
        }

        .features-grid {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        }

        .schedule-grid {
            grid-template-columns: 1fr;
        }

        .actions {
            flex-direction: column-reverse;
            gap: 0.5rem;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .tab-button,
        .feature-item {
            transition: none;
        }

        .feature-item:hover {
            transform: none;
        }
    }
</style>
