<script lang="ts">
    import View from "$lib/components/ui/View.svelte";
    import Block from "$lib/components/ui/Block.svelte";
    import Button from "$lib/components/ui/Button.svelte";
    import { goto } from "$app/navigation";
    import type { Rating, UserExperience } from "$lib/types/rating";
    import { page } from "$app/stores";
    import { currentBlock } from "$lib/stores";
    import {
        cabinets as cabinetsStore,
        loadAllCabinets,
        patients as patientsStore,
        loadAllPatients,
    } from "$lib/stores/data";
    import { onMount } from "svelte";

    // Get the cabinet from the route param
    const cabinetId = $page.params.id ? parseInt($page.params.id) : 0;

    let cabinet = $state<any>(null);
    let patient = $state<any>(null);
    let isLoading = $state(true);
    let error = $state<string | null>(null);

    onMount(async () => {
        isLoading = true;
        error = null;
        try {
            // Load cabinets and patients from API if not already loaded
            if ($cabinetsStore.length === 0) {
                await loadAllCabinets();
            }
            if ($patientsStore.length === 0) {
                await loadAllPatients();
            }

            const foundCabinet = $cabinetsStore.find((c) => c.id === cabinetId);
            if (!foundCabinet) {
                error = "Cabinet not found";
                isLoading = false;
                return;
            }
            cabinet = foundCabinet;

            // Use the first patient from API data (in real app, would be current logged-in patient)
            if ($patientsStore.length === 0) {
                error = "No patients found";
                isLoading = false;
                return;
            }
            patient = $patientsStore[0];
        } catch (err: any) {
            error = err.message || "Failed to load data";
        } finally {
            isLoading = false;
        }
    });

    // Initialize currentBlock to null to show all blocks
    currentBlock.set([]);

    // Type-safe category handling
    type ExperienceCategory = keyof UserExperience;

    let rating = $derived.by(() => ({
        id: Date.now(),
        patient: patient,
        cabinet: cabinet,
        date: new Date(),
        equippement: {
            disponibility: true,
            rating: 5,
        },
        userExperience: {
            reception: 5,
            hygiene: 5,
            waitTime: 5,
            communication: 5,
            professionalism: 5,
            emplacement: 5,
        },
        review: "",
    }));

    const experienceCategories: Array<{
        id: ExperienceCategory;
        label: string;
    }> = [
        { id: "reception", label: "Reception Service" },
        { id: "hygiene", label: "Cleanliness & Hygiene" },
        { id: "waitTime", label: "Waiting Time" },
        { id: "communication", label: "Staff Communication" },
        { id: "professionalism", label: "Professionalism" },
        { id: "emplacement", label: "Location & Accessibility" },
    ];

    async function handleSubmit() {
        try {
            // Type safety for cabinet
            if (!cabinet) {
                throw new Error("Cabinet not found");
            }

            // Validate required fields
            if (
                !rating.equippement.rating ||
                Object.values(rating.userExperience).some((val) => !val)
            ) {
                alert("Please rate all categories before submitting");
                return;
            }

            // Add the rating (cabinet.ratings is guaranteed to exist by the Cabinet type)
            cabinet.ratings.push({
                ...rating,
                id: Date.now(),
                date: new Date(),
                cabinet: cabinet, // Ensure correct circular reference
            });

            // Show success message
            alert("Thank you for your review!");

            // Navigate back to the cabinet page
            await goto(`/cabinets/${cabinet.id}`);
        } catch (error) {
            console.error("Error submitting rating:", error);
            alert(
                "There was an error submitting your rating. Please try again.",
            );
        }
    }

    function handleStarKeyDown(
        e: KeyboardEvent,
        value: number,
        setter: (val: number) => void,
    ) {
        const target = e.target as HTMLButtonElement;
        if (e.key === " " || e.key === "Enter") {
            e.preventDefault();
            setter(value);
        } else if (e.key === "ArrowLeft" && value > 1) {
            e.preventDefault();
            setter(value - 1);
            const prev = target.previousElementSibling as HTMLButtonElement;
            prev?.focus();
        } else if (e.key === "ArrowRight" && value < 5) {
            e.preventDefault();
            setter(value + 1);
            const next = target.nextElementSibling as HTMLButtonElement;
            next?.focus();
        }
    }
</script>

