<script>
  import { SITE_DESCRIPTION, SITE_NAME } from "$lib";
  import Button from "$lib/components/ui/Button.svelte";
  import Face from "$lib/components/ui/Face.svelte";
  import View from "$lib/components/ui/View.svelte";
  import {
    ArrowRight,
    Building2,
    Users,
    Calendar,
    MessageSquare,
    Clock,
    FileText,
    BellRing,
    BarChart3,
  } from "@lucide/svelte";

  const stats = [
    { number: "2,500+", label: "Registered Doctors" },
    { number: "150,000+", label: "Patient Consultations" },
    { number: "98%", label: "Patient Satisfaction" },
    { number: "24/7", label: "Support Available" },
  ];

  const solutions = [
    {
      icon: Calendar,
      title: "Smart Scheduling",
      description:
        "Efficient appointment management with automated reminders and scheduling conflicts resolution.",
    },
    {
      icon: FileText,
      title: "Digital Records",
      description:
        "Secure electronic health records with easy access and HIPAA-compliant storage.",
    },
    {
      icon: MessageSquare,
      title: "Patient Communication",
      description:
        "Integrated messaging system for seamless doctor-patient communication.",
    },
    {
      icon: BellRing,
      title: "Smart Notifications",
      description:
        "Automated reminders for appointments, follow-ups, and medication schedules.",
    },
    {
      icon: Clock,
      title: "Real-time Updates",
      description: "Live updates on waiting times and doctor availability.",
    },
    {
      icon: BarChart3,
      title: "Analytics Dashboard",
      description:
        "Comprehensive insights into practice performance and patient trends.",
    },
  ];

  const cabinets = [
    {
      name: "General Medicine",
      patients: "50K+",
      description: "Complete primary healthcare management solution.",
    },
    {
      name: "Dental Care",
      patients: "30K+",
      description: "Specialized tools for dental practice management.",
    },
    {
      name: "Pediatrics",
      patients: "25K+",
      description: "Child-focused healthcare management system.",
    },
  ];
</script>

