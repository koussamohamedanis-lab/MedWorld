<script lang="ts">
    import { onMount } from "svelte";
    import {
        Stethoscope,
        Plus,
        FileText,
        Calendar,
        User,
        Search,
        Edit,
        Trash2,
        Eye,
        Pen,
    } from "@lucide/svelte";
    import Block from "$lib/components/ui/Block.svelte";
    import Button from "$lib/components/ui/Button.svelte";
    import SearchInput from "$lib/components/ui/SearchInput.svelte";
    import Avatar from "$lib/components/ui/Avatar.svelte";
    import IconButton from "$lib/components/ui/IconButton.svelte";
    import AddConsultation from "./AddConsultation.svelte";
    import type { Consultation } from "$lib/types/consultation";
    import type { Patient } from "$lib/types/users/patient";
    import type { Doctor } from "$lib/types/users/doctor";
    import type { User as UserType } from "$lib/types/users";
    import { Users } from "$lib/types/users";

    interface IProps {
        user: UserType<any>;
        patients: Patient[];
    }

    let { user, patients }: IProps = $props();

    let consultations: Consultation[] = $state([]);
    let searchQuery: string = $state("");
    let showAddModal: boolean = $state(false);
    let selectedPatient: Patient | null = $state(null);
    let selectedConsultation: Consultation | null = $state(null);
    let viewMode: "list" | "detail" = $state("list");

    onMount(async () => {
        // Load existing consultations
        // consultations = await DoctorAPI.getConsultations(user.id);
    });

    // Add Consultation
    function openAddConsultation(patient?: Patient) {
        selectedPatient = patient || patients[0];
        showAddModal = true;
    }

    function closeAddConsultation() {
        showAddModal = false;
        selectedPatient = null;
    }

    function handleSaveConsultation(
        consultationData: Omit<Consultation, "id" | "createdAt" | "updatedAt">,
    ) {
        const newConsultation: Consultation = {
            ...consultationData,
            id: Date.now(),
            createdAt: new Date(),
            updatedAt: new Date(),
        };

        consultations = [newConsultation, ...consultations];
        console.log("Consultation saved:", newConsultation);
        closeAddConsultation();
    }

    function viewConsultation(consultation: Consultation) {
        selectedConsultation = consultation;
        viewMode = "detail";
    }

    function closeConsultationDetail() {
        selectedConsultation = null;
        viewMode = "list";
    }

    function editConsultation(consultation: Consultation) {
        // For now, we'll just view it
        viewConsultation(consultation);
    }

    // Delete Consultation
    function deleteConsultation(consultation: Consultation) {
        if (
            confirm(
                `Are you sure you want to delete this consultation for ${consultation.patient.fullName}?`,
            )
        ) {
            consultations = consultations.filter(
                (c) => c.id !== consultation.id,
            );
        }
    }

    // Filter consultations based on ssearch
    let filteredConsultations = $derived(
        consultations.filter((consultation) => {
            if (!searchQuery) return true;

            const searchTerm = searchQuery.toLowerCase();
            return (
                consultation.patient
                    .fullName
                    .toLowerCase()
                    .includes(searchTerm) ||
                consultation.doctor
                    .fullName
                    .toLowerCase()
                    .includes(searchTerm) ||
                consultation.notes.toLowerCase().includes(searchTerm) ||
                consultation.prescriptions.some((prescription) =>
                    prescription.toLowerCase().includes(searchTerm),
                )
            );
        }),
    );

    // Format date for display
    function formatDate(date: Date): string {
        return new Date(date).toLocaleDateString("en-US", {
            year: "numeric",
            month: "short",
            day: "numeric",
            hour: "2-digit",
            minute: "2-digit",
        });
    }

    // Get preview of notes
    function getNotesPreview(notes: string): string {
        return notes.length > 100 ? notes.substring(0, 100) + "..." : notes;
    }
</script>

