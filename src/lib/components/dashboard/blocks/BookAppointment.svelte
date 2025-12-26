<script lang="ts">
    import Block from "$lib/components/ui/Block.svelte";
    import Button from "$lib/components/ui/Button.svelte";
    import Input from "$lib/components/ui/Input.svelte";
    import SearchInput from "$lib/components/ui/SearchInput.svelte";
    import {
        Calendar,
        MapPin,
        Building2,
        User,
        Clock,
        Calendar as CalendarIcon,
        Compass,
    } from "@lucide/svelte";
    import { fade, slide } from "svelte/transition";
    import { isCabinetOpen, type Cabinet } from "$lib/types/cabinet";
    import type { Doctor } from "$lib/types/users/doctor";
    import type { Fillable } from "$lib/validation";
    import {
        cabinets as cabinetsStore,
        doctors as doctorsStore,
        loadAllCabinets,
        loadAllDoctors,
    } from "$lib/stores/data";
    import { Users, type IDoctor } from "$lib/types/users";
    import { validate, validation } from "$lib/validation";
    import { onMount } from "svelte";

    let step = $state(1);
    let searchTerm = $state("");
    let searchType = $state<"cabinet" | "doctor">("cabinet");
    let selectedCabinet: Cabinet | null = $state(null);
    let selectedDoctor: Doctor | null = $state(null);
    let selectedDate: string = $state("");
    let selectedTime: string = $state("");

    let cabinets = $state<Cabinet[]>([]);
    let doctors = $state<Doctor[]>([]);

    onMount(async () => {
        // Load cabinets and doctors from API if not already loaded
        if ($cabinetsStore.length === 0) {
            await loadAllCabinets();
        }
        if ($doctorsStore.length === 0) {
            await loadAllDoctors();
        }
        cabinets = $cabinetsStore;
        doctors = $doctorsStore;
    });

    function resetSelections() {
        selectedCabinet = null;
        selectedDoctor = null;
    }

    let filteredCabinets = $derived(
        cabinets.filter(
            (cabinet) =>
                cabinet.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                cabinet.location.address
                    .toLowerCase()
                    .includes(searchTerm.toLowerCase()),
        ),
    );

    let filteredDoctors = $derived(
        (() => {
            const allDoctors = cabinets.flatMap((c) => c.doctors);

            return allDoctors.filter((doctor: IDoctor) => {
                if (doctor.type !== Users.Doctor && doctor.type !== Users.Admin)
                    return false;

                const matchesSearch =
                    doctor.fullName
                        .toLowerCase()
                        .includes(searchTerm.toLowerCase()) ||
                    doctor.speciality
                        .toLowerCase()
                        .includes(searchTerm.toLowerCase());

                if (selectedCabinet) {
                    return matchesSearch;
                } else {
                    return matchesSearch;
                }
            });
        })(),
    );

    function handleNext() {
        if (step < 3) step++;
    }

    function handleBack() {
        if (step > 1) step--;
    }

    function handleSubmit() {
        alert("Appointment request submitted!");
        step = 1;
        selectedCabinet = null;
        selectedDoctor = null;
        selectedDate = "";
        selectedTime = "";
    }

    function getAvailableTimeSlots() {
        return [
            "09:00",
            "09:30",
            "10:00",
            "10:30",
            "11:00",
            "11:30",
            "14:00",
            "14:30",
            "15:00",
            "15:30",
            "16:00",
            "16:30",
        ];
    }

    let data: Fillable = $state({
        selectedDate: {
            value: "",
            error: "",
            validator: validation.futureDate,
        },
    });

    function book() {
        validateField("selectedDate");
        // Check if any field has error
        const hasErrors = Object.keys(data).some(
            (key) => data[key as keyof typeof data].error !== "",
        );

        if (hasErrors) {
            alert("Invalid date.");
            return;
        }

        let error = validate(data);
        if (error) {
            return alert(error);
        }
        window.location.href = "/dashboard";
    }

    function validateField(arg0: string) {
        throw new Error("Function not implemented.");
    }
</script>

