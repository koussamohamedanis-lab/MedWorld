<script lang="ts">
    import type { Admin } from "$lib/types/users/admin";

    import Block from "$lib/components/ui/Block.svelte";
    import Button from "$lib/components/ui/Button.svelte";

    import { user } from "$lib/stores";
    import { PlanID } from "$lib/types/plan";
    import { Check, Crown } from "@lucide/svelte";

    let currentPlanId: PlanID | null = ($user as Admin).plan?.planID || null;

    type PlanCard = {
        id: string;
        name: string;
        tag?: string;
        subtitle?: string;
        price?: string;
        period?: string;
        features: string[];
        cta?: string;
        highlight?: boolean;
    };

    const plans: PlanCard[] = [
        {
            id: PlanID.basic,
            name: "Free",
            tag: "Basic",
            subtitle: "Perfect for getting started",
            price: "Free",
            features: [
                "1 Doctor Account",
                "Basic Appointment Management",
                "Patient Records",
                "Email Notifications",
                "Standard Support",
            ],
            cta: "Start Free",
        },
        {
            id: PlanID.standard,
            name: "Standard",
            tag: "Most Popular",
            subtitle: "For growing medical practices",
            price: "1900 DA",
            period: "/month",
            features: [
                "AI Features",
                "Up to 3 Doctor Accounts",
                "SMS Notifications",
                "Online Payments",
            ],
            cta: "Get Started",
            highlight: true,
        },
        {
            id: PlanID.premium,
            name: "Premium",
            subtitle: "For large medical centers",
            price: "3800 DA",
            period: "/month",
            features: [
                "Unlimited Doctor Accounts",
                "AI Features",
                "Full Practice Management",
                "Advanced Analytics & Reports",
                "Priority Support 24/7",
                "Custom Integration Options",
                "Staff Management",
            ],
            cta: "Get Started",
        },
    ];

    let current = $derived(
        currentPlanId
            ? (plans.find((p) => p.id === currentPlanId) ?? null)
            : null,
    );
</script>

<Block group={"plan" as any} title="My Subscriptions" Icon={Crown}>
    {#if current}
        <div class="current-container">
            <article class="plan current" aria-live="polite">
                <header class="plan-header">
                    <div>
                        <strong class="tag">Current Plan</strong>
                        <h3>{current.name}</h3>
                        {#if current.subtitle}
                            <p class="subtitle">{current.subtitle}</p>
                        {/if}
                    </div>
                    <div class="price">
                        <span class="amount">{current.price}</span>
                        {#if current.period}
                            <span class="period">{current.period}</span>
                        {/if}
                    </div>
                </header>

                <ul class="features">
                    {#each current.features as feat}
                        <li>
                            <Check size={16} />
                            <span>{feat}</span>
                        </li>
                    {/each}
                </ul>
            </article>

            <div class="actions">
                <Button
                    category="primary"
                    Icon={Crown}
                    disabled={currentPlanId === PlanID.premium}
                    style="width: 100%"
                    color="var(--gold-dark) !important;"
                    href="/dashboard/plans"
                    target="_blank"
                >
                    {currentPlanId !== PlanID.premium
                        ? "Upgrade Plan"
                        : "You're in the best plan!"}
                </Button>
            </div>
        </div>
    {/if}
</Block>

<style>
    .current-container {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .plan {
        background: var(--surface, #fff);
        border-radius: 0.75rem;
        padding: 1.25rem;
        border: 1px solid rgba(0, 0, 0, 0.06);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 320px;
    }

    .plan.current {
        border: 2px solid var(--color-primary);
        background-color: #f9fcff;
    }

    .plan-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
    }

    .tag {
        display: inline-block;
        background: var(--color-primary);
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 999px;
        font-size: 0.75rem;
        margin-bottom: 0.5rem;
    }

    h3 {
        margin: 0;
        font-size: 1.25rem;
    }

    .subtitle {
        margin: 0.25rem 0 0 0;
        color: var(--text-muted);
        font-size: 0.95rem;
    }

    .price {
        text-align: right;
        min-width: 96px;
    }

    .price .amount {
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--color-primary);
    }

    .price .period {
        font-size: 0.9rem;
        color: var(--text-muted);
    }

    .features {
        list-style: none;
        padding: 0;
        margin: 1rem 0 1.25rem 0;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .features li {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--text-color);
    }

    .features :global(svg) {
        color: var(--color-primary);
    }

    .actions {
        margin-top: auto;
    }
</style>