<View screen style="padding: 2rem;">
    <div class="rating-container">
        {#if isLoading}
            <div class="loading-state">
                <p>Loading cabinet information...</p>
            </div>
        {:else if error}
            <div class="error-state">
                <p>{error}</p>
            </div>
        {:else if cabinet && patient}
            <header>
                <h1>Rate Your Experience at {cabinet?.name}</h1>
                <p class="subtitle">
                    Your feedback helps others make informed decisions
                </p>
            </header>

            <form on:submit|preventDefault={handleSubmit} class="rating-form">
                <!-- Equipment Section -->
                <section class="form-section">
                    <h2>Equipment & Facilities</h2>
                    <div class="equipment-rating">
                        <label class="checkbox-label">
                            <input
                                type="checkbox"
                                bind:checked={rating.equippement.disponibility}
                            />
                            Equipment was available when needed
                        </label>
                        <div class="rating-control">
                            <div
                                class="rating-label"
                                id="equipment-quality-label"
                            >
                                Equipment Quality
                            </div>
                            <div
                                class="star-rating"
                                role="radiogroup"
                                aria-labelledby="equipment-quality-label"
                            >
                                {#each [1, 2, 3, 4, 5] as value}
                                    <button
                                        type="button"
                                        class="star-button {value <=
                                        rating.equippement.rating
                                            ? 'active'
                                            : ''}"
                                        on:click={() =>
                                            (rating.equippement.rating = value)}
                                        on:keydown={(e) =>
                                            handleStarKeyDown(
                                                e,
                                                value,
                                                (val) =>
                                                    (rating.equippement.rating =
                                                        val),
                                            )}
                                        aria-label="Rate equipment quality {value} {value ===
                                        1
                                            ? 'star'
                                            : 'stars'}"
                                        role="radio"
                                        aria-checked={value <=
                                            rating.equippement.rating}
                                        tabindex={value ===
                                        rating.equippement.rating
                                            ? 0
                                            : -1}
                                    >
                                        ★
                                    </button>
                                {/each}
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Experience Section -->
                <section class="form-section">
                    <h2>Your Experience</h2>
                    <div class="experience-ratings">
                        {#each experienceCategories as category}
                            <div class="rating-control">
                                <div
                                    class="rating-label"
                                    id="rating-{category.id}-label"
                                >
                                    {category.label}
                                </div>
                                <div
                                    class="star-rating"
                                    role="radiogroup"
                                    aria-labelledby="rating-{category.id}-label"
                                >
                                    {#each [1, 2, 3, 4, 5] as value}
                                        <button
                                            type="button"
                                            class="star-button {value <=
                                            rating.userExperience[category.id]
                                                ? 'active'
                                                : ''}"
                                            on:click={() =>
                                                (rating.userExperience[
                                                    category.id
                                                ] = value)}
                                            on:keydown={(e) =>
                                                handleStarKeyDown(
                                                    e,
                                                    value,
                                                    (val) =>
                                                        (rating.userExperience[
                                                            category.id
                                                        ] = val),
                                                )}
                                            aria-label="Rate {value} {value ===
                                            1
                                                ? 'star'
                                                : 'stars'} for {category.label}"
                                            role="radio"
                                            aria-checked={value <=
                                                rating.userExperience[
                                                    category.id
                                                ]}
                                            tabindex={value ===
                                            rating.userExperience[category.id]
                                                ? 0
                                                : -1}
                                        >
                                            ★
                                        </button>
                                    {/each}
                                </div>
                            </div>
                        {/each}
                    </div>
                </section>

                <!-- Written Review Section -->
                <section class="form-section">
                    <h2>Written Review</h2>
                    <div class="review-input">
                        <textarea
                            bind:value={rating.review}
                            placeholder="Share your experience with this medical cabinet..."
                            rows="4"
                            aria-label="Written review"
                        ></textarea>
                    </div>
                </section>

                <div class="form-actions">
                    <Button
                        type="button"
                        variant="secondary"
                        on:click={() => goto(`/cabinets/${cabinet!.id}`)}
                        >Cancel</Button
                    >
                    <Button type="submit">Submit Review</Button>
                </div>
            </form>
        {/if}
    </div>
</View>

<style>
    .rating-container {
        max-width: 800px;
        margin: 0 auto;
    }

    header {
        text-align: center;
        margin-bottom: 2rem;
    }

    h1 {
        font-size: 1.75rem;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0.5rem;
    }

    .subtitle {
        color: #718096;
        font-size: 1rem;
    }

    .form-section {
        margin-bottom: 2rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .form-section h2 {
        font-size: 1.25rem;
        font-weight: 500;
        color: #4a5568;
        margin-bottom: 1rem;
    }

    .equipment-rating {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #4a5568;
    }

    .rating-control {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        padding: 1rem;
        background: #f7fafc;
        border-radius: 0.5rem;
    }

    .rating-label {
        color: #4a5568;
        font-weight: 500;
    }

    .star-rating {
        display: flex;
        gap: 0.25rem;
    }

    .star-button {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #cbd5e0;
        transition: color 0.2s;
        padding: 0;
        line-height: 1;
    }

    .star-button.active {
        color: #ecc94b;
    }

    .star-button:hover {
        color: #ecc94b;
    }

    .experience-ratings {
        display: grid;
        gap: 1rem;
    }

    .review-input textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        resize: vertical;
        font-family: inherit;
        transition: border-color 0.2s;
    }

    .review-input textarea:focus {
        outline: none;
        border-color: #4299e1;
        box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 2rem;
    }

    @media (min-width: 640px) {
        .experience-ratings {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>
