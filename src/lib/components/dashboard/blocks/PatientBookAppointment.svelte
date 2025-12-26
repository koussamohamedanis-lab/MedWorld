<script lang="ts">
    import { onMount } from "svelte";
    import {
        Calendar,
        MapPin,
        Building2,
        User,
        Clock,
        Loader,
        ChevronRight,
        AlertCircle,
    } from "@lucide/svelte";
    import Block from "$lib/components/ui/Block.svelte";
    import Button from "$lib/components/ui/Button.svelte";
    import Input from "$lib/components/ui/Input.svelte";
    import SearchInput from "$lib/components/ui/SearchInput.svelte";
    import Modal from "$lib/components/ui/Modal.svelte";
    import { AppointmentAPI, CabinetAPI, DoctorAPI } from "$lib/api";
    import type { Cabinet } from "$lib/types/cabinet";
    import type { Doctor } from "$lib/types/users/doctor";
    import type { Patient } from "$lib/types/users/patient";
    import { Users } from "$lib/types/users";

    interface IProps {
        patient: Patient;
    }

    let { patient }: IProps = $props();

    // State
    let step = $state(1);
    let searchTerm = $state("");
    let cabinets = $state<Cabinet[]>([]);
    let selectedCabinet = $state<Cabinet | null>(null);
    let selectedDoctor = $state<Doctor | null>(null);
    let doctors = $state<Doctor[]>([]);
    let selectedDate = $state("");
    let selectedTime = $state("");
    let availableSlots = $state<string[]>([]);
    let isLoadingSlots = $state(false);
    let isLoading = $state(false);
    let isSubmitting = $state(false);
    let errorMessage = $state("");
    let successMessage = $state("");
    let showConfirmModal = $state(false);

    // Load cabinets and their doctors
    onMount(async () => {
        isLoading = true;
        try {
            cabinets = await CabinetAPI.list();
        } catch (error) {
            console.error("Failed to load cabinets:", error);
            errorMessage = "Failed to load cabinets. Please try again.";
        } finally {
            isLoading = false;
        }
    });

    async function loadDoctorsForCabinet(cabinetId: number) {
        try {
            doctors = await CabinetAPI.getDoctors(cabinetId);
        } catch (e) {
            console.error("Failed to load doctors:", e);
            doctors = [];
        }
    }

    // Derived filtered lists
    let filteredCabinets = $derived(
        cabinets.filter(
            (c) =>
                c.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                (c.location?.address || "")
                    .toLowerCase()
                    .includes(searchTerm.toLowerCase()),
        ),
    );

    let filteredDoctors = $derived(
        doctors
            .filter((d: any) => d.type === Users.Doctor || d.type === Users.Admin)
            .filter(
                (d: any) =>
                    d.fullName?.toLowerCase().includes(searchTerm.toLowerCase()) ||
                    String(d.speciality || "")
                        .toLowerCase()
                        .includes(searchTerm.toLowerCase()),
            ),
    );

    // Generate available time slots
    function getTimeSlots() {
        const slots = [];
        for (let h = 9; h < 17; h++) {
            for (let m = 0; m < 60; m += 30) {
                slots.push(
                    `${String(h).padStart(2, "0")}:${String(m).padStart(2, "0")}`,
                );
            }
        }
        return slots;
    }

    $effect(() => {
        // If date changes, reset time and refresh availability.
        selectedTime = "";
        if (step === 3) {
            if (selectedDate) {
                loadSlots();
            } else {
                availableSlots = [];
            }
        }
    });

    // Validation
    function isDateValid() {
        if (!selectedDate) return false;
        const selected = new Date(selectedDate);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        return selected >= today;
    }

    function canProceedToNextStep() {
        if (step === 1) return selectedCabinet !== null;
        if (step === 2) return selectedDoctor !== null;
        if (step === 3) return selectedDate && selectedTime && isDateValid() && availableSlots.includes(selectedTime);
        return false;
    }

    // Handlers
    function nextStep() {
        errorMessage = "";
        if (!canProceedToNextStep()) {
            errorMessage = "Please complete this step before proceeding";
            return;
        }
        step++;
    }

    function prevStep() {
        if (step > 1) step--;
        errorMessage = "";
    }

    function resetForm() {
        selectedCabinet = null;
        selectedDoctor = null;
        doctors = [];
        selectedDate = "";
        selectedTime = "";
        availableSlots = [];
        step = 1;
        searchTerm = "";
        errorMessage = "";
    }

    async function loadSlots() {
        if (!selectedDoctor?.doctorId || !selectedDate) {
            availableSlots = [];
            return;
        }
        isLoadingSlots = true;
        try {
            availableSlots = await DoctorAPI.getAvailability(selectedDoctor.doctorId, selectedDate);
        } catch (e) {
            console.error("Failed to load availability:", e);
            availableSlots = [];
        } finally {
            isLoadingSlots = false;
        }
    }

    $effect(() => {
        if (step === 3 && selectedDate) {
            loadSlots();
        }
    });

    function showConfirmation() {
        errorMessage = "";
        if (!isDateValid()) {
            errorMessage = "Selected date must be in the future";
            return;
        }
        showConfirmModal = true;
    }

    async function submitAppointment() {
        if (!patient?.patientId || !selectedDoctor?.doctorId || !selectedCabinet?.id) {
            errorMessage = "Missing required information";
            return;
        }

        isSubmitting = true;
        errorMessage = "";

        try {
            const [datePart, timePart] = [selectedDate, selectedTime];
            const dateTime = new Date(`${datePart}T${timePart}:00`);

            await AppointmentAPI.create({
                patientId: patient.patientId,
                doctorId: selectedDoctor.doctorId,
                cabinetId: selectedCabinet.id,
                date: dateTime,
                status: "SCHEDULED",
            } as any);

            successMessage = "Appointment booked successfully!";
            showConfirmModal = false;
            setTimeout(() => {
                resetForm();
                successMessage = "";
            }, 2000);
        } catch (error: any) {
            console.error("Error booking appointment:", error);
            errorMessage =
                error.message ||
                "Failed to book appointment. Please try again.";
        } finally {
            isSubmitting = false;
        }
    }
