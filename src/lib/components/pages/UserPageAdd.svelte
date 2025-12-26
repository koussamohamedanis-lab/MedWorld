<script lang="ts">
    import { page } from "$app/state";
    import { getTypeFromString, Users } from "$lib/types/users";
    import { Image } from "@lucide/svelte";
    import { onMount } from "svelte";
    import { CabinetAPI } from "$lib/api";
    import { Specialities } from "$lib/types/speciality";

    import View from "$lib/components/ui/View.svelte";
    import Input from "$lib/components/ui/Input.svelte";
    import Button from "$lib/components/ui/Button.svelte";
    import InputArray from "$lib/components/ui/InputArray.svelte";
    import {
        validate,
        validation,
        type Fillable,
        extract,
    } from "$lib/validation";

    import {
        bloodTypes,
        type BloodType,
        type Patient,
    } from "$lib/types/users/patient";
    import type { Doctor } from "$lib/types/users/doctor";
    import type {
        IDoctor,
        IUser,
        IMixedUser,
        User,
        UserType,
    } from "$lib/types/users";

    let userType: Users = $state(Users.Patient);
    let cabinetID: number | null = $state(null);

    onMount(() => {
        let params = new URLSearchParams(location.search);

        cabinetID = Number(params.get("cabinet_id") || "") || null;
        userType =
            getTypeFromString((params.get("user_type") || params.get("type")) as UserType) ||
            Users.Patient;
    });

    let isLoading = $state(false);
    let errorMessage = $state("");
    let avatarUrl = $state("");

    // Form data using Fillable pattern with validators
    let formData: Fillable = $state({
        firstName: {
            value: "",
            error: "",
            validator: validation.name,
        },
        lastName: {
            value: "",
            error: "",
            validator: validation.name,
        },
        email: {
            value: "",
            error: "",
            validator: validation.email,
        },
        password: {
            value: "",
            error: "",
            validator: validation.password,
        },
        phoneNumber: {
            value: "",
            error: "",
            validator: validation.phoneNumber,
        },
        address: {
            value: "",
            error: "",
            validator: validation.notEmpty,
        },
        dateOfBirth: {
            value: new Date().toISOString().split("T")[0],
            error: "",
            validator: validation.pastDate,
        },
        gender: {
            value: "",
            error: "",
            validator: validation.notEmpty,
        },
        speciality: {
            value: "",
            error: "",
            validator: validation.notEmpty,
        },
        licenseNumber: {
            value: "",
            error: "",
            validator: validation.notEmpty,
        },
        yearsOfExperience: {
            value: "",
            error: "",
            validator: validation.number,
        },
        bloodType: {
            value: "",
            error: "",
            validator: validation.notEmpty,
        },
        emergencyContact: {
            value: "",
            error: "",
            validator: validation.phoneNumber,
        },
        allergies: {
            value: "",
            error: "",
            validator: validation.nothing,
        },
        medicalHistory: {
            value: "",
            error: "",
            validator: validation.nothing,
        },
    });

    function handleFileChange(e: Event) {
        const target = e.target as HTMLInputElement;
        const file = target.files?.[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = () => {
                avatarUrl = reader.result as string;
            };
            reader.readAsDataURL(file);
        }
    }

    function validateField(fieldName: keyof typeof formData) {
        const field = formData[fieldName];
        const validationError = field.validator
            ? field.validator(String(field.value ?? ""))
            : "";
        field.error = validationError;
    }

    async function handleSubmit() {
        try {
            // Validate required fields before submission
            validateField("firstName");
            validateField("lastName");
            validateField("email");
            validateField("password");
            validateField("phoneNumber");

            // Check if any field has error
            const hasErrors = Object.keys(formData).some(
                (key) => formData[key as keyof typeof formData].error !== "",
            );

            if (hasErrors) {
                errorMessage = "Please correct the errors in the form.";
                return;
            }

            isLoading = true;
            errorMessage = "";

            // Extract clean data from Fillable
            const cleanData = extract(formData) as any;
            const submissionData = {
                ...cleanData,
                avatarUrl,
            };

            if (userType === Users.Doctor && cabinetID) {
                const yearsRaw = submissionData.yearsOfExperience;
                const yearsNum = yearsRaw === undefined || yearsRaw === null || yearsRaw === "" ? NaN : Number(yearsRaw);

                await CabinetAPI.createDoctor(cabinetID, {
                    firstName: submissionData.firstName,
                    lastName: submissionData.lastName,
                    email: submissionData.email,
                    password: submissionData.password,
                    phoneNumber: submissionData.phoneNumber,
                    address: submissionData.address,
                    gender: submissionData.gender,
                    speciality: submissionData.speciality,
                    licenseNumber: submissionData.licenseNumber,
                    consultationPrice: submissionData.consultationPrice,
                    consultationDuration: submissionData.consultationDuration,
                    yearsOfExperience: Number.isFinite(yearsNum) ? yearsNum : undefined,
                });
                alert("Doctor created successfully");
                return;
            }

            alert("Unsupported creation flow");
        } catch (error: any) {
            errorMessage = error.message || "An error occurred while saving.";
        } finally {
            isLoading = false;
        }
    }
