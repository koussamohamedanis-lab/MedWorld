<script lang="ts">
    import { onMount } from "svelte";
    import {
        User as UserIcon,
        Mail,
        Phone,
        MapPin,
        Heart,
        AlertCircle,
        Loader,
        Edit,
        Save,
        X,
    } from "@lucide/svelte";
    import Block from "$lib/components/ui/Block.svelte";
    import Button from "$lib/components/ui/Button.svelte";
    import Input from "$lib/components/ui/Input.svelte";
    import { PatientAPI, UserAPI } from "$lib/api";
    import type { Patient } from "$lib/types/users/patient";
    import type { User } from "$lib/types/users";

    interface IProps {
        patient: Patient;
    }

    let { patient }: IProps = $props();

    // State
    let isEditing = $state(false);
    let isLoading = $state(false);
    let isSaving = $state(false);
    let errorMessage = $state("");
    let successMessage = $state("");

    let patientData = $state<Patient | null>(null);
    let userData = $state<User<any> | null>(null);

    let formData = $state({
        firstName: "",
        lastName: "",
        email: "",
        phoneNumber: "",
        address: "",
        bloodType: "",
        medicalHistory: "",
        allergies: "",
        emergencyContact: "",
    });

    onMount(async () => {
        await loadPatientData();
    });

    async function loadPatientData() {
        isLoading = true;
        errorMessage = "";
        try {
            if (patient?.id) {
                patientData = await PatientAPI.getById(patient.id);
                if (patientData) {
                    initializeForm();
                }
            }
        } catch (error: any) {
            console.error("Error loading patient data:", error);
            errorMessage = "Failed to load patient information";
        } finally {
            isLoading = false;
        }
    }

    function initializeForm() {
        if (!patientData) return;

        formData = {
            firstName: patientData.firstName || "",
            lastName: patientData.lastName || "",
            email: patientData.email || "",
            phoneNumber: patientData.phoneNumber || "",
            address: patientData.address || "",
            bloodType: patientData.bloodType || "",
            medicalHistory: patientData.medicalHistory.join(", ") || "",
            allergies: patientData.allergies?.join(", ") || "",
            emergencyContact: patientData.emergencyContact || "",
        };
    }

    function startEditing() {
        isEditing = true;
        errorMessage = "";
    }

    function cancelEditing() {
        isEditing = false;
        initializeForm();
    }

    async function saveChanges() {
        errorMessage = "";
        successMessage = "";

        if (!formData.firstName || !formData.lastName) {
            errorMessage = "First name and last name are required";
            return;
        }

        isSaving = true;

        try {
            if (patientData?.id) {
                const updatedPatient = await PatientAPI.getById(patientData.id);
                if (updatedPatient) {
                    patientData = updatedPatient;
                    successMessage = "Profile updated successfully!";
                    isEditing = false;
                    setTimeout(() => {
                        successMessage = "";
                    }, 3000);
                }
            }
        } catch (error: any) {
            console.error("Error saving profile:", error);
            errorMessage = error.message || "Failed to save profile changes";
        } finally {
            isSaving = false;
        }
    }

    function getAgeGroup() {
        if (!patientData?.dateOfBirth) return "Not specified";
        const age =
            new Date().getFullYear() -
            new Date(patientData.dateOfBirth).getFullYear();
        if (age < 18) return "Pediatric";
        if (age < 65) return "Adult";
        return "Senior";
    }

    function getBloodTypeColor(bloodType?: string) {
        switch (bloodType) {
            case "O+":
            case "O-":
                return "o-type";
            case "A+":
            case "A-":
                return "a-type";
            case "B+":
            case "B-":
                return "b-type";
            case "AB+":
            case "AB-":
                return "ab-type";
            default:
                return "default";
        }
    }
</script>