</script>

<Block group="book_appointment" title="Book an Appointment" Icon={Calendar}>
    {#if successMessage}
        <div class="success-banner">
            <p>✓ {successMessage}</p>
        </div>
    {/if}

    {#if errorMessage}
        <div class="error-banner">
            <AlertCircle size={18} />
            <p>{errorMessage}</p>
        </div>
    {/if}

    {#if isLoading}
        <div class="loading-container">
            <Loader size={32} />
            <p>Loading available cabinets...</p>
        </div>
    {:else}
        <!-- Progress Indicator -->
        <div class="progress-container">
            <div class="step-indicator" class:active={step >= 1}>
                <div class="step-number">1</div>
                <span>Cabinet</span>
            </div>
            <div class="progress-line" class:active={step >= 2}></div>
            <div class="step-indicator" class:active={step >= 2}>
                <div class="step-number">2</div>
                <span>Doctor</span>
            </div>
            <div class="progress-line" class:active={step >= 3}></div>
            <div class="step-indicator" class:active={step >= 3}>
                <div class="step-number">3</div>
                <span>Date & Time</span>
            </div>
        </div>

        <!-- Step 1: Select Cabinet -->
        {#if step === 1}
            <div class="step-content">
                <h3>Select a Cabinet</h3>
                <SearchInput
                    placeholder="Search by name or location..."
                    bind:value={searchTerm}
                />

                <div class="cabinet-grid">
                    {#each filteredCabinets as cabinet (cabinet.id)}
                        <button
                            class="cabinet-card"
                            class:selected={selectedCabinet?.id === cabinet.id}
                            onclick={async () => {
                                selectedCabinet = cabinet;
                                selectedDoctor = null;
                                await loadDoctorsForCabinet(cabinet.id);
                            }}
                        >
                            <div class="card-header">
                                <h4>{cabinet.name}</h4>
                                <span class="doctor-count">Doctors</span>
                            </div>
                            <p class="location">
                                <MapPin size={16} />
                                {cabinet.location?.address || "No address"}
                            </p>
                            {#if selectedCabinet?.id === cabinet.id}
                                <div class="checkmark">✓</div>
                            {/if}
                        </button>
                    {/each}
                </div>

                {#if filteredCabinets.length === 0}
                    <p class="no-results">
                        No cabinets found matching your search
                    </p>
                {/if}
            </div>

            <!-- Step 2: Select Doctor -->
        {:else if step === 2}
            <div class="step-content">
                <div class="step-header">
                    <h3>Select a Doctor</h3>
                    <p class="cabinet-selected">
                        <Building2 size={16} />
                        {selectedCabinet?.name}
                    </p>
                </div>

                <SearchInput
                    placeholder="Search by name or specialty..."
                    bind:value={searchTerm}
                />

                <div class="doctor-grid">
                    {#each filteredDoctors as doctor (doctor.id)}
                        <button
                            class="doctor-card"
                            class:selected={selectedDoctor?.id === doctor.id}
                            onclick={() => (selectedDoctor = doctor)}
                        >
                            <div class="card-header">
                                <h4>{doctor.fullName}</h4>
                                <span class="specialty"
                                    >{doctor.speciality}</span
                                >
                            </div>
                            <div class="doctor-info">
                                <p>
                                    <Clock size={14} />
                                    {doctor.consultationDuration || 30}min
                                </p>
                                <p class="price">
                                    {doctor.consultationPrice || 2000} DZD
                                </p>
                            </div>
                            {#if selectedDoctor?.id === doctor.id}
                                <div class="checkmark">✓</div>
                            {/if}
                        </button>
                    {/each}
                </div>

                {#if filteredDoctors.length === 0}
                    <p class="no-results">No doctors found in this cabinet</p>
                {/if}
            </div>

            <!-- Step 3: Select Date & Time -->
        {:else if step === 3}
            <div class="step-content">
                <h3>Select Date & Time</h3>
                <div class="summary">
                    <p>
                        <Building2 size={14} />
                        {selectedCabinet?.name}
                    </p>
                    <p>
                        <User size={14} />
                        {selectedDoctor?.fullName}
                    </p>
                </div>

                <div class="date-time-container">
                    <div class="date-picker">
                        <Input
                            category="input"
                            type="date"
                            bind:value={selectedDate}
                            placeholder="Select date"
                            min={new Date().toISOString().split("T")[0]}
                        />
                    </div>

                    <div class="time-slots">
                        {#if isLoadingSlots}
                            <p class="muted">Loading available slots...</p>
                        {:else if !selectedDate}
                            <p class="muted">Select a date to see available slots.</p>
                        {:else if availableSlots.length === 0}
                            <p class="muted">No available slots for this date.</p>
                        {:else}
                            <div class="slots-grid">
                                {#each availableSlots as slot}
                                    <button
                                        class="slot-button"
                                        class:selected={selectedTime === slot}
                                        onclick={() => (selectedTime = slot)}
                                    >
                                        {slot}
                                    </button>
                                {/each}
                            </div>
                        {/if}
                    </div>
                </div>
            </div>
        {/if}

        <!-- Navigation Buttons -->
        <div class="action-buttons">
            {#if step > 1}
                <Button
                    category="secondary"
                    label="Back"
                    onClick={prevStep}
                    disabled={isSubmitting}
                />
            {/if}

            {#if step < 3}
                <Button
                    category="primary"
                    label="Next"
                    Icon={ChevronRight}
                    onClick={nextStep}
                    disabled={!canProceedToNextStep() || isSubmitting}
                />
            {:else}
                <Button
                    category="primary"
                    label="Review & Confirm"
                    Icon={ChevronRight}
                    onClick={showConfirmation}
                    disabled={!canProceedToNextStep() || isSubmitting}
                />
            {/if}
        </div>
    {/if}
</Block>

<!-- Confirmation Modal -->
<Modal
    isOpen={showConfirmModal}
    onClose={() => {
        showConfirmModal = false;
    }}
    title="Confirm Appointment"
>
    <div class="confirmation">
        <div class="confirm-item">
            <span class="label">Cabinet:</span>
            <span class="value">{selectedCabinet?.name}</span>
        </div>
        <div class="confirm-item">
            <span class="label">Doctor:</span>
            <span class="value">{selectedDoctor?.fullName}</span>
            <span class="sub-value">{selectedDoctor?.speciality}</span>
        </div>
        <div class="confirm-item">
            <span class="label">Date:</span>
            <span class="value"
                >{selectedDate
                    ? new Date(selectedDate).toLocaleDateString()
                    : ""}</span
            >
        </div>
        <div class="confirm-item">
            <span class="label">Time:</span>
            <span class="value">{selectedTime}</span>
        </div>
        <div class="confirm-item">
            <span class="label">Duration:</span>
            <span class="value"
                >{selectedDoctor?.consultationDuration || 30} minutes</span
            >
        </div>
        <div class="confirm-item highlight">
            <span class="label">Fee:</span>
            <span class="value"
                >{selectedDoctor?.consultationPrice || 2000} DZD</span
            >
        </div>

        <div class="modal-actions">
            <Button
                category="secondary"
                label="Cancel"
                onClick={() => {
                    showConfirmModal = false;
                }}
                disabled={isSubmitting}
            />
            <Button
                category="primary"
                label={isSubmitting ? "Booking..." : "Confirm Appointment"}
                Icon={isSubmitting ? Loader : Calendar}
                onClick={submitAppointment}
                disabled={isSubmitting}
            />
        </div>
    </div>
</Modal>

<style>
    .loading-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 3rem;
        gap: 1rem;
        color: var(--text-muted);
    }

    .success-banner {
        padding: 1rem;
        background: #dcfce7;
        border: 1px solid #86efac;
        border-radius: 0.75rem;
        color: #166534;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .error-banner {
        padding: 1rem;
        background: #fee2e2;
        border: 1px solid #fca5a5;
        border-radius: 0.75rem;
        color: #991b1b;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .progress-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 2rem;
        padding: 0 1rem;
    }

    .step-indicator {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        flex: 1;
    }

    .step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--background-secondary);
        border: 2px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        color: var(--text-muted);
        transition: all 0.3s ease;
    }

    .step-indicator.active .step-number {
        background: var(--color-primary);
        border-color: var(--color-primary);
        color: white;
    }

    .step-indicator span {
        font-size: 0.75rem;
        color: var(--text-muted);
        text-align: center;
    }

    .step-indicator.active span {
        color: var(--color-primary);
        font-weight: 500;
    }

    .progress-line {
        flex: 1;
        height: 2px;
        background: var(--border-color);
        margin: 0 0.5rem;
        transition: all 0.3s ease;
    }

    .progress-line.active {
        background: var(--color-primary);
    }

    .step-content {
        padding: 2rem 0;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .step-content h3 {
        margin: 0 0 1rem;
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--text-color);
    }

    .step-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
    }

    .step-header h3 {
        margin: 0;
    }

    .cabinet-selected,
    .summary {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: var(--text-muted);
        padding: 1rem;
        background: var(--background-secondary);
        border-radius: 0.5rem;
    }

    .cabinet-selected p,
    .summary p {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0;
    }

    .cabinet-grid,
    .doctor-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1rem;
        max-height: 400px;
        overflow-y: auto;
    }

    .cabinet-card,
    .doctor-card {
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        padding: 1rem;
        background: var(--background-secondary);
        border: 2px solid var(--border-color);
        border-radius: 0.75rem;
        text-align: left;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .cabinet-card:hover,
    .doctor-card:hover {
        border-color: var(--color-primary);
        transform: translateY(-2px);
    }

    .cabinet-card.selected,
    .doctor-card.selected {
        border-color: var(--color-primary);
        background: var(--color-primary-alpha);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
    }

    .card-header h4 {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text-color);
    }

    .doctor-count,
    .specialty {
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
        background: var(--color-primary-alpha);
        color: var(--color-primary);
        border-radius: 1rem;
        white-space: nowrap;
    }

    .location {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: var(--text-muted);
        margin: 0;
    }

    .doctor-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.5rem;
    }

    .doctor-info p {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        margin: 0;
        font-size: 0.875rem;
        color: var(--text-muted);
    }

    .doctor-info .price {
        font-weight: 600;
        color: var(--color-primary);
        margin-left: auto;
    }

    .checkmark {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: var(--color-primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.875rem;
    }

    .no-results {
        text-align: center;
        padding: 2rem;
        color: var(--text-muted);
        margin: 0;
    }

    .datetime-form {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .form-group label {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--text-color);
    }

    .time-slots {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
        gap: 0.75rem;
    }

    .time-slot {
        padding: 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: 0.5rem;
        background: var(--background-primary);
        color: var(--text-color);
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .time-slot:hover {
        border-color: var(--color-primary);
    }

    .time-slot.selected {
        background: var(--color-primary);
        border-color: var(--color-primary);
        color: white;
    }

    .error-text {
        font-size: 0.75rem;
        color: #dc2626;
    }

    .action-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--border-color);
    }

    .confirmation {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .confirm-item {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 1rem;
        background: var(--background-secondary);
        border-radius: 0.5rem;
        gap: 1rem;
    }

    .confirm-item.highlight {
        background: var(--color-primary-alpha);
        border: 1px solid var(--color-primary);
    }

    .confirm-item .label {
        font-weight: 600;
        color: var(--text-muted);
        min-width: 100px;
    }

    .confirm-item .value {
        text-align: right;
        font-weight: 500;
        color: var(--text-color);
    }

    .sub-value {
        display: block;
        font-size: 0.75rem;
        color: var(--text-muted);
        text-align: right;
    }

    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border-color);
    }

    @media (max-width: 768px) {
        .progress-container {
            padding: 0;
        }

        .step-indicator span {
            display: none;
        }

        .cabinet-grid,
        .doctor-grid {
            grid-template-columns: 1fr;
        }

        .step-header {
            flex-direction: column;
        }

        .time-slots {
            grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
        }

        .confirm-item {
            flex-direction: column;
        }

        .confirm-item .value,
        .sub-value {
            text-align: left;
        }
    }
</style>
