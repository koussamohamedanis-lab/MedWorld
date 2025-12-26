<script lang="ts">
    import { userLocation } from "$lib/stores";
    import {
        calculateDistance,
        isCabinetOpen,
        type Cabinet,
    } from "$lib/types/cabinet";
    import { calculateRatingScore } from "$lib/types/rating";
    // @ts-ignore
    import tippy from "sveltejs-tippy";

    let { cabinet }: { cabinet: Cabinet } = $props();
    function getDayOfWeek(): string {
        const days = [
            "Sunday",
            "Monday",
            "Tuesday",
            "Wednesday",
            "Thursday",
            "Friday",
            "Saturday",
        ];
        return days[new Date().getDay()];
    }

    function shorten(address: string): string {
        function innerShorten(address: string): string {
            return address.length > 50 ? address.slice(0, 50) + "..." : address;
        }

        // if the address contains commas, return the last 3 comma-separated strings
        if (address.includes(",")) {
            return innerShorten(address.split(",").slice(-5).join(", "));
        }
        return innerShorten(address);
    }
</script>

<button class="cabinet-card">
    <div class="cabinet-image">
        <img
            src={cabinet.image || "placeholder-cabinet.jpg"}
            alt={cabinet.name}
        />
        <div class="stickers">
            {#if cabinet.accessHandicap}
                <div
                    class="accessibility-badge"
                    use:tippy={{
                        content: "Wheelchair accessible",
                        placement: "top",
                    }}
                    title="Wheelchair accessible"
                >
                    ♿
                </div>
            {/if}
            {#if cabinet.hasParking}
                <div
                    class="accessibility-badge"
                    use:tippy={{
                        content: "Parking available",
                        placement: "top",
                    }}
                    title="Parking available"
                >
                    🅿️
                </div>
            {/if}

            {#if cabinet.hasWifi}
                <div
                    class="accessibility-badge"
                    use:tippy={{
                        content: "Wi-Fi available",
                        placement: "top",
                    }}
                    title="Wi-Fi available"
                >
                    📶
                </div>
            {/if}

            {#if cabinet.acceptsInsurance}
                <div
                    class="accessibility-badge"
                    use:tippy={{
                        content: "Accepts insurance",
                        placement: "top",
                    }}
                    title="Accepts insurance"
                >
                    💳
                </div>
            {/if}
        </div>
    </div>

    <div class="cabinet-info">
        <h3>{cabinet.name}</h3>
        <p class="address">
            📍 {shorten(cabinet.location?.address) || "No address"}
        </p>
        <p class="phone">📞 {cabinet.phone || "No phone"}</p>
        <p>
            🚗 Distance {calculateDistance(cabinet, $userLocation).toFixed(0)} KM
        </p>
        <p class="rating">
            ⭐ {calculateRatingScore(cabinet).toFixed(1)} / 5
        </p>
        <p class="hours-preview">
            <span>🕒 Today: </span>
            {#if cabinet.openingHours?.[getDayOfWeek().toLowerCase()]}
                <span
                    >{cabinet.openingHours[getDayOfWeek().toLowerCase()].open} -
                    {cabinet.openingHours[getDayOfWeek().toLowerCase()]
                        .close}</span
                >
            {:else}
                <span class="closed">Closed</span>
            {/if}
        </p>
    </div>
</button>

<style>
    .cabinet-card {
        background: white;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        cursor: pointer;
        border: 2px solid transparent;
        overflow: hidden;
        transition: all 0.2s ease;
        width: 100%;
        border: none;
    }

    .cabinet-card:hover {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .stickers {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;

        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
    }

    .cabinet-image {
        position: relative;
        width: 100%;
        height: 200px;
    }

    .cabinet-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .accessibility-badge {
        background: rgba(255, 255, 255, 0.9);
        padding: 0.25rem;
        border-radius: 50%;
        font-size: 1rem;
    }

    .cabinet-info {
        padding: 1rem;
    }

    .cabinet-info > * {
        margin-bottom: 0.5rem;
    }

    .cabinet-info h3 {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 0.5rem;
        text-align: left;
    }

    .cabinet-info p {
        text-align: left;
        font-size: 0.875rem;
        color: #666;
        display: flex;
        gap: 0.5rem;
    }

    .hours-preview {
        font-size: 0.875rem;
        color: #666;
        display: flex;
        align-items: center;
    }

    .hours-preview .closed {
        color: #dc2626;
        font-weight: 500;
    }
</style>