<Face title="Landing Page" description={SITE_DESCRIPTION}>
  <div class="landing">
    <section class="hero-section">
      <video src="/hero.mp4" autoplay muted loop poster="./hero-cover.png"
      ></video>

      <div class="hero">
        <View style="justify-content: end; padding: 2rem;">
          <h1>Digitally taking care</h1>
          <p class="hero-text">
            {SITE_NAME} - Empowering your cabinets with smart digital technology.
          </p>

          <div class="actions">
            <Button category="third" href="/about">Learn more</Button>
            <Button
              large
              blink
              category="primary"
              href="/accounts/create"
              Icon={ArrowRight}
              iconPosition="right">Get Started</Button
            >
          </div>
        </View>
      </div>
    </section>

    <section class="stats">
      <View>
        <div class="stats-grid">
          {#each stats as stat}
            <div class="stat-card">
              <h3>{stat.number}</h3>
              <p>{stat.label}</p>
            </div>
          {/each}
        </div>
      </View>
    </section>

    <section class="cabinets">
      <View>
        <div class="section-header">
          <h2>Our Medical Cabinets</h2>
          <p>Trusted by healthcare professionals across various specialties</p>
        </div>

        <div class="cabinets-grid">
          {#each cabinets as cabinet}
            <div class="cabinet-card">
              <Building2 size={32} />
              <h3>{cabinet.name}</h3>
              <p class="patients">{cabinet.patients} Patients</p>
              <p>{cabinet.description}</p>
            </div>
          {/each}
        </div>

        <div class="section-footer">
          <Button
            href="/cabinets"
            category="primary"
            Icon={ArrowRight}
            iconPosition="right"
            large
            blink
          >
            Visit Our Cabinets
          </Button>
        </div>
      </View>
    </section>

    <section class="solutions">
      <View>
        <div class="section-header">
          <h2>Our Solutions</h2>
          <p>Comprehensive tools to streamline your medical practice</p>
        </div>

        <div class="solutions-grid">
          {#each solutions as solution}
            <div class="solution-card">
              <svelte:component this={solution.icon} size={32} />
              <h3>{solution.title}</h3>
              <p>{solution.description}</p>
            </div>
          {/each}
        </div>
      </View>
    </section>

    <section class="cta">
      <View>
        <div class="cta-content">
          <h2>Ready to Transform Your Practice?</h2>
          <p>
            Join thousands of healthcare professionals who trust {SITE_NAME}
          </p>
          <Button
            large
            category="primary"
            href="/accounts/create"
            Icon={ArrowRight}
            iconPosition="right">Start Your Journey</Button
          >
        </div>
      </View>
    </section>
  </div>
</Face>

<style>
  .landing {
    background: var(--color-background);
  }

  .hero-section {
    position: relative;
    height: calc(70vh - var(--nav-height));
    overflow: hidden;
    background-image: url("./hero-cover.png");
    background-size: cover;
    background-position: center;

    padding: 0;
  }

  .hero {
    z-index: 100;
    position: relative;
    height: 100%;
    background-image: linear-gradient(transparent, rgba(0, 0, 0, 0.776));

    display: flex;
    flex-direction: column;
    justify-content: end;
    height: 100%;
  }

  .hero h1 {
    color: white;
    font-size: 3rem;
    margin-bottom: 1rem;
    font-weight: 400;
  }

  .hero-text {
    color: white;
    font-size: 1.5rem;
    max-width: 600px;
  }

  .hero-section video {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
  }

  .actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    align-items: center;
    margin-top: 2rem;
  }

  section:not(.hero-section) {
    padding: 5rem 0;
  }

  .section-header {
    text-align: center;
    margin-bottom: 3rem;
  }

  .section-header h2 {
    font-size: 2.5rem;
    color: var(--color-primary-dark);
    margin-bottom: 1rem;
  }

  .section-header p {
    font-size: 1.2rem;
    color: var(--color-text-secondary);
  }

  .stats {
    background: var(--color-background-secondary);
  }

  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 2rem;
    text-align: center;
  }

  .stat-card h3 {
    font-size: 2.5rem;
    color: var(--color-primary);
    margin-bottom: 0.5rem;
  }

  .stat-card p {
    color: var(--color-text-secondary);
    font-size: 1.1rem;
  }

  .cabinets-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
  }

  .cabinet-card {
    background: var(--white);
    padding: 2rem;
    border-radius: var(--border-radius-lg);
    box-shadow: 0 4px 12px rgba(var(--shadow-color-rgb), 0.1);
    text-align: center;
  }

  .cabinet-card h3 {
    margin: 1rem 0;
    color: var(--color-primary-dark);
  }

  .cabinet-card .patients {
    color: var(--color-primary);
    font-weight: 600;
    margin-bottom: 0.5rem;
  }

  .cabinet-card p {
    color: var(--color-text-secondary);
  }

  .solutions {
    background: var(--color-background-secondary);
  }

  .solutions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2rem;
  }

  .solution-card {
    background: var(--white);
    padding: 2rem;
    border-radius: var(--border-radius-lg);
    box-shadow: 0 4px 12px rgba(var(--shadow-color-rgb), 0.1);
  }

  .solution-card h3 {
    margin: 1rem 0;
    color: var(--color-primary-dark);
  }

  .solution-card p {
    color: var(--color-text-secondary);
  }

  .section-footer {
    text-align: center;
    margin-top: 3rem;
  }

  .cta {
    background: linear-gradient(
      135deg,
      var(--color-primary) 0%,
      var(--color-primary-dark) 100%
    );
  }

  .cta-content {
    text-align: center;
    max-width: 800px;
    margin: 0 auto;
  }

  .cta-content h2 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    color: var(--white);
  }

  .cta-content p {
    font-size: 1.2rem;
    margin-bottom: 2rem;
    opacity: 0.9;
    color: var(--white);
  }

  @media (max-width: 768px) {
    .hero h1 {
      font-size: 2rem;
    }

    .hero-text {
      font-size: 1.2rem;
    }

    .section-header h2 {
      font-size: 2rem;
    }

    .stat-card h3 {
      font-size: 2rem;
    }

    .cta-content h2 {
      font-size: 2rem;
    }

    section {
      padding: 3rem 0;
    }
  }
</style>