</script>

<View>
    <main>
        <div class="avatar">
            <div class="avatar-wrapper">
                <input
                    type="file"
                    accept="image/*"
                    class="avatar-input"
                    onchange={handleFileChange}
                />

                <div class="profile-picture">
                    <input
                        type="file"
                        accept="image/*"
                        class="avatar-input"
                        onchange={handleFileChange}
                    />
                    <img
                        src={avatarUrl ||
                            "https://media.istockphoto.com/id/1341046662/vector/picture-profile-icon-human-or-people-sign-and-symbol-for-template-design.jpg?s=612x612&w=0&k=20&c=A7z3OK0fElK3tFntKObma-3a7PyO8_2xxW0jtmjzT78="}
                        alt="Profile"
                        class="avatar-image"
                    />

                    <div class="avatar-overlay">
                        <Image size="24" />
                        <span>Upload New Image</span>
                    </div>
                </div>
            </div>
        </div>

        <section>
            {#if isLoading}
                <p>Loading...</p>
            {:else if errorMessage}
                <p class="error">{errorMessage}</p>
            {:else}
                <form
                    onsubmit={(e) => {
                        e.preventDefault();
                        handleSubmit();
                    }}
                >
                    <div class="form-section">
                        <h3>Basic Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <Input
                                    label="First Name"
                                    showLabel
                                    type="text"
                                    bind:value={formData.firstName.value}
                                    bind:error={formData.firstName.error}
                                    validation={formData.firstName.validator}
                                    required
                                />
                            </div>

                            <div class="form-group">
                                <Input
                                    label="Last Name"
                                    showLabel
                                    type="text"
                                    bind:value={formData.lastName.value}
                                    bind:error={formData.lastName.error}
                                    validation={formData.lastName.validator}
                                    required
                                />
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <Input
                                    label="Email"
                                    showLabel
                                    type="email"
                                    bind:value={formData.email.value}
                                    bind:error={formData.email.error}
                                    validation={formData.email.validator}
                                    required
                                />
                            </div>

                            <div class="form-group">
                                <Input
                                    label="Password"
                                    showLabel
                                    type="password"
                                    bind:value={formData.password.value}
                                    bind:error={formData.password.error}
                                    validation={formData.password.validator}
                                    required
                                />
                            </div>

                            <div class="form-group">
                                <Input
                                    label="Phone Number"
                                    showLabel
                                    type="tel"
                                    bind:value={formData.phoneNumber.value}
                                    bind:error={formData.phoneNumber.error}
                                    validation={formData.phoneNumber.validator}
                                />
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <Input
                                    label="Date of Birth"
                                    showLabel
                                    type="date"
                                    bind:value={formData.dateOfBirth.value}
                                    bind:error={formData.dateOfBirth.error}
                                    validation={formData.dateOfBirth.validator}
                                />
                            </div>

                            <div class="form-group">
                                <Input
                                    category="select"
                                    label="Select Gender"
                                    bind:value={formData.gender.value}
                                    bind:error={formData.gender.error}
                                    validation={formData.gender.validator}
                                    options={[
                                        { value: "male", label: "Male" },
                                        { value: "female", label: "Female" },
                                    ]}
                                    placeholder="Select Gender"
                                />
                            </div>
                        </div>

                        <div class="form-group">
                            <Input
                                label="Address"
                                showLabel
                                type="text"
                                bind:value={formData.address.value}
                                bind:error={formData.address.error}
                                validation={formData.address.validator}
                            />
                        </div>
                    </div>

                    {#if userType === Users.Doctor}
                        <div class="form-section">
                            <h3>Professional Information</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <Input
                                        category="select"
                                        label="Select Specialization"
                                        bind:value={formData.speciality.value}
                                        bind:error={formData.speciality.error}
                                        validation={formData.speciality
                                            .validator}
                                        options={Specialities.map((s) => ({
                                            value: s,
                                            label: s,
                                        }))}
                                        placeholder="Select Specialization"
                                    />
                                </div>

                                <div class="form-group">
                                    <Input
                                        label="License Number"
                                        showLabel
                                        type="text"
                                        bind:value={
                                            formData.licenseNumber.value
                                        }
                                        bind:error={
                                            formData.licenseNumber.error
                                        }
                                        validation={formData.licenseNumber
                                            .validator}
                                    />
                                </div>
                            </div>

                            <div class="form-group">
                                <Input
                                    label="Years of Experience"
                                    showLabel
                                    type="number"
                                    bind:value={
                                        formData.yearsOfExperience.value
                                    }
                                    bind:error={
                                        formData.yearsOfExperience.error
                                    }
                                    validation={formData.yearsOfExperience
                                        .validator}
                                />
                            </div>
                        </div>
                    {/if}

                    {#if userType === Users.Patient}
                        <div class="form-section">
                            <h3>Medical Information</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <Input
                                        category="select"
                                        label="Select Blood Type"
                                        bind:value={formData.bloodType.value}
                                        bind:error={formData.bloodType.error}
                                        validation={formData.bloodType
                                            .validator}
                                        options={bloodTypes.map((b) => ({
                                            value: b,
                                            label: b,
                                        }))}
                                        placeholder="Select Blood Type"
                                    />
                                </div>

                                <div class="form-group">
                                    <Input
                                        label="Emergency Contact"
                                        showLabel
                                        type="text"
                                        bind:value={
                                            formData.emergencyContact.value
                                        }
                                        bind:error={
                                            formData.emergencyContact.error
                                        }
                                        validation={formData.emergencyContact
                                            .validator}
                                    />
                                </div>
                            </div>
                        </div>
                    {/if}

                    <div class="actions">
                        <Button
                            type="button"
                            onClick={() => window.history.back()}
                            category="secondary"
                        >
                            Cancel
                        </Button>
                        <Button
                            type="submit"
                            disabled={isLoading}
                            variant="primary"
                        >
                            {isLoading ? "Saving..." : "Save Changes"}
                        </Button>
                    </div>
                </form>
            {/if}
        </section>
    </main>
</View>

<style>
    main {
        display: flex;
        gap: 4rem;
        padding-bottom: 4rem;
        padding-top: 2rem;

        position: relative;
    }

    .avatar {
        position: sticky;
        top: calc(var(--nav-height) + 5rem);

        aspect-ratio: 1 / 1;
        width: 100%;
        height: 100%;
        max-width: 400px;
    }

    .avatar-wrapper {
        position: relative;
        width: 100%;
        height: 100%;

        flex: 1;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .avatar-input {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        z-index: 3;
    }

    .profile-picture {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        position: relative;
    }

    .avatar-image {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #222;
        font-weight: 600;
        font-size: 1.5rem;
        color: #fff;
    }

    .avatar-overlay {
        position: absolute;
        inset: 0;
        background-color: rgba(0, 0, 0, 0.6);
        color: #fff;
        margin: 0.5rem;
        border: 2px dotted rgba(255, 255, 255, 0.5);
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-size: 0.85rem;
        font-weight: 500;
        text-align: center;
        opacity: 0.4;
        transition: opacity 0.25s ease;
        z-index: 2;
    }

    .avatar-wrapper:hover .avatar-overlay {
        opacity: 1;
    }

    section {
        flex: 1;
    }

    .form-section {
        margin-bottom: 2rem;
        padding: 1.5rem;
        background: var(--background, #f8f9fa);
        border-radius: 8px;
        border: 1px solid var(--border, #e1e8ed);
    }

    .form-section h3 {
        margin: 0 0 1rem 0;
        color: var(--text-primary, #2c3e50);
        font-size: 1.25rem;
        font-weight: 600;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .error {
        color: var(--error, #e74c3c);
        background: var(--error-bg, #fee);
        padding: 1rem;
        border-radius: 6px;
        border-left: 4px solid var(--error, #e74c3c);
    }

    .actions {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }

    @media (max-width: 768px) {
        main {
            flex-direction: column;
        }

        .profile-picture {
            width: 120px;
            height: 120px;
            margin: 0 auto;
        }

        .avatar {
            position: static;
            padding: 0;
        }

        .avatar-input {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .avatar-wrapper,
        .avatar {
            width: auto;
            height: auto;
            aspect-ratio: unset;
        }

        .avatar-overlay {
            display: none;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .actions {
            flex-direction: column;
        }
    }
</style>
