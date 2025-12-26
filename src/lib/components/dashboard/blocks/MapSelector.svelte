<script lang="ts">
  import { onMount } from "svelte";
  import { fade } from "svelte/transition";
  import type { Location } from "$lib/types/cabinet";
  import { Loader, MapPin } from "@lucide/svelte";
  import { validate, validation, type Fillable } from "$lib/validation";
  import "leaflet/dist/leaflet.css";
  import Input from "$lib/components/ui/Input.svelte";
  import Button from "$lib/components/ui/Button.svelte";
  import IconButton from "$lib/components/ui/IconButton.svelte";

  interface IProps {
    location: Location;
    onChange: (location: Location) => void;
  }

  let { location, onChange }: IProps = $props();
  let mapContainer = $state<HTMLDivElement>();
  let map: any;
  let marker: any;
  let searchResults = $state<any[]>([]);
  let showResults = $state(false);
  let isMapVisible = $state(false);
  let loadingLocateMe = $state(false);

  const searchValidator = (query: string) => {
    return query.trim().length >= 2
      ? ""
      : "Search query must be at least 2 characters.";
  };

  let formData: Fillable = $state({
    searchQuery: {
      value: "",
      error: "",
      validator: searchValidator,
    },
  });

  let loadingSearch = $state(false);

  let mapInitialized = $state(false);

  onMount(() => {
    if (!mapContainer || mapInitialized) return;

    (async () => {
      try {
        // Dynamically import Leaflet on client side
        // @ts-ignore
        const L = (await import("leaflet")).default;

        if (!mapContainer) return;

        // Fix Leaflet default icon issue
        delete (L.Icon.Default.prototype as any)._getIconUrl;
        L.Icon.Default.mergeOptions({
          iconRetinaUrl:
            "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png",
          iconUrl:
            "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png",
          shadowUrl:
            "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png",
        });

        // Initialize map
        map = L.map(mapContainer).setView(
          [location.latitude || 36.737, location.longitude || 3.0588],
          13
        );

        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
          attribution: "© OpenStreetMap contributors",
          maxZoom: 19,
        }).addTo(map);

        // Add marker
        marker = L.marker([
          location.latitude || 36.737,
          location.longitude || 3.0588,
        ])
          .addTo(map)
          .bindPopup("Cabinet Location");

        // Click to place marker
        map.on("click", (e: any) => {
          const { lat, lng } = e.latlng;
          marker.setLatLng([lat, lng]);
          onChange({
            ...location,
            latitude: lat,
            longitude: lng,
          });
        });

        mapInitialized = true;

        // Initial size invalidation
        setTimeout(() => {
          if (map) map.invalidateSize();
        }, 500);
      } catch (error) {
        console.error("Map initialization error:", error);
      }
    })();

    return () => {
      try {
        if (map) {
          map.remove();
          map = null;
          marker = null;
          mapInitialized = false;
        }
      } catch (e) {
        console.error("Cleanup error:", e);
      }
    };
  });

  let searchTimeout = $state<number | null>(null);

  async function searchLocation() {
    if (searchTimeout) {
      clearTimeout(searchTimeout);
    }
    searchTimeout = setTimeout(async () => {
      loadingSearch = true;
      if (formData.searchQuery.error) {
        return;
      }

      try {
        const response = await fetch(
          `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(
            formData.searchQuery.value
          )}&countrycodes=dz`
        );
        const results = await response.json();
        searchResults = results.slice(0, 5);
        showResults = true;
        if (searchResults.length > 0) {
          isMapVisible = true;
        }
      } catch (error) {
        console.error("Search error:", error);
        formData.searchQuery.error = "Failed to search location";
      } finally {
        loadingSearch = false;
      }
    }, 500);
  }

  function selectSearchResult(result: any) {
    const lat = parseFloat(result.lat);
    const lng = parseFloat(result.lon);

    updateMap(lat, lng, result.display_name);

    formData.searchQuery.value = "";
    formData.searchQuery.error = "";
    searchResults = [];
    showResults = false;
    isMapVisible = true;
  }

  function updateMap(lat: number, lng: number, address?: string) {
    if (map && marker) {
      map.setView([lat, lng], 15);
      marker.setLatLng([lat, lng]);
    }

    onChange({
      address: address || location.address,
      latitude: lat,
      longitude: lng,
    });
  }

  function locateMe() {
    if (!navigator.geolocation) {
      alert("Geolocation is not supported by your browser");
      return;
    }

    loadingLocateMe = true;
    navigator.geolocation.getCurrentPosition(
      async (position) => {
        const { latitude, longitude } = position.coords;

        // Try to get address for these coordinates
        try {
          const response = await fetch(
            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`
          );
          const data = await response.json();
          updateMap(latitude, longitude, data.display_name);
          isMapVisible = true;
        } catch (e) {
          updateMap(latitude, longitude, "Current Location");
          isMapVisible = true;
        } finally {
          loadingLocateMe = false;
        }
      },
      (error) => {
        console.error("Geolocation error:", error);
        alert("Unable to retrieve your location");
        loadingLocateMe = false;
      }
    );
  }

  // Sync map with location prop if it changes from outside
  $effect(() => {
    if (
      map &&
      marker &&
      (location.latitude !== marker.getLatLng().lat ||
        location.longitude !== marker.getLatLng().lng)
    ) {
      marker.setLatLng([location.latitude, location.longitude]);
      map.panTo([location.latitude, location.longitude]);
    }
  });

  $effect(() => {
    if (isMapVisible && map) {
      // Multiple staggered calls to ensure size is correct after CSS changes
      setTimeout(() => {
        if (map) {
          map.invalidateSize();
          console.log(map);
          if (marker) {
            map.panTo(marker.getLatLng());
          }
        }
      }, 100);
    }
  });
</script>

<div class="map-container">
  <div class="map-header">
    <div class="search-box">
      <div class="search-input-group">
        <div class="search-icon-wrapper">
          {#if loadingSearch}
            <Loader size={18} />
          {:else}
            <MapPin size={18} />
          {/if}
        </div>
        <input
          placeholder="Search for an address..."
          bind:value={formData.searchQuery.value}
          oninput={searchLocation}
          class="search-input"
          aria-label="Search for cabinet location"
        />
        <IconButton
          onClick={locateMe}
          Icon={loadingLocateMe ? Loader : MapPin}
          label="Use my current location"
          disabled={loadingLocateMe}
        />
      </div>

      {#if showResults && searchResults.length > 0}
        <div class="search-results" transition:fade>
          {#each searchResults as result}
            <button
              class="result-item"
              onclick={() => selectSearchResult(result)}
            >
              <MapPin size={14} />
              <span>{result.display_name}</span>
            </button>
          {/each}
        </div>
      {/if}
    </div>

    <div class="location-summary">
      <div class="address-display">
        <span class="label">Selected Address</span>
        <p class="value">
          {location.address || "Click on map or search to set address"}
        </p>
      </div>
      <div class="coords-display">
        <div class="coord">
          <span class="label">LAT</span>
          <span class="value">{location.latitude.toFixed(6)}</span>
        </div>
        <div class="coord">
          <span class="label">LNG</span>
          <span class="value">{location.longitude.toFixed(6)}</span>
        </div>
      </div>
      <div class="toggle-wrapper">
        <Button
          onClick={() => (isMapVisible = !isMapVisible)}
          theme="secondary"
          size="sm"
        >
          {isMapVisible ? "Close Map" : "Open Map"}
        </Button>
      </div>
    </div>
  </div>

  <div class="map-wrapper" class:hidden={!isMapVisible}>
    <div bind:this={mapContainer} class="map"></div>
    <div class="map-hint">
      <p>💡 Click anywhere on the map to refine the location</p>
    </div>
  </div>
</div>

<style>
  .map-container {
    display: flex;
    flex-direction: column;
    background: var(--white);
    border-radius: var(--border-radius-lg);
    overflow: hidden;
  }

  .map-header {
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    background: var(--background-primary);
    border-bottom: 1px solid var(--border-color-light);
  }

  .search-box {
    position: relative;
  }

  .search-input-group {
    position: relative;
    display: flex;
    align-items: center;
    border-radius: var(--border-radius-md);
    transition: all 0.2s ease;
  }

  .search-input-group:focus-within {
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px var(--color-primary-alpha);
  }

  .search-icon-wrapper {
    color: var(--color-primary);
    margin-right: 0.5rem;
    display: flex;
    align-items: center;
  }

  .search-input {
    flex: 1;
    border: none;
    outline: none;
    background: transparent;
    padding: 0.75rem 0;
    font-size: 0.9rem;
  }

  .locate-btn {
    background: none;
    border: none;
    color: var(--color-primary);
    cursor: pointer;
    padding: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: background 0.2s ease;
  }

  .locate-btn:hover {
    background: var(--color-primary-alpha);
  }

  .search-results {
    position: absolute;
    top: calc(100% + 0.5rem);
    left: 0;
    right: 0;
    background: var(--white);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius-md);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    max-height: 250px;
    overflow-y: auto;
    z-index: 100;
  }

  .result-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    width: 100%;
    padding: 0.75rem 1rem;
    background: none;
    border: none;
    border-bottom: 1px solid var(--border-color-light);
    color: var(--text-primary);
    cursor: pointer;
    text-align: left;
    font-size: 0.85rem;
    transition: background 0.2s ease;
  }

  .result-item:hover {
    background: var(--background-secondary);
  }

  .location-summary {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1.5rem;
  }

  .address-display {
    flex: 1;
  }

  .toggle-wrapper {
    display: flex;
    align-items: center;
  }

  .label {
    display: block;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-secondary);
    margin-bottom: 0.25rem;
  }

  .value {
    font-size: 0.9rem;
    color: var(--text-primary);
    margin: 0;
    line-height: 1.4;
  }

  .coords-display {
    display: flex;
    gap: 1rem;
    background: var(--background-secondary);
    padding: 0.5rem 0.75rem;
    border-radius: var(--border-radius-sm);
  }

  .coord {
    display: flex;
    flex-direction: column;
  }

  .coord .value {
    font-family: var(--font-third);
    font-size: 0.8rem;
    font-weight: 500;
  }

  .map-wrapper {
    position: relative;
    display: flex;
    flex-direction: column;
  }

  .map-wrapper.hidden {
    display: none;
  }

  .map {
    width: 100%;
    height: 400px;
    min-height: 300px;
    z-index: 1;
  }

  .map-hint {
    position: absolute;
    bottom: 1rem;
    left: 50%;
    transform: translateX(-50%);
    z-index: 2;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(4px);
    padding: 0.5rem 1rem;
    border-radius: 2rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    pointer-events: none;
  }

  .map-hint p {
    margin: 0;
    font-size: 0.75rem;
    color: var(--text-secondary);
    white-space: nowrap;
  }

  @media (max-width: 768px) {
    .location-summary {
      flex-direction: column;
      gap: 1rem;
    }

    .coords-display {
      width: 100%;
      justify-content: space-around;
    }
  }
</style>
