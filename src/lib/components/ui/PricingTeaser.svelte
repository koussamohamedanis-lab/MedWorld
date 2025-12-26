<script>
    import Button from "./Button.svelte";
    import { Check } from "@lucide/svelte";
</script>

<section class="pricing-teaser">
    <div class="container">
        <h2>Simple, transparent pricing</h2>
        <p class="subtitle">Start free, upgrade when you're ready</p>

        <div class="plan">
            <div class="plan-header">
                <h3>Pro Plan</h3>
                <div class="price">
                    <span class="amount">$49</span>
                    <span class="period">/month</span>
                </div>
            </div>

            <ul class="features">
                <li>
                    <Check size={16} />
                    <span>Unlimited patients</span>
                </li>
                <li>
                    <Check size={16} />
                    <span>Smart scheduling</span>
                </li>
                <li>
                    <Check size={16} />
                    <span>Secure messaging</span>
                </li>
                <li>
                    <Check size={16} />
                    <span>Team access</span>
                </li>
            </ul>

            <Button href="/pricing" category="primary" style="width: 100%">
                View all plans
            </Button>
        </div>
    </div>
</section>

<style>
    .pricing-teaser {
        padding: 4rem 0;
        background: var(--surface-alt, #f8f9fa);
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1rem;
        text-align: center;
    }

    h2 {
        margin: 0 0 0.5rem 0;
        font-size: 2rem;
    }

    .subtitle {
        color: var(--text-muted);
        margin-bottom: 2rem;
    }

    .plan {
        max-width: 400px;
        margin: 0 auto;
        padding: 2rem;
        background: white;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .plan-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .plan h3 {
        margin: 0 0 1rem 0;
        font-size: 1.5rem;
    }

    .price {
        font-size: 2.5rem;
        font-weight: 600;
        color: var(--primary-color);
    }

    .period {
        font-size: 1rem;
        font-weight: normal;
        color: var(--text-muted);
    }

    .features {
        margin: 0 0 2rem 0;
        padding: 0;
        list-style: none;
    }

    .features li {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
        color: var(--text-color);
    }

    .features :global(svg) {
        color: var(--primary-color);
    }

    @media (max-width: 768px) {
        .pricing-teaser {
            padding: 3rem 0;
        }

        h2 {
            font-size: 1.75rem;
        }

        .plan {
            padding: 1.5rem;
        }
    }
</style>
