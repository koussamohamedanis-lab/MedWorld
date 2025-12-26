<script lang="ts">
    let {
        avatarUrl = "",
        alt = "? ",
        size = "2rem",
        original = false,
    }: {
        avatarUrl?: string;
        alt?: string;
        size?: string;
        original?: boolean;
    } = $props();

    let realAlt = $derived(
        typeof alt !== "undefined" && alt !== "" && alt.split(" ").length > 1
            ? alt.split(" ")[0][0] + (alt.split(" ")[1][0] || "")
            : "?",
    );

    function getColorFromAlt(alt: string) {
        let hash = 0;
        for (let i = 0; i < alt.length; i++) {
            hash = alt.charCodeAt(i) + ((hash << 5) - hash);
        }

        const h = Math.abs(hash % 360);
        const s = 70;
        const l = 60;

        return `hsl(${h}, ${s}%, ${l}%)`;
    }
</script>

<main style="width: {size}; height: {size};">
    <div class="avatar">
        {#if avatarUrl}
            <img
                src={avatarUrl + (original ? "" : "?x=" + Math.random())}
                alt=""
            />
        {:else}
            <div
                class="placeholder"
                style="background-color: {getColorFromAlt(alt)};"
            >
                {realAlt}
            </div>
        {/if}
    </div>
</main>

<style>
    .avatar {
        width: 100%;
        height: 100%;
        aspect-ratio: 1/1;
        border-radius: 50%;
        overflow: hidden;
    }

    .avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        text-transform: uppercase;
    }
</style>