{#if viewMode === "detail" && selectedConsultation}
    <!-- Consultation Detail View -->
    <Block group="consultations" title="Consultation Details" Icon={FileText}>
        <div class="consultation-detail">
            <div class="detail-header">
                <div class="patient-info">
                    <Avatar
                        size="60px"
                        avatarUrl={selectedConsultation.patient.avatarUrl}
                        alt={selectedConsultation.patient.fullName}
                    />
                    <div class="patient-details">
                        <h3>{selectedConsultation.patient.fullName}</h3>
                        <div class="meta-info">
                            <span class="meta-item">
                                <Calendar size={16} />
                                {formatDate(selectedConsultation.createdAt)}
                            </span>
                            <span class="meta-item">
                                <User size={16} />
                                Dr. {selectedConsultation.doctor.fullName}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="detail-actions">
                    <Button
                        type="secondary"
                        size="small"
                        onclick={() => editConsultation(selectedConsultation!)}
                    >
                        <Pen size={16} />
                        Edit
                    </Button>
                    <Button
                        type="secondary"
                        size="small"
                        color="red"
                        onclick={() =>
                            deleteConsultation(selectedConsultation!)}
                    >
                        <Trash2 size={16} />
                        Delete
                    </Button>
                </div>
            </div>

            <div class="detail-content">
                <div class="detail-section">
                    <h4>Clinical Notes</h4>
                    <div class="notes-content">
                        {selectedConsultation.notes}
                    </div>
                </div>

                {#if selectedConsultation.prescriptions.length > 0}
                    <div class="detail-section">
                        <h4>Prescriptions</h4>
                        <ul class="prescriptions-list">
                            {#each selectedConsultation.prescriptions as prescription}
                                <li class="prescription-item">
                                    {prescription}
                                </li>
                            {/each}
                        </ul>
                    </div>
                {/if}

                {#if selectedConsultation.attachments && selectedConsultation.attachments.length > 0}
                    <div class="detail-section">
                        <h4>Attachments</h4>
                        <div class="attachments-list">
                            {#each selectedConsultation.attachments as attachment, index}
                                <div class="attachment-item">
                                    <FileText size={16} />
                                    <span class="attachment-name"
                                        >Attachment {index + 1}</span
                                    >
                                    <Button type="secondary" size="small"
                                        >Download</Button
                                    >
                                </div>
                            {/each}
                        </div>
                    </div>
                {/if}
            </div>
        </div>
    </Block>
{:else}
    <!-- Consultation List View -->
    <Block group="consultations" title="Manage Consultations" Icon={FileText}>
        <div class="consultations-header">
            <SearchInput
                bind:value={searchQuery}
                placeholder="Search consultations by patient, notes, or prescriptions..."
            />

            <div class="consultations-stats">
                <span class="stat"
                    >{consultations.length} total consultations</span
                >
                {#if filteredConsultations.length !== consultations.length}
                    <span class="stat"
                        >({filteredConsultations.length} filtered)</span
                    >
                {/if}
            </div>
        </div>

        {#if consultations.length === 0}
            <div class="empty-state">
                <FileText size={48} />
                <h3>No Consultations Yet</h3>
                <p>Start by adding a consultation for one of your patients.</p>
                {#if patients.length > 0}
                    <Button Icon={Plus} onclick={() => openAddConsultation()}>
                        Create First Consultation
                    </Button>
                {:else}
                    <p class="no-patients">
                        No patients available for consultation.
                    </p>
                {/if}
            </div>
        {:else if filteredConsultations.length === 0}
            <div class="empty-state">
                <Search size={48} />
                <h3>No Matching Consultations</h3>
                <p>Try adjusting your search criteria.</p>
            </div>
        {:else}
            <div class="consultations-table">
                <table>
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th class="desktop">Date</th>
                            <th>Notes Preview</th>
                            <th class="desktop">Prescriptions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {#each filteredConsultations as consultation}
                            <tr>
                                <td>
                                    <div class="patient-cell">
                                        <Avatar
                                            size="36px"
                                            avatarUrl={consultation.patient
                                                .avatarUrl}
                                            alt={consultation.patient.fullName}
                                        />
                                        <span class="patient-name"
                                            >{consultation.patient.fullName}</span
                                        >
                                    </div>
                                </td>
                                <td class="desktop">
                                    {formatDate(consultation.createdAt)}
                                </td>
                                <td class="notes-preview">
                                    {getNotesPreview(consultation.notes)}
                                </td>
                                <td class="desktop">
                                    {#if consultation.prescriptions.length > 0}
                                        <span class="prescription-count">
                                            {consultation.prescriptions.length} prescription(s)
                                        </span>
                                    {:else}
                                        <span class="no-prescriptions"
                                            >None</span
                                        >
                                    {/if}
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <IconButton
                                            Icon={Eye}
                                            label="View Consultation"
                                            onclick={() =>
                                                viewConsultation(consultation)}
                                        />
                                        <IconButton
                                            Icon={Edit}
                                            type="secondary"
                                            color="gray"
                                            label="Edit Consultation"
                                            onclick={() =>
                                                editConsultation(consultation)}
                                        />
                                        <IconButton
                                            Icon={Trash2}
                                            type="secondary"
                                            color="red"
                                            label="Delete Consultation"
                                            onclick={() =>
                                                deleteConsultation(
                                                    consultation,
                                                )}
                                        />
                                    </div>
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>
        {/if}
    </Block>
{/if}

{#if selectedPatient && selectedConsultation && showAddModal}
    <AddConsultation
        currentAppointment={selectedConsultation.appointment}
        patient={selectedPatient}
        currentDoctor={user as Doctor}
        onSave={handleSaveConsultation}
    />
{/if}

<style>
    .consultations-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        gap: 1rem;
    }

    .consultations-stats {
        display: flex;
        gap: 1rem;
        font-size: 0.875rem;
        color: #6b7280;
    }

    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
        color: #6b7280;
    }

    .empty-state h3 {
        margin: 1rem 0 0.5rem 0;
        color: #374151;
    }

    .empty-state p {
        margin-bottom: 1.5rem;
    }

    .no-patients {
        color: #ef4444;
        font-style: italic;
    }

    /* Table Styles */
    .consultations-table {
        overflow-x: auto;
    }

    .patient-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .patient-name {
        font-weight: 500;
    }

    .notes-preview {
        max-width: 300px;
        color: #6b7280;
        font-size: 0.875rem;
    }

    .prescription-count {
        background: #dbeafe;
        color: #1e40af;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
    }

    .no-prescriptions {
        color: #9ca3af;
        font-style: italic;
    }

    .table-actions {
        display: flex;
        gap: 0.25rem;
    }

    /* Detail View Styles */
    .consultation-detail {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    .detail-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .patient-info {
        display: flex;
        gap: 1rem;
        align-items: flex-start;
    }

    .patient-details h3 {
        margin: 0 0 0.5rem 0;
        font-size: 1.5rem;
        font-weight: 600;
        color: #111827;
    }

    .meta-info {
        display: flex;
        gap: 1.5rem;
        font-size: 0.875rem;
        color: #6b7280;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .detail-actions {
        display: flex;
        gap: 0.5rem;
    }

    .detail-content {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    .detail-section h4 {
        margin: 0 0 1rem 0;
        font-size: 1.125rem;
        font-weight: 600;
        color: #374151;
    }

    .notes-content {
        background: #f8fafc;
        padding: 1.5rem;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        line-height: 1.6;
        white-space: pre-wrap;
    }

    .prescriptions-list {
        margin: 0;
        padding-left: 1.5rem;
    }

    .prescription-item {
        color: #374151;
        margin-bottom: 0.5rem;
        line-height: 1.5;
    }

    .attachments-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .attachment-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
    }

    .attachment-name {
        flex: 1;
        font-weight: 500;
        color: #374151;
    }

    @media (max-width: 768px) {
        .consultations-header {
            flex-direction: column;
            align-items: stretch;
        }

        .consultations-stats {
            justify-content: center;
        }

        .detail-header {
            flex-direction: column;
            gap: 1rem;
        }

        .detail-actions {
            align-self: stretch;
            justify-content: flex-end;
        }

        .meta-info {
            flex-direction: column;
            gap: 0.5rem;
        }

        .table-actions {
            flex-direction: column;
        }

        .desktop {
            display: none;
        }
    }
</style>
