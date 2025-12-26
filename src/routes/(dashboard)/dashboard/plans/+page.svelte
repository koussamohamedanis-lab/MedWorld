<script lang="ts">
    import Button from "$lib/components/ui/Button.svelte";
    import { user } from "$lib/stores";
    import { Check, Crown } from "@lucide/svelte";
    import type { Admin } from "$lib/types/users/admin";
    import { PlanID } from "$lib/types/plan";
    import { goto } from "$app/navigation";
    import View from "$lib/components/ui/View.svelte";

    let currentPlanId: PlanID | null = ($user as Admin)?.plan?.planID;

    type PlanCard = {
        id: PlanID;
        name: string;
        tag?: string;
        subtitle?: string;
        price?: string;
        period?: string;
        features: string[];
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
        },
    ];

    function selectPlan(planId: PlanID) {
        alert(`You have selected the ${planId} plan!`);
        goto("/dashboard");
    }
</script>

<main class="plans-page">
    <View>
        <h1 class="page-title">Choose Your Plan</h1>
        <div class="plans-grid">
            {#each plans as plan}
                <article
                    class="plan-card {plan.highlight
                        ? 'highlight'
                        : ''} {currentPlanId === plan.id ? 'current' : ''}"
                >
                    <header class="plan-header">
                        <div>
                            {#if plan.tag}
                                <span class="tag">{plan.tag}</span>
                            {/if}
                            <h2>{plan.name}</h2>
                            {#if plan.subtitle}
                                <p class="subtitle">{plan.subtitle}</p>
                            {/if}
                        </div>
                        <div class="price">
                            <span class="amount">{plan.price}</span>
                            {#if plan.period}
                                <span class="period">{plan.period}</span>
                            {/if}
                        </div>
                    </header>

                    <ul class="features">
                        {#each plan.features as feature}
                            <li><Check size={16} /> {feature}</li>
                        {/each}
                    </ul>

                    <div class="actions">
                        {#if currentPlanId === plan.id}
                            <Button disabled style="width:100%">
                                Current Plan
                            </Button>
                        {:else}
                            <Button
                                category="primary"
                                Icon={Crown}
                                style="width:100%"
                                onclick={() => selectPlan(plan.id)}
                            >
                                Choose Plan
                            </Button>
                        {/if}
                    </div>
                </article>
            {/each}
        </div>
    </View>
</main>

<style>
    .plans-page {
        padding: 2rem;
    }

    .page-title {
        text-align: center;
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 2rem;
    }

    .plans-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        justify-content: center;
        align-items: stretch;
    }

    .plan-card {
        background: #fff;
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 1rem;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all 0.25s ease;
    }

    .plan-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
    }

    .plan-card.highlight {
        border: 2px solid var(--color-primary);
        background: #f9fcff;
    }

    .plan-card.current {
        border: 2px solid var(--color-primary);
        background: #f1f9ff;
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
        color: #fff;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 999px;
        padding: 0.25rem 0.5rem;
        margin-bottom: 0.5rem;
    }

    h2 {
        font-size: 1.5rem;
        margin: 0;
    }

    .subtitle {
        font-size: 0.95rem;
        color: var(--text-muted);
        margin: 0.25rem 0 0;
    }

    .price {
        text-align: right;
    }

    .amount {
        font-size: 1.6rem;
        font-weight: bold;
        color: var(--color-primary);
    }

    .period {
        display: block;
        font-size: 0.9rem;
        color: var(--text-muted);
    }

    .features {
        list-style: none;
        margin: 1rem 0 1.5rem;
        padding: 0;
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

    @media (max-width: 768px) {
        .plans-page {
            padding: 1rem;
        }
        .plans-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
