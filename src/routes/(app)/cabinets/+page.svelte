<script lang="ts">
  import View from "$lib/components/ui/View.svelte";
  import Face from "$lib/components/ui/Face.svelte";
  import {
    AlarmSmoke,
    CreditCard,
    ParkingCircle,
    Wifi,
    Accessibility,
    Clock,
    Award,
    DollarSign,
  } from "@lucide/svelte";
  import { fade } from "svelte/transition";
  import { Specialities, type Speciality } from "$lib/types/speciality";
  import { searchDoctors, cabinets, isLoadingDoctors } from "$lib/stores/data";
  import type { Doctor } from "$lib/types/users/doctor";
  import CheckBox from "$lib/components/ui/CheckBox.svelte";
  import CheckBoxGrid from "$lib/components/ui/CheckBoxGrid.svelte";

  let allDoctors: Doctor[] = [];

  type SearchSettings = {
    speciality: Speciality | null;
    maxPrice: number | null;
    minPrice: number | null;
    cabinetId: number | null;
    criteria: {
      acceptsUrgent: boolean;
      acceptsInsurance: boolean;
      hasHighRating: boolean;
    };
  };

  let search: SearchSettings = $state({
    speciality: null,
    maxPrice: null,
    minPrice: null,
    cabinetId: null,
    criteria: {
      acceptsUrgent: false,
      acceptsInsurance: false,
      hasHighRating: false,
    },
  });

  // Validation: at least one filter selected
  function hasFiltersSelected(): boolean {
    return (
      search.speciality !== null ||
      search.maxPrice !== null ||
      search.minPrice !== null ||
      search.cabinetId !== null ||
      Object.values(search.criteria).some(Boolean)
    );
  }

  let hasFilters: boolean = $derived(hasFiltersSelected());

  async function performSearch(settings: SearchSettings): Promise<Doctor[]> {
    const filters: any = {};

    if (settings.speciality) {
      filters.speciality = settings.speciality;
    }
    if (settings.maxPrice !== null) {
      filters.priceMax = settings.maxPrice;
    }
    if (settings.minPrice !== null) {
      filters.priceMin = settings.minPrice;
    }
    if (settings.cabinetId !== null) {
      filters.cabinetId = settings.cabinetId;
    }

    try {
      const results = await searchDoctors(filters);
      return results.filter((doctor) => {
        if (
          settings.criteria.hasHighRating &&
          (!doctor.rating || doctor.rating < 4)
        ) {
          return false;
        }
        return true;
      });
    } catch (error) {
      console.error("Search failed:", error);
      return [];
    }
  }

  let filteredDoctors: Doctor[] = $state([]);

  async function executeSearch() {
    if (!hasFilters) {
      filteredDoctors = [];
      return;
    }
    filteredDoctors = await performSearch(search);
  }

  $effect(() => {
    executeSearch();
  });
</script>

<Face
  title="Find Doctors"
  description="Search for doctors using decision-making criteria"