<Block group="book_appointment" title="Book Appointment" Icon={Calendar}>
    <div class="booking-container">
        <div class="progress-bar">
            <div class="progress-step" class:active={step >= 1}>
                <div class="step-number">1</div>
                <span
                    >Select {searchType === "cabinet"
                        ? "Cabinet"
                        : "Doctor"}</span
                >
            </div>
            <div class="progress-line" class:active={step >= 2}></div>
            <div class="progress-step" class:active={step >= 2}>
                <div class="step-number">2</div>
                <span
                    >{searchType === "cabinet"
                        ? "Select Doctor"
                        : "Select Cabinet"}</span
                >
            </div>
            <div class="progress-line" class:active={step >= 3}></div>
            <div class="progress-step" class:active={step >= 3}>
                <div class="step-number">3</div>
                <span>Choose Time</span>
            </div>
        </div>

        {#if step === 1}
            <div class="step-content" transition:fade>
                <div class="search-type">
                    <button
                        class="type-btn"
                        class:active={searchType === "cabinet"}
                        onclick={() => {
                            searchType = "cabinet";
                            resetSelections();
                        }}
                    >
                        <Building2 size={20} />
                        Search by Cabinet
                    </button>
                    <button
                        class="type-btn"
                        class:active={searchType === "doctor"}
                        onclick={() => {
                            searchType = "doctor";
                            resetSelections();
                        }}
                    >
                        <User size={20} />
                        Search by Doctor
                    </button>
                </div>

                <div class="search-section">
                    <SearchInput
                        placeholder={searchType === "cabinet"
                            ? "Search cabinets by name or location..."
                            : "Search doctors by name or specialty..."}
                        bind:value={searchTerm}
                    />

                    <div class="results-grid">
                        {#if searchType === "cabinet"}
                            {#each filteredCabinets as cabinet}
                                <button
                                    class="result-card"
                                    class:selected={selectedCabinet?.id ===
                                        cabinet.id}
                                    onclick={() => (selectedCabinet = cabinet)}
                                >
                                    <div class="card-header">
                                        <h3>{cabinet.name}</h3>
                                        {#if !isCabinetOpen(cabinet)}
                                            <span class="status closed"
                                                >Closed</span
                                            >
                                        {:else}
                                            <span class="status open">Open</span
                                            >
                                        {/if}
                                    </div>
                                    <p class="info">
                                        <User size={16} />
                                        {cabinet.doctors.length} doctors available
                                    </p>
                                </button>
                            {/each}
                        {:else}
                            {#each filteredDoctors as doctor}
                                <button
                                    class="result-card"
                                    class:selected={selectedDoctor?.id ===
                                        doctor.id}
                                    onclick={() => (selectedDoctor = doctor)}
                                >
                                    <div class="card-header">
                                        <h3>{doctor.fullName}</h3>
                                        <span class="specialty"
                                            >{doctor.speciality}</span
                                        >
                                    </div>
                                    <p class="info">
                                        <Clock size={16} />
                                        {doctor.consultationDuration}min
                                        consultation
                                    </p>
                                    <p class="price">
                                        {doctor.consultationPrice} DZD
                                    </p>
                                </button>
                            {/each}
                        {/if}
                    </div>
                </div>
            </div>
        {:else if step === 2}
            <div class="step-content" transition:fade>
                <div class="selected-item">
                    {#if searchType === "cabinet"}
                        <h3>Selected Cabinet: {selectedCabinet?.name}</h3>

                        <br />
                    {:else}
                        <h3>
                            Selected Doctor: {selectedDoctor?.fullName}
                        </h3>

                        <br />
                    {/if}
                </div>

                <div class="search-section">
                    <SearchInput
                        placeholder={searchType === "cabinet"
                            ? "Search doctors in this cabinet..."
                            : "Search available cabinets..."}
                        bind:value={searchTerm}
                    />

                    <div class="results-grid">
                        {#if searchType === "cabinet"}
                            {#each filteredDoctors as doctor}
                                <button
                                    class="result-card"
                                    class:selected={selectedDoctor?.id ===
                                        doctor.id}
                                    onclick={() => (selectedDoctor = doctor)}
                                >
                                    <div class="card-header">
                                        <h3>{doctor.fullName}</h3>
                                        <span class="specialty"
                                            >{doctor.speciality}</span
                                        >
                                    </div>
                                    <p class="info">
                                        <Clock size={16} />
                                        {doctor.consultationDuration}min
                                        consultation
                                    </p>
                                    <p class="price">
                                        {doctor.consultationPrice} DZD
                                    </p>
                                </button>
                            {/each}
                        {:else}
                            {#each filteredCabinets as cabinet}
                                <button
                                    class="result-card"
                                    class:selected={selectedCabinet?.id ===
                                        cabinet.id}
                                    onclick={() => (selectedCabinet = cabinet)}
                                >
                                    <div class="card-header">
                                        <h3>{cabinet.name}</h3>
                                        {#if !isCabinetOpen(cabinet)}
                                            <span class="status closed"
                                                >Closed</span
                                            >
                                        {:else}
                                            <span class="status open">Open</span
                                            >
                                        {/if}
                                    </div>
                                    <p class="location">
                                        <MapPin size={16} />
                                        {cabinet.location.address}
                                    </p>
                                </button>
                            {/each}
                        {/if}
                    </div>
                </div>
            </div>
        {:else if step === 3}
            <div class="step-content" transition:fade>
                <div class="selected-items">
                    <div class="selection-summary">
                        <div class="summary-item">
                            <Building2 size={20} />
                            <div>
                                <h4>Cabinet</h4>
                                <p>{selectedCabinet?.name}</p>
                            </div>
                        </div>
                        <div class="summary-item">
                            <User size={20} />
                            <div>
                                <h4>Doctor</h4>
                                <p>{selectedDoctor?.fullName}</p>
                                <span class="specialty"
                                    >{selectedDoctor?.speciality}</span
                                >
                            </div>
                        </div>
                    </div>

                    <div class="datetime-picker">
                        <div class="date-section">
                            <h4>Select Date</h4>
                            <Input
                                type="date"
                                bind:value={data.selectedDate.value}
                                bind:error={data.selectedDate.error}
                                validation={data.selectedDate.validator}
                                min={new Date().toISOString().split("T")[0]}
                                theme="secondary"
                            />
                        </div>

                        {#if selectedDate}
                            <div class="time-section">
                                <h4>Available Time Slots</h4>
                                <div class="time-slots">
                                    {#each getAvailableTimeSlots() as time}
                                        <button
                                            class="time-slot"
                                            class:selected={selectedTime ===
                                                time}
                                            onclick={() =>
                                                (selectedTime = time)}
                                        >
                                            {time}
                                        </button>
                                    {/each}
                                </div>
                            </div>
                        {/if}
                    </div>
                </div>
            </div>
        {/if}

        <div class="actions">
            {#if step > 1}
                <Button
                    category="secondary"
                    label="Back"
                    onClick={handleBack}
                />
            {/if}

            {#if step < 3}
                <Button
                    category="primary"
                    label="Next"
                    disabled={(step === 1 &&
                        !selectedCabinet &&
                        !selectedDoctor) ||
                        (step === 2 && !selectedCabinet && !selectedDoctor)}
                    onClick={handleNext}
                />
            {:else}
                <Button
                    category="primary"
                    Icon={CalendarIcon}
                    label="Book Appointment"
                    disabled={!selectedDate || !selectedTime}
                    onClick={handleSubmit}
                />
            {/if}
        </div>
    </div>
</Block>

<Button Icon={Compass} label="Explore Cabinets First" href="/cabinets"></Button>

<style>
    .booking-container {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    .progress-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 1rem;
    }

    .progress-step {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--text-muted);
        font-size: 0.875rem;
    }

    .progress-step.active {
        color: var(--color-primary);
    }

    .step-number {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: var(--background-secondary);
        border: 2px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.75rem;
        transition: all 0.3s ease;
    }

    .progress-step.active .step-number {
        background: var(--color-primary);
        border-color: var(--color-primary);
        color: white;
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
        padding: 1rem 0;
    }

    .search-type {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .type-btn {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.75rem;
        background: var(--background-secondary);
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
        color: var(--text-color);
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .type-btn:hover {
        background: var(--background-hover);
    }

    .type-btn.active {
        background: var(--color-primary);
        border-color: var(--color-primary);
        color: white;
    }

    .search-section {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .results-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1rem;
        max-height: 400px;
        overflow-y: auto;
        padding: 0.5rem;
        margin: 0 -0.5rem;
    }

    .result-card {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        padding: 1rem;
        background: var(--background-secondary);
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
        text-align: left;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .result-card:hover {
        border-color: var(--color-primary);
        transform: translateY(-2px);
    }

    .result-card.selected {
        background: var(--color-primary-alpha);
        border-color: var(--color-primary);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
    }

    .card-header h3 {
        margin: 0;
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text-color);
    }

    .location,
    .info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: var(--text-muted);
        margin: 0;
    }

    .status {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 1rem;
        font-weight: 500;
    }

    .status.open {
        background: #dcfce7;
        color: #166534;
    }

    .status.closed {
        background: #fee2e2;
        color: #991b1b;
    }

    .specialty {
        font-size: 0.75rem;
        color: var(--color-primary);
        background: var(--color-primary-alpha);
        padding: 0.25rem 0.5rem;
        border-radius: 1rem;
    }

    .price {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-color);
        margin: 0;
    }

    .selected-items {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    .selection-summary {
        display: flex;
        gap: 2rem;
        padding: 1.5rem;
        background: var(--background-secondary);
        border-radius: 0.75rem;
    }

    .summary-item {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .summary-item h4 {
        margin: 0 0 0.25rem;
        font-size: 0.875rem;
        color: var(--text-muted);
    }

    .summary-item p {
        margin: 0;
        font-size: 0.9rem;
        font-weight: 500;
        color: var(--text-color);
    }

    .datetime-picker {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .date-section,
    .time-section {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .date-section h4,
    .time-section h4 {
        margin: 0;
        font-size: 0.9rem;
        color: var(--text-color);
    }

    .time-slots {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
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

    .actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border-color);
    }

    @media (max-width: 768px) {
        .progress-bar {
            padding: 0;
        }

        .progress-step span {
            display: none;
        }

        .search-type {
            flex-direction: column;
        }

        .results-grid {
            grid-template-columns: 1fr;
        }

        .selection-summary {
            flex-direction: column;
            gap: 1rem;
        }

        .time-slots {
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
        }
    }

    /* Custom scrollbar for results */
    .results-grid::-webkit-scrollbar {
        width: 6px;
    }

    .results-grid::-webkit-scrollbar-track {
        background: var(--background-primary);
    }

    .results-grid::-webkit-scrollbar-thumb {
        background: var(--border-color);
        border-radius: 3px;
    }

    .results-grid::-webkit-scrollbar-thumb:hover {
        background: var(--text-muted);
    }
</style>
