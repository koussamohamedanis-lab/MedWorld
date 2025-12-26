<script lang="ts">
    import { onMount } from "svelte";
    import {
        Stethoscope,
        FileText,
        User,
        Calendar,
        AlertCircle,
        Loader,
        Eye,
    } from "@lucide/svelte";
    import Block from "$lib/components/ui/Block.svelte";
    import Modal from "$lib/components/ui/Modal.svelte";
    import Button from "$lib/components/ui/Button.svelte";
    import SearchInput from "$lib/components/ui/SearchInput.svelte";
    import { PatientAPI } from "$lib/api";
    import type { Patient } from "$lib/types/users/patient";
    import type { Consultation } from "$lib/types/consultation";

    interface IProps {
        patient: Patient;
    }

    let { patient }: IProps = $props();

    // State
    let consultations = $state<Consultation[]>([]);
    let filteredConsultations = $state<Consultation[]>([]);
    let searchTerm = $state("");
    let isLoading = $state(false);
    let errorMessage = $state("");
    let selectedConsultation = $state<Consultation | null>(null);
    let showDetailsModal = $state(false);

    // Load consultations
    onMount(async () => {
        await loadConsultations();
    });

    async function loadConsultations() {
        isLoading = true;
        errorMessage = "";
        try {
            if (patient?.id) {
                consultations = await PatientAPI.getConsultations(patient.id);
                filterConsultations();
            }
        } catch (error: any) {
            console.error("Error loading consultations:", error);
            errorMessage = "Failed to load consultations";
        } finally {
            isLoading = false;
        }
    }

    function filterConsultations() {
        let filtered = consultations;

        if (searchTerm) {
            const term = searchTerm.toLowerCase();
            filtered = filtered.filter(
                (c) =>
                    c.doctor.fullName.toLowerCase().includes(term) ||
                    c.notes.toLowerCase().includes(term),
            );
        }

        // Sort by date descending
        filtered.sort(
            (a, b) =>
                new Date(b.appointment.date).getTime() -
                new Date(a.appointment.date).getTime(),
        );

        filteredConsultations = filtered;
    }

    function onSearchChange() {
        filterConsultations();
    }

    function openDetails(consultation: Consultation) {
        selectedConsultation = consultation;
        showDetailsModal = true;
    }

    function closeDetails() {
        showDetailsModal = false;
        selectedConsultation = null;
    }

    function formatDate(date: string | Date) {
        return new Date(date).toLocaleDateString("en-US", {
            year: "numeric",
            month: "long",
            day: "numeric",
        });
    }
</script>