<Block group="any" title="Patient Profile" Icon={UserIcon}>
    {#if errorMessage}
        <div class="error-banner">
            <AlertCircle size={18} />
            <p>{errorMessage}</p>
        </div>
    {/if}

    {#if successMessage}
        <div class="success-banner">
            <p>✓ {successMessage}</p>
        </div>
    {/if}

    {#if isLoading}
        <div class="loading">
            <Loader />
            <p>Loading profile...</p>
        </div>
    {:else if patientData}
        {#if isEditing}
            <!-- Edit Mode -->
            <div class="edit-form">
                <div class="form-section">
                    <h3>Personal Information</h3>
                    <div class="form-grid">
                        <Input
                            label="First Name"
                            bind:value={formData.firstName}
                            showLabel
                        />
                        <Input
                            label="Last Name"
                            bind:value={formData.lastName}
                            showLabel
                        />
                        <Input
                            label="Email"
                            type="email"
                            bind:value={formData.email}
                            showLabel
                            disabled
                        />
                        <Input
                            label="Phone Number"
                            type="tel"
                            bind:value={formData.phoneNumber}
                            showLabel
                        />
                        <Input
                            label="Address"
                            bind:value={formData.address}
                            showLabel
                        />
                    </div>
                </div>

                <div class="form-section">
                    <h3>Medical Information</h3>
                    <div class="form-grid">
                        <Input
                            label="Blood Type"
                            bind:value={formData.bloodType}
                            showLabel
                        />
                        <Input
                            label="Emergency Contact"
                            bind:value={formData.emergencyContact}
                            showLabel
                        />
                    </div>
                    <Input
                        category="textarea"
                        label="Medical History"
                        bind:value={formData.medicalHistory}
                        showLabel
                        rows={4}
                    />
                    <Input
                        category="textarea"
                        label="Allergies"
                        bind:value={formData.allergies}
                        showLabel
                        rows={3}
                    />
                </div>

                <div class="form-actions">
                    <Button
                        category="secondary"
                        label="Cancel"
                        Icon={X}
                        onClick={cancelEditing}
                        disabled={isSaving}
                    />
                    <Button
                        category="primary"
                        label={isSaving ? "Saving..." : "Save Changes"}
                        Icon={isSaving ? Loader : Save}
                        onClick={saveChanges}
                        disabled={isSaving}
                    />
                </div>
            </div>
        {:else}
            <!-- View Mode -->
            <div class="profile-view">
                <!-- Profile Header -->
                <div class="profile-header">
                    <div class="avatar">
                        <UserIcon size={48} />
                    </div>
                    <div class="profile-info">
                        <h2>{patientData.firstName} {patientData.lastName}</h2>
                        <p class="email">{patientData.email}</p>
                        <p class="age-group">{getAgeGroup()}</p>
                    </div>
                    <Button
                        category="secondary"
                        label="Edit Profile"
                        Icon={Edit}
                        onClick={startEditing}
                    />
                </div>

                <!-- Contact Information -->
                <div class="info-section">
                    <h3>Contact Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-icon">
                                <Mail size={18} />
                            </div>
                            <div>
                                <label>Email</label>
                                <p>{patientData.email || "Not provided"}</p>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon">
                                <Phone size={18} />
                            </div>
                            <div>
                                <label>Phone Number</label>
                                <p>
                                    {patientData.phoneNumber || "Not provided"}
                                </p>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon">
                                <MapPin size={18} />
                            </div>
                            <div>
                                <label>Address</label>
                                <p>{patientData.address || "Not provided"}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Medical Information -->
                <div class="info-section">
                    <h3>Medical Information</h3>

                    <div class="medical-badges">
                        {#if patientData.bloodType}
                            <div
                                class={`badge blood-type ${getBloodTypeColor(patientData.bloodType)}`}
                            >
                                <Heart size={16} />
                                <span>Blood Type: {patientData.bloodType}</span>
                            </div>
                        {/if}

                        {#if patientData.emergencyContact}
                            <div class="badge emergency">
                                <AlertCircle size={16} />
                                <span
                                    >Emergency: {patientData.emergencyContact}</span
                                >
                            </div>
                        {/if}
                    </div>

                    {#if patientData.medicalHistory}
                        <div class="info-item">
                            <label>Medical History</label>
                            <p class="text-block">
                                {patientData.medicalHistory}
                            </p>
                        </div>
                    {/if}

                    {#if patientData.allergies}
                        <div class="info-item">
                            <label>Allergies</label>
                            <p class="text-block alert">
                                {patientData.allergies}
                            </p>
                        </div>
                    {/if}
                </div>

                <!-- Stats -->
                {#if patientData.appointments}
                    <div class="stats-section">
                        <div class="stat-card">
                            <span class="stat-number"
                                >{patientData.appointments.length}</span
                            >
                            <span class="stat-label">Appointments</span>
                        </div>
                        <div class="stat-card">
                            <span class="stat-number"
                                >{patientData.consultations?.length || 0}</span
                            >
                            <span class="stat-label">Consultations</span>
                        </div>
                    </div>
                {/if}
            </div>
        {/if}
    {/if}
</Block>

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

    .success-banner {
        padding: 1rem;
        background: #dcfce7;
        border: 1px solid #86efac;
        border-radius: 0.75rem;
        color: #166534;
        margin-bottom: 1.5rem;
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

    /* Edit Mode */
    .edit-form {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    .form-section {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .form-section h3 {
        margin: 0 0 1rem;
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-color);
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border-color);
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid var(--border-color);
    }

    /* View Mode */
    .profile-view {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    .profile-header {
        display: flex;
        align-items: center;
        gap: 2rem;
        padding: 2rem;
        background: linear-gradient(
            135deg,
            var(--color-primary-alpha),
            var(--background-secondary)
        );
        border-radius: 0.75rem;
        border: 1px solid var(--border-color);
    }

    .avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: var(--color-primary);
        color: white;
        flex-shrink: 0;
    }

    .profile-info {
        flex: 1;
    }

    .profile-info h2 {
        margin: 0 0 0.5rem;
        font-size: 1.5rem;
        color: var(--text-color);
    }

    .profile-info .email {
        margin: 0 0 0.25rem;
        color: var(--text-muted);
    }

    .profile-info .age-group {
        margin: 0;
        font-size: 0.875rem;
        color: var(--color-primary);
        font-weight: 500;
    }

    .info-section {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        padding: 1.5rem;
        background: var(--background-secondary);
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
    }

    .info-section h3 {
        margin: 0 0 1rem;
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-color);
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--border-color);
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .info-item {
        display: flex;
        gap: 1rem;
    }

    .info-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--color-primary-alpha);
        color: var(--color-primary);
        flex-shrink: 0;
    }

    .info-item label {
        display: block;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-muted);
        margin: 0 0 0.25rem;
    }

    .info-item p {
        margin: 0;
        color: var(--text-color);
        line-height: 1.5;
    }

    .text-block {
        white-space: pre-wrap;
        word-break: break-word;
    }

    .text-block.alert {
        color: #dc2626;
        background: #fee2e2;
        padding: 0.75rem;
        border-radius: 0.5rem;
        border-left: 3px solid #dc2626;
    }

    .medical-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .badge {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .badge.blood-type {
        background: var(--color-primary-alpha);
        color: var(--color-primary);
    }

    .badge.blood-type.o-type {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge.blood-type.a-type {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge.blood-type.b-type {
        background: #fce7f3;
        color: #be185d;
    }

    .badge.blood-type.ab-type {
        background: #e9d5ff;
        color: #6b21a8;
    }

    .badge.emergency {
        background: #fee2e2;
        color: #991b1b;
    }

    .stats-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
    }

    .stat-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
        background: var(--background-secondary);
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
        gap: 0.5rem;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: var(--color-primary);
    }

    .stat-label {
        font-size: 0.875rem;
        color: var(--text-muted);
        text-align: center;
    }

    @media (max-width: 768px) {
        .profile-header {
            flex-direction: column;
            text-align: center;
        }

        .profile-info {
            width: 100%;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }

        .stats-section {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>
