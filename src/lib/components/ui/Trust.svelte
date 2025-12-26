<script>
    import { Shield, Star, Users } from "@lucide/svelte";
</script>

<section class="trust">
    <div class="container">
        <div class="stats">
            <div class="stat">
                <Shield size={24} />
                <h3>100% Secure</h3>
                <p>HIPAA-compliant data protection</p>
            </div>
            <div class="stat">
                <Users size={24} />
                <h3>500+ Doctors</h3>
                <p>Trust our platform daily</p>
            </div>
            <div class="stat">
                <Star size={24} />
                <h3>4.9/5 Rating</h3>
                <p>From verified users</p>
            </div>
        </div>

        <div class="testimonial">
            <blockquote>
                "This platform has transformed how I manage my practice. The
                interface is intuitive and the support is exceptional."
            </blockquote>
            <cite>
                <img src="/avatar-placeholder.png" alt="Dr. Sarah Mitchell" />
                <div>
                    <strong>Dr. Sarah Mitchell</strong>
                    <span>Family Practice, NYC</span>
                </div>
            </cite>
        </div>
    </div>
</section>

<style>
    .trust {
        padding: 4rem 0;
        background: white;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1rem;
    }

    .stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 2rem;
        margin-bottom: 4rem;
        text-align: center;
    }

    .stat {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
    }

    .stat :global(svg) {
        color: var(--primary-color);
    }

    .stat h3 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 600;
    }

    .stat p {
        margin: 0;
        color: var(--text-muted);
    }

    .testimonial {
        max-width: 800px;
        margin: 0 auto;
        text-align: center;
    }

    blockquote {
        font-size: 1.25rem;
        font-style: italic;
        margin: 0 0 2rem 0;
        color: var(--text-color);
    }

    cite {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1rem;
    }

    cite img {
        width: 48px;
        height: 48px;
        border-radius: 50%;
    }

    cite div {
        text-align: left;
    }

    cite strong {
        display: block;
        font-style: normal;
    }

    cite span {
        color: var(--text-muted);
        font-size: 0.875rem;
    }

    @media (max-width: 768px) {
        .trust {
            padding: 3rem 0;
        }

        .stats {
            gap: 1.5rem;
        }

        blockquote {
            font-size: 1.125rem;
        }
    }
</style>
