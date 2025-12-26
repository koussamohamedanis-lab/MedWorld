<script lang="ts">
    import View from "$lib/components/ui/View.svelte";
    import Button from "$lib/components/ui/Button.svelte";
    import { Phone } from "@lucide/svelte";
    import { Check } from "@lucide/svelte";

    const plans = [
        {
            name: "Basic",
            price: "Free",
            subtitle: "Perfect for getting started",
            features: [
                "1 Doctor Account",
                "Basic Appointment Management",
                "Patient Records",
                "Email Notifications",
                "Standard Support",
            ],
            cta: {
                label: "Start Free",
                href: "/accounts/create/?plan=basic",
            },
            recommended: false,
        },

        {
            name: "Standard",
            price: "1900 DA",
            period: "/month",
            subtitle: "For growing medical practices",
            features: [
                "AI Features",
                "Up to 3 Doctor Accounts",
                "Advanced Appointment System",
                "Patient History & Analytics",
                "SMS Notifications",
                "Priority Support",
                "Custom Branding",
                "Online Payments",
            ],
            cta: {
                label: "Get Started",
                href: "/accounts/create/?plan=standard",
            },
            recommended: true,
        },

        {
            name: "Premium",
            price: "3800 DA",
            period: "/month",
            subtitle: "For large medical centers",
            features: [
                "Unlimited Doctor Accounts",
                "AI Features",
                "Full Practice Management",
                "Advanced Analytics & Reports",
                "Priority Support 24/7",
                "Custom Integration Options",
                "Staff Management",
                "Multi-Location Support",
                "Custom Feature Development",
            ],
            cta: {
                label: "Get Started",
                href: "/accounts/create/?plan=premium",
            },
            recommended: false,
        },
    ];
</script>

<main>
    <View center style="padding-top: 1.5rem;">
        <div class="container">
            <div class="header">
                <h1>Join Our Healthcare Network</h1>
                <p>
                    Choose the plan that best fits your medical practice needs
                </p>
            </div>

            <div class="cards">
                {#each plans as plan}
                    <div
                        class="card"
                        class:recommended={plan.recommended}
                        data-aos="flip-left"
                        data-aos-duration="1000"
                        data-aos-delay={plan.recommended ? "200" : "0"}
                    >
                        {#if plan.recommended}
                            <div class="recommended-badge">Most Popular</div>
                        {/if}
                        <div class="plan-header">
                            <h2>{plan.name}</h2>
                            <div class="price">
                                <span class="amount">{plan.price}</span>
                                {#if plan.period}
                                    <span class="period">{plan.period}</span>
                                {/if}
                            </div>
                            <p class="subtitle">{plan.subtitle}</p>
                        </div>

                        <ul class="features">
                            {#each plan.features as feature}
                                <li>
                                    <Check
                                        color="var(--color-primary-dark)"
                                        style="margin-right: 0.5rem;"
                                        size="16"
                                    />
                                    {feature}
                                </li>
                            {/each}
                        </ul>

                        <div class="card-footer">
                            <Button
                                category={plan.recommended
                                    ? "primary"
                                    : "secondary"}
                                label={plan.cta.label}
                                blink={plan.recommended}
                                href={plan.cta.href}
                                uppercase
                                style="width: 100%;"
                            />
                        </div>
                    </div>
                {/each}
            </div>

            <div class="footer">
                <h3>Need a custom solution?</h3>
                <p>
                    Contact our team for a tailored plan that fits your specific
                    requirements
                </p>

                <Button
                    large
                    Icon={Phone}
                    href="/contact"
                    category="primary"
                    label="Contact Us"
                />
            </div>
        </div>
    </View>
</main>

<style>
    main {
        padding-top: 2rem;
    }

    .container {
        width: 100%;
    }

    .header {
        text-align: center;
        margin-bottom: 4rem;
    }

    .header h1 {
        font-size: 3.5rem;
        color: var(--color-primary-dark);
        margin-bottom: 1rem;
    }

    .header p {
        font-size: 1.2rem;
        color: var(--color-text-secondary);
    }

    .cards {
        display: flex;
        align-items: flex-end;

        gap: 2rem;
        justify-content: center;
    }

    .card {
        background: var(--white);
        border-radius: var(--border-radius-lg);
        padding: 1.25rem;
        padding-top: 2rem;
        position: relative;
        box-shadow: 0 4px 12px rgba(var(--shadow-color-rgb), 0.1);
        transition: transform var(--transition-normal);
        display: flex;
        flex-direction: column;
        height: 100%;
        max-width: 350px;
        width: 100%;
    }

    .card:hover {
        transform: translateY(-5px);
    }

    .card.recommended {
        border: 2px solid var(--color-primary);
        box-shadow: 0 8px 24px rgba(var(--shadow-color-rgb), 0.15);
    }

    .recommended-badge {
        position: absolute;
        top: -12px;
        right: 20px;
        background: var(--color-primary);
        color: var(--white);
        padding: 0.25rem 1rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .plan-header {
        text-align: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid var(--color-border);
    }

    .plan-header h2 {
        color: var(--color-primary-dark);
        font-size: 2rem;
    }

    .card.recommended .plan-header h2 {
        color: var(--color-primary);
        font-size: 2.5rem;
    }

    .price {
        margin-bottom: 0.75rem;
    }

    .price .amount {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--color-primary);
    }

    .price .period {
        font-size: 0.875rem;
        color: var(--color-text-secondary);
    }

    .subtitle {
        color: var(--color-text-secondary);
        font-size: 0.875rem;
    }

    .features {
        list-style: none;
        padding: 0;
        margin: 0;
        flex-grow: 1;
    }

    .features li {
        display: flex;
        align-items: center;
        margin-bottom: 0.5rem;
        color: var(--color-text);
        font-size: 0.875rem;
    }

    .card-footer {
        margin-top: 1.5rem;
        text-align: center;
    }

    .card-footer :global(button) {
        padding: 0.5rem 1.25rem;
        font-size: 0.875rem;
    }

    .footer {
        text-align: center;
        margin-top: 4rem;
        padding: 3rem;
        background: var(--color-background-secondary);
        border-radius: var(--border-radius-lg);
    }

    .footer h3 {
        color: var(--color-primary-dark);
        margin-bottom: 1rem;
    }

    .footer p {
        color: var(--color-text-secondary);
        margin-bottom: 2rem;
    }

    @media (max-width: 768px) {
        .header h1 {
            font-size: 2rem;
        }

        .cards {
            flex-direction: column;
            align-items: center;
        }

        .card {
            max-width: 400px;
        }
    }
</style>
