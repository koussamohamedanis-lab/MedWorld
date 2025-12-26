<script lang="ts">
    import { onMount } from "svelte";
    import {
        Calendar,
        Clock,
        User,
        Building2,
        Stethoscope,
        X,
        Eye,
        AlertCircle,
        Loader,
    } from "@lucide/svelte";
    import Block from "$lib/components/ui/Block.svelte";
    import Button from "$lib/components/ui/Button.svelte";
    import Modal from "$lib/components/ui/Modal.svelte";
    import SearchInput from "$lib/components/ui/SearchInput.svelte";
    import { PatientAPI, AppointmentAPI } from "$lib/api";
    import type { Patient } from "$lib/types/users/patient";
    import type { Appointment } from "$lib/types/appointment";
    import { AppointmentStatus } from "$lib/types/appointment";

    interface IProps {
        patient: Patient;
    }

    let { patient }: IProps = $props();

    // State
    let appointments = $state<Appointment[]>([]);
    let filteredAppointments = $state<Appointment[]>([]);
    let searchTerm = $state("");
    let selectedStatus = $state<string>("all");
    let isLoading = $state(false);
    let isCanceling = $state(false);
    let errorMessage = $state("");
    let selectedAppointment = $state<Appointment | null>(null);
    let showDetailsModal = $state(false);
    let showCancelModal = $state(false);

    // Load appointments
    onMount(async () => {
        await loadAppointments();
    });

    async function loadAppointments() {
        isLoading = true;
        errorMessage = "";
        try {
            if (patient?.id) {
                appointments = await PatientAPI.getAppointments(patient.id);
                filterAppointments();
            }
        } catch (error: any) {
            console.error("Error loading appointments:", error);
            errorMessage = "Failed to load appointments";
        } finally {
            isLoading = false;
        }
    }

    function filterAppointments() {
        let filtered = appointments;

        // Filter by status
        if (selectedStatus !== "all") {
            filtered = filtered.filter(
                (a) => a.status === selectedStatus.toUpperCase(),
            );
        }

        // Filter by search term
        if (searchTerm) {
            const term = searchTerm.toLowerCase();
            filtered = filtered.filter(
                (a) =>
                    a.doctor.fullName.toLowerCase().includes(term) ||
                    a.cabinet.name.toLowerCase().includes(term) ||
                    a.patient.fullName.toLowerCase().includes(term),
            );
        }

        // Sort by date descending
        filtered.sort(
            (a, b) => new Date(b.date).getTime() - new Date(a.date).getTime(),
        );

        filteredAppointments = filtered;
    }

    function onSearchChange() {
        filterAppointments();
    }

    function onStatusChange() {
        filterAppointments();
    }

    function openDetails(appointment: Appointment) {
        selectedAppointment = appointment;
        showDetailsModal = true;
    }

    function closeDetails() {
        showDetailsModal = false;
        selectedAppointment = null;
    }

    function openCancelConfirm(appointment: Appointment) {
        selectedAppointment = appointment;
        showCancelModal = true;
    }

    function closeCancelConfirm() {
        showCancelModal = false;
    }

    async function confirmCancel() {
        if (!selectedAppointment?.id) return;

        isCanceling = true;
        errorMessage = "";

        try {
            await AppointmentAPI.updateStatus(
                selectedAppointment.id,
                AppointmentStatus.CANCELLED,
            );

            // Reload appointments
            await loadAppointments();
            showCancelModal = false;
            selectedAppointment = null;
        } catch (error: any) {
            console.error("Error canceling appointment:", error);
            errorMessage = "Failed to cancel appointment";
        } finally {
            isCanceling = false;
        }
    }

    function getStatusColor(status: string) {
        switch (status) {
            case AppointmentStatus.SCHEDULED:
                return "scheduled";
            case AppointmentStatus.CONFIRMED:
                return "confirmed";
            case AppointmentStatus.COMPLETED:
                return "completed";
            case AppointmentStatus.CANCELLED:
                return "cancelled";
            default:
                return "default";
        }
    }

    function formatDate(date: string | Date) {
        return new Date(date).toLocaleDateString("en-US", {
            year: "numeric",
            month: "long",
            day: "numeric",
            hour: "2-digit",
            minute: "2-digit",
        });
    }

    function canCancel(appointment: Appointment) {
        return (
            appointment.status === AppointmentStatus.SCHEDULED ||
            appointment.status === AppointmentStatus.CONFIRMED
        );
    }
</script>

