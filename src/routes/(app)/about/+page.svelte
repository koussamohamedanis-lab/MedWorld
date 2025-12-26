<script lang="ts">
    import View from "$lib/components/ui/View.svelte";
    import Button from "$lib/components/ui/Button.svelte";
    import LogoFull from "$lib/components/LogoFull.svelte";
</script>

<main>
    <View data-aos="fade-up">
        <div class="about-container">
            <LogoFull />

            <div class="mission-section">
                <div
                    class="cta-section"
                    data-aos="fade-up"
                    data-aos-duration="1000"
                >
                    <h2>Join Our Healthcare Network</h2>
                    <p>
                        Whether you're a healthcare provider looking to digitize
                        your practice or a patient seeking better healthcare
                        management, MedWorld is here for you.
                    </p>
                    <div class="stats-section">
                        <div class="stat-item">
                            <h3>1000+</h3>
                            <p>Registered Doctors</p>
                        </div>
                        <div class="stat-item">
                            <h3>500+</h3>
                            <p>Medical Cabinets</p>
                        </div>
                        <div class="stat-item">
                            <h3>58</h3>
                            <p>Wilayas Covered</p>
                        </div>
                    </div>

                    <div class="cta-buttons">
                        <Button
                            href="accounts/register"
                            category="secondary"
                            label="Register"
                            large
                        />
                    </div>
                </div>

                <h2>Our Mission</h2>
                <p>
                    MedWorld is dedicated to transforming healthcare management
                    in Algeria by connecting patients with quality healthcare
                    providers through innovative digital solutions. We aim to
                    make healthcare more accessible, efficient, and transparent
                    for everyone.
                </p>
            </div>

            <div class="features-grid">
                <div
                    class="feature-card"
                    data-aos="flip-left"
                    data-aos-delay="100"
                    data-aos-duration="1000"
                >
                    <h3>Digital Appointments</h3>
                    <p>
                        Easy online booking system that saves time and reduces
                        waiting periods for both patients and medical staff.
                    </p>
                </div>
                <div
                    class="feature-card"
                    data-aos="flip-left"
                    data-aos-delay="200"
                >
                    <h3>Cabinet Management</h3>
                    <p>
                        Comprehensive tools for medical practices to manage
                        their operations, staff, and patient relationships
                        efficiently.
                    </p>
                </div>
                <div
                    class="feature-card"
                    data-aos="flip-left"
                    data-aos-delay="300"
                >
                    <h3>Patient Care</h3>
                    <p>
                        Secure patient records, appointment history, and
                        communication channels between patients and healthcare
                        providers.
                    </p>
                </div>
            </div>
        </div>
    </View>
</main>

<style>
    .about-container {
        padding: 4rem 0;

        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .mission-section {
        text-align: center;
        max-width: 800px;
        margin: 0 auto 4rem;
    }

    .mission-section h2 {
        color: var(--color-primary-dark);
        margin-bottom: 1.5rem;
    }

    .mission-section p {
        font-size: 1.2rem;
        line-height: 1.6;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
        margin-bottom: 4rem;
    }

    .feature-card {
        background: var(--white);
        padding: 2rem;
        border-radius: var(--border-radius-lg);
        box-shadow: 0 4px 12px rgba(var(--shadow-color-rgb), 0.1);
        transition: transform var(--transition-normal);
    }

    .feature-card:hover {
        transform: translateY(-5px);
    }

    .feature-card h3 {
        color: var(--color-primary-dark);
        margin-bottom: 1rem;
    }

    .stats-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 2rem;
        text-align: center;
    }

    .stat-item h3 {
        font-size: 2.5rem;
        color: var(--color-primary-dark);
        margin-bottom: 0.5rem;
    }

    .stat-item p {
        color: var(--color-text-secondary);
        font-size: 1.1rem;
    }

    .cta-section {
        text-align: center;
        background: var(--color-background-secondary);
        padding: 2rem 0;
        border-radius: var(--border-radius-lg);

        margin-bottom: 4rem;
    }

    .cta-section h2 {
        color: var(--color-primary-dark);
        margin-bottom: 1.5rem;
    }

    .cta-section p {
        max-width: 600px;
        margin: 0 auto 2rem;
        font-size: 1.1rem;
    }

    .cta-buttons {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    @media (max-width: 768px) {
        .mission-section p {
            font-size: 1.1rem;
        }

        .stat-item h3 {
            font-size: 2rem;
        }

        .cta-section {
            padding: 3rem 1.5rem;
        }

        .cta-buttons {
            flex-direction: column;
        }
    }
</style>