>
  <View>
    <div class="page-inner">
      <div class="list-header">
        <div class="search">
          <div class="filters-container" transition:fade>
            <div class="filters-section specialities-section">
              <h3>Specialities</h3>
              <div class="specialities">
                <select class="select-field" bind:value={search.speciality}>
                  <option value={null}>All Specialities</option>
                  {#each Specialities as speciality}
                    <option value={speciality}>{speciality}</option>
                  {/each}
                </select>
              </div>
            </div>

            <div class="filters-section price-section">
              <h3>Consultation Price</h3>
              <div class="price-inputs">
                <input
                  type="number"
                  placeholder="Min"
                  class="price-input"
                  bind:value={search.minPrice}
                />
                <input
                  type="number"
                  placeholder="Max"
                  class="price-input"
                  bind:value={search.maxPrice}
                />
              </div>
            </div>

            <div class="filters-section cabinet-section">
              <h3>Cabinet</h3>
              <div class="cabinet-select">
                <select class="select-field" bind:value={search.cabinetId}>
                  <option value={null}>All Cabinets</option>
                  {#each $cabinets as cabinet}
                    <option value={cabinet.id}>{cabinet.name}</option>
                  {/each}
                </select>
              </div>
            </div>

            <div class="filters-section features">
              <h3>Doctor Features</h3>
              {#if !hasFilters}
                <div class="validation-message">
                  <p>⚠️ Select criteria to search</p>
                </div>
              {:else}
                <div class="validation-message success">
                  <p>✓ Searching...</p>
                </div>
              {/if}

              <CheckBoxGrid>
                <CheckBox
                  bind:checked={search.criteria.acceptsUrgent}
                  title="Accepts Urgent"
                  Icon={AlarmSmoke}
                />

                <CheckBox
                  bind:checked={search.criteria.acceptsInsurance}
                  title="Accepts Insurance"
                  Icon={CreditCard}
                />

                <CheckBox
                  bind:checked={search.criteria.hasHighRating}
                  title="High Rating (4+)"
                  Icon={Award}
                />
              </CheckBoxGrid>
            </div>
          </div>
        </div>
      </div>

      {#if $isLoadingDoctors}
        <div class="loading">
          <div class="spinner"></div>
          <p>Loading doctors...</p>
        </div>
      {:else if filteredDoctors.length > 0}
        <div class="doctors-results">
          <div class="results-header">
            <h2>
              Found {filteredDoctors.length} doctor{filteredDoctors.length !== 1
                ? "s"
                : ""}
            </h2>
          </div>
          <div class="doctors-grid">
            {#each filteredDoctors as doctor (doctor.id)}
              <a
                href={`/doctors/${doctor.id}`}
                class="doctor-card"
                transition:fade
              >
                <div class="doctor-header">
                  <img
                    src={doctor.avatarUrl ||
                      `https://api.dicebear.com/7.x/avataaars/svg?seed=${doctor.id}`}
                    alt={doctor.firstName}
                    class="doctor-avatar"
                  />
                  <div class="doctor-info">
                    <h3>
                      Dr. {doctor.firstName}
                      {doctor.lastName}
                    </h3>
                    <p class="speciality">
                      {doctor.speciality}
                    </p>
                  </div>
                </div>

                <div class="doctor-details">
                  <div class="detail">
                    <Award size={16} />
                    <span
                      >Experience: {doctor.getYearsOfExperience() || "N/A"} years</span
                    >
                  </div>
                  <div class="detail">
                    <DollarSign size={16} />
                    <span>{doctor.consultationPrice} DA</span>
                  </div>
                </div>

                {#if doctor.cabinet}
                  <div class="cabinet-info">
                    {doctor.cabinet.name}
                  </div>
                {/if}
              </a>
            {/each}
          </div>
        </div>
      {:else if hasFilters}
        <div class="no-results">
          <p>No doctors found with the current search and filters.</p>
        </div>
      {:else}
        <div class="no-search">
          <p>Select search criteria to find doctors</p>
        </div>
      {/if}
    </div>
  </View>
</Face>

<style>
  .page-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
  }

  .list-header {
    margin-bottom: 2rem;
  }

  .search {
    flex: 1;
  }

  .filters-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    padding: 2rem;
    background: var(--color-background-secondary);
    border-radius: 1rem;
    margin-top: 1rem;
  }

  .filters-section {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }

  .filters-section h3 {
    font-size: 0.95rem;
    font-weight: 700;
    margin: 0;
  }

  .select-field {
    padding: 0.75rem;
    border: 1px solid var(--color-border);
    border-radius: 0.5rem;
    font-size: 0.9rem;
    background: white;
  }

  .select-field:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  }

  .price-inputs {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
  }

  .price-input {
    padding: 0.75rem;
    border: 1px solid var(--color-border);
    border-radius: 0.5rem;
    font-size: 0.9rem;
  }

  .price-input:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  }

  .validation-message {
    padding: 0.75rem;
    background: var(--color-danger-light);
    color: var(--color-danger);
    border-radius: 0.5rem;
    text-align: center;
    margin-bottom: 0.75rem;
  }

  .validation-message.success {
    background: var(--color-success-light);
    color: var(--color-success);
  }

  .validation-message p {
    margin: 0;
    font-size: 0.875rem;
  }

  .loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem;
    gap: 1rem;
  }

  .spinner {
    width: 40px;
    height: 40px;
    border: 4px solid var(--color-border);
    border-top-color: var(--color-primary);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
  }

  @keyframes spin {
    to {
      transform: rotate(360deg);
    }
  }

  .doctors-results {
    margin-top: 2rem;
  }

  .results-header {
    margin-bottom: 1.5rem;
  }

  .results-header h2 {
    margin: 0;
    font-size: 1.5rem;
  }

  .doctors-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
  }

  .doctor-card {
    padding: 1.5rem;
    background: white;
    border: 1px solid var(--color-border);
    border-radius: 1rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    transition: all 0.2s ease;
  }

  .doctor-card:hover {
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
  }

  .doctor-header {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
  }

  .doctor-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
  }

  .doctor-info {
    flex: 1;
  }

  .doctor-info h3 {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
  }

  .speciality {
    margin: 0.25rem 0 0 0;
    color: var(--color-primary);
    font-size: 0.875rem;
    font-weight: 600;
  }

  .doctor-details {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }

  .detail {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
  }

  .cabinet-info {
    padding: 0.75rem;
    background: var(--color-background-secondary);
    border-radius: 0.5rem;
    font-size: 0.875rem;
    text-align: center;
  }

  .no-results,
  .no-search {
    text-align: center;
    padding: 3rem 2rem;
    color: var(--text-color-muted);
  }

  .no-results p,
  .no-search p {
    margin: 0;
    font-size: 1rem;
  }

  @media (max-width: 768px) {
    .page-inner {
      padding: 1rem;
    }

    .filters-container {
      grid-template-columns: 1fr;
      padding: 1rem;
    }

    .doctors-grid {
      grid-template-columns: 1fr;
      gap: 1rem;
    }

    .price-inputs {
      grid-template-columns: 1fr;
    }
  }
</style>