<Block group="appointments" title="Your Appointments" Icon={Calendar}>
    {#if errorMessage}
        <div class="error-banner">
            <AlertCircle size={18} />
            <p>{errorMessage}</p>
        </div>
    {/if}

    {#if isLoading}
        <div class="loading">
            <Loader />
            <p>Loading appointments...</p>
        </div>
    {:else}
        <div class="appointments-header">
            <div class="search-filters">
                <SearchInput
                    placeholder="Search by doctor, cabinet..."
                    bind:value={searchTerm}
                    onchange={onSearchChange}
                />

                <select
                    bind:value={selectedStatus}
                    onchange={onStatusChange}
                    class="status-filter"
                >
                    <option value="all">All Appointments</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <div class="stats">
                <div class="stat">
                    <span class="label">Total:</span>
                    <span class="value">{appointments.length}</span>
                </div>
                <div class="stat">
                    <span class="label">Upcoming:</span>
                    <span class="value">
                        {appointments.filter(
                            (a) => new Date(a.date) > new Date(),
                        ).length}
                    </span>
                </div>
            </div>
        </div>

        {#if filteredAppointments.length === 0}
            <div class="empty-state">
                <Calendar size={48} />
                <h3>No Appointments</h3>
                <p>
                    {searchTerm || selectedStatus !== "all"
                        ? "No appointments match your filters"
                        : "You haven't booked any appointments yet"}
                </p>
            </div>
        {:else}
            <div class="appointments-list">
                {#each filteredAppointments as appointment (appointment.id)}
                    <div class="appointment-item">
                        <div class="appointment-main">
                            <div class="datetime">
                                <Calendar size={18} />
                                <div>
                                    <p class="date">
                                        {new Date(
                                            appointment.date,
                                        ).toLocaleDateString()}
                                    </p>
                                    <p class="time">
                                        {new Date(
                                            appointment.date,
                                        ).toLocaleTimeString("en-US", {
                                            hour: "2-digit",
                                            minute: "2-digit",
                                        })}
                                    </p>
                                </div>
                            </div>

                            <div class="doctor-info">
                                <h4>{appointment.doctor.fullName}</h4>
                                <p>{appointment.doctor.speciality}</p>
                            </div>

                            <div class="cabinet-info">
                                <p class="cabinet">
                                    <Building2 size={14} />
                                    {appointment.cabinet.name}
                                </p>
                                <p class="location">
                                    {appointment.cabinet.location?.address}
                                </p>
                            </div>

                            <div class="status-badge">
                                <span
                                    class={`status ${getStatusColor(appointment.status)}`}
                                >
                                    {appointment.status}
                                </span>
                            </div>
                        </div>

                        <div class="appointment-actions">
                            <button
                                class="action-btn"
                                title="View Details"
                                onclick={() => openDetails(appointment)}
                            >
                                <Eye size={18} />
                            </button>

                            {#if canCancel(appointment)}
                                <button
                                    class="action-btn cancel"
                                    title="Cancel Appointment"
                                    onclick={() =>
                                        openCancelConfirm(appointment)}
                                >
                                    <X size={18} />
                                </button>
                            {/if}
                        </div>
                    </div>
                {/each}
            </div>
        {/if}
    {/if}
</Block>

<!-- Details Modal -->
<Modal
    isOpen={showDetailsModal}
    onClose={closeDetails}
    title="Appointment Details"
>
    {#if selectedAppointment}
        <div class="modal-content">
            <div class="detail-group">
                <label>Date & Time</label>
                <p>{formatDate(selectedAppointment.date)}</p>
            </div>

            <div class="detail-group">
                <label>Doctor</label>
                <div>
                    <p class="name">{selectedAppointment.doctor.fullName}</p>
                    <p class="sub">{selectedAppointment.doctor.speciality}</p>
                </div>
            </div>

            <div class="detail-group">
                <label>Cabinet</label>
                <div>
                    <p class="name">{selectedAppointment.cabinet.name}</p>
                    <p class="sub">
                        {selectedAppointment.cabinet.location?.address}
                    </p>
                </div>
            </div>

            <div class="detail-group">
                <label>Status</label>
                <p
                    class={`status ${getStatusColor(selectedAppointment.status)}`}
                >
                    {selectedAppointment.status}
                </p>
            </div>

            {#if selectedAppointment.consultation}
                <div class="detail-group highlight">
                    <label>
                        <Stethoscope size={16} />
                        Consultation Notes
                    </label>
                    <p>{selectedAppointment.consultation.notes}</p>
                </div>
            {/if}

            <div class="modal-actions">
                <Button
                    category="secondary"
                    label="Close"
                    onClick={closeDetails}
                />
                {#if canCancel(selectedAppointment)}
                    <Button
                        category="primary"
                        label="Cancel Appointment"
                        onClick={() => {
                            closeDetails();
                            openCancelConfirm(selectedAppointment!);
                        }}
                    />
                {/if}
            </div>
        </div>
    {/if}
</Modal>

<!-- Cancel Confirmation Modal -->
<Modal
    isOpen={showCancelModal}
    onClose={closeCancelConfirm}
    title="Cancel Appointment"
>
    <div class="confirmation">
        <p>Are you sure you want to cancel this appointment?</p>
        {#if selectedAppointment}
            <div class="appointment-summary">
                <p>
                    <strong>{selectedAppointment.doctor.fullName}</strong>
                </p>
                <p>
                    {new Date(selectedAppointment.date).toLocaleDateString()} at
                    {new Date(selectedAppointment.date).toLocaleTimeString(
                        "en-US",
                        { hour: "2-digit", minute: "2-digit" },
                    )}
                </p>
            </div>
        {/if}

        <div class="modal-actions">
            <Button
                category="secondary"
                label="Keep Appointment"
                onClick={closeCancelConfirm}
                disabled={isCanceling}
            />
            <Button
                category="primary"
                label={isCanceling ? "Canceling..." : "Yes, Cancel"}
                onClick={confirmCancel}
                disabled={isCanceling}
            />
        </div>
    </div>
</Modal>

<style>
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

    .loading {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 3rem;
        gap: 1rem;
        color: var(--text-muted);
    }

    .appointments-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 2rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }

    .search-filters {
        display: flex;
        gap: 1rem;
        flex: 1;
        min-width: 300px;
    }

    .status-filter {
        padding: 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: 0.5rem;
        background: var(--background-primary);
        color: var(--text-color);
        font-size: 0.875rem;
        cursor: pointer;
    }

    .stats {
        display: flex;
        gap: 2rem;
    }

    .stat {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.25rem;
    }

    .stat .label {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .stat .value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--color-primary);
    }

    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 3rem 2rem;
        gap: 1rem;
        color: var(--text-muted);
    }

    .empty-state h3 {
        margin: 0;
        color: var(--text-color);
    }

    .empty-state p {
        margin: 0;
        max-width: 300px;
        text-align: center;
    }

    .appointments-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .appointment-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
        background: var(--background-secondary);
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
        gap: 1rem;
    }

    .appointment-item:hover {
        border-color: var(--color-primary);
        background: var(--background-hover);
    }

    .appointment-main {
        display: flex;
        align-items: center;
        gap: 2rem;
        flex: 1;
        min-width: 0;
    }

    .datetime {
        display: flex;
        align-items: center;
        gap: 1rem;
        min-width: 120px;
    }

    .datetime p {
        margin: 0;
        line-height: 1.4;
    }

    .date {
        font-weight: 600;
        color: var(--text-color);
    }

    .time {
        font-size: 0.875rem;
        color: var(--text-muted);
    }

    .doctor-info {
        min-width: 150px;
    }

    .doctor-info h4 {
        margin: 0 0 0.25rem;
        font-size: 0.95rem;
        color: var(--text-color);
    }

    .doctor-info p {
        margin: 0;
        font-size: 0.75rem;
        color: var(--color-primary);
    }

    .cabinet-info {
        flex: 1;
        min-width: 150px;
    }

    .cabinet-info p {
        margin: 0;
        font-size: 0.875rem;
        line-height: 1.4;
    }

    .cabinet {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
        color: var(--text-color);
    }

    .location {
        color: var(--text-muted);
        font-size: 0.75rem;
    }

    .status-badge {
        min-width: 100px;
    }

    .status {
        display: inline-block;
        padding: 0.5rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status.scheduled {
        background: #fef3c7;
        color: #92400e;
    }

    .status.confirmed {
        background: #dbeafe;
        color: #1e40af;
    }

    .status.completed {
        background: #dcfce7;
        color: #166534;
    }

    .status.cancelled {
        background: #f3f4f6;
        color: #6b7280;
    }

    .appointment-actions {
        display: flex;
        gap: 0.5rem;
    }

    .action-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border: 1px solid var(--border-color);
        border-radius: 0.5rem;
        background: var(--background-primary);
        color: var(--text-muted);
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .action-btn:hover {
        border-color: var(--color-primary);
        color: var(--color-primary);
    }

    .action-btn.cancel:hover {
        border-color: #dc2626;
        color: #dc2626;
    }

    .modal-content,
    .confirmation {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .detail-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        padding: 1rem;
        background: var(--background-secondary);
        border-radius: 0.5rem;
    }

    .detail-group.highlight {
        background: var(--color-primary-alpha);
        border: 1px solid var(--color-primary);
    }

    .detail-group label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .detail-group p {
        margin: 0;
        color: var(--text-color);
    }

    .detail-group .name {
        font-weight: 500;
    }

    .detail-group .sub {
        font-size: 0.85rem;
        color: var(--text-muted);
    }

    .appointment-summary {
        padding: 1rem;
        background: var(--background-secondary);
        border-radius: 0.5rem;
        border-left: 3px solid var(--color-primary);
    }

    .appointment-summary p {
        margin: 0;
        line-height: 1.6;
    }

    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border-color);
    }

    .confirmation p:first-child {
        color: var(--text-muted);
        margin-bottom: 0;
    }

    @media (max-width: 1024px) {
        .appointment-main {
            gap: 1rem;
        }

        .doctor-info,
        .cabinet-info {
            min-width: 120px;
        }
    }

    @media (max-width: 768px) {
        .appointments-header {
            flex-direction: column;
            align-items: stretch;
        }

        .search-filters {
            flex-direction: column;
        }

        .stats {
            width: 100%;
            justify-content: space-around;
        }

        .appointment-item {
            flex-direction: column;
            align-items: flex-start;
        }

        .appointment-main {
            width: 100%;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .cabinet-info {
            width: 100%;
        }

        .appointment-actions {
            width: 100%;
            justify-content: flex-end;
        }
    }
</style>