<Block group="consultations" title="Consultation History" Icon={Stethoscope}>
    {#if errorMessage}
        <div class="error-banner">
            <AlertCircle size={18} />
            <p>{errorMessage}</p>
        </div>
    {/if}

    {#if isLoading}
        <div class="loading">
            <Loader />
            <p>Loading consultations...</p>
        </div>
    {:else}
        <div class="consultations-header">
            <SearchInput
                placeholder="Search by doctor or notes..."
                bind:value={searchTerm}
                onchange={onSearchChange}
            />

            <div class="stat">
                <span class="label">Total Consultations:</span>
                <span class="value">{consultations.length}</span>
            </div>
        </div>

        {#if filteredConsultations.length === 0}
            <div class="empty-state">
                <Stethoscope size={48} />
                <h3>No Consultations</h3>
                <p>
                    {searchTerm
                        ? "No consultations match your search"
                        : "You haven't had any consultations yet"}
                </p>
            </div>
        {:else}
            <div class="consultations-list">
                {#each filteredConsultations as consultation (consultation.id)}
                    <div class="consultation-card">
                        <div class="card-header">
                            <div class="doctor-section">
                                <h4>{consultation.doctor.fullName}</h4>
                                <p class="specialty">
                                    {consultation.doctor.speciality}
                                </p>
                            </div>
                            <div class="date-section">
                                <Calendar size={16} />
                                <p>
                                    {formatDate(consultation.appointment.date)}
                                </p>
                            </div>
                        </div>

                        <div class="card-content">
                            <p class="notes-preview">
                                {consultation.notes.length > 150
                                    ? consultation.notes.substring(0, 150) +
                                      "..."
                                    : consultation.notes}
                            </p>

                            {#if consultation.prescriptions && consultation.prescriptions.length > 0}
                                <div class="prescriptions">
                                    <span class="label">
                                        <FileText size={14} />
                                        Prescriptions: {consultation
                                            .prescriptions.length}
                                    </span>
                                </div>
                            {/if}
                        </div>

                        <div class="card-footer">
                            <button
                                class="view-btn"
                                onclick={() => openDetails(consultation)}
                            >
                                <Eye size={16} />
                                View Details
                            </button>
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
    title="Consultation Details"
>
    {#if selectedConsultation}
        <div class="modal-content">
            <div class="detail-group">
                <label>Doctor</label>
                <div class="doctor-detail">
                    <h4>{selectedConsultation.doctor.fullName}</h4>
                    <p>{selectedConsultation.doctor.speciality}</p>
                </div>
            </div>

            <div class="detail-group">
                <label>Date</label>
                <p>{formatDate(selectedConsultation.appointment.date)}</p>
            </div>

            <div class="detail-group">
                <label>Cabinet</label>
                <p>{selectedConsultation.appointment.cabinet.name}</p>
            </div>

            <div class="detail-group highlight">
                <label>Clinical Notes</label>
                <p class="notes">{selectedConsultation.notes}</p>
            </div>

            {#if selectedConsultation.prescriptions && selectedConsultation.prescriptions.length > 0}
                <div class="detail-group highlight">
                    <label>
                        <FileText size={16} />
                        Prescriptions
                    </label>
                    <ul class="prescriptions-list">
                        {#each selectedConsultation.prescriptions as prescription}
                            <li>{prescription}</li>
                        {/each}
                    </ul>
                </div>
            {/if}

            <div class="modal-actions">
                <Button
                    category="secondary"
                    label="Close"
                    onClick={closeDetails}
                />
            </div>
        </div>
    {/if}
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

    .consultations-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 2rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
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

    .consultations-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 1.5rem;
    }

    .consultation-card {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        padding: 1.5rem;
        background: var(--background-secondary);
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
        transition: all 0.2s ease;
    }

    .consultation-card:hover {
        border-color: var(--color-primary);
        box-shadow: 0 4px 12px rgba(var(--color-primary-rgb), 0.1);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
    }

    .doctor-section h4 {
        margin: 0 0 0.25rem;
        font-size: 0.95rem;
        color: var(--text-color);
    }

    .specialty {
        margin: 0;
        font-size: 0.75rem;
        color: var(--color-primary);
    }

    .date-section {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: var(--text-muted);
        white-space: nowrap;
    }

    .card-content {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        flex: 1;
    }

    .notes-preview {
        margin: 0;
        font-size: 0.875rem;
        color: var(--text-color);
        line-height: 1.5;
    }

    .prescriptions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem;
        background: var(--color-primary-alpha);
        border-radius: 0.5rem;
        font-size: 0.875rem;
    }

    .prescriptions .label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--color-primary);
        margin: 0;
    }

    .card-footer {
        display: flex;
        justify-content: center;
        padding-top: 0.75rem;
        border-top: 1px solid var(--border-color);
    }

    .view-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border: none;
        background: var(--color-primary);
        color: white;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .view-btn:hover {
        background: var(--color-primary-dark);
    }

    .modal-content {
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
        line-height: 1.6;
    }

    .doctor-detail h4 {
        margin: 0 0 0.25rem;
        font-size: 0.95rem;
        color: var(--text-color);
    }

    .doctor-detail p {
        margin: 0;
        font-size: 0.85rem;
        color: var(--text-muted);
    }

    .notes {
        white-space: pre-wrap;
        word-break: break-word;
    }

    .prescriptions-list {
        margin: 0;
        padding-left: 1.5rem;
    }

    .prescriptions-list li {
        color: var(--text-color);
        margin: 0.5rem 0;
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

    @media (max-width: 768px) {
        .consultations-header {
            flex-direction: column;
            align-items: stretch;
        }

        .consultations-list {
            grid-template-columns: 1fr;
        }
    }
</style>
