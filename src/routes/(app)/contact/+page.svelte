<script lang="ts">
    import { SITE_NAME } from "$lib";

    import Button from "$lib/components/ui/Button.svelte";
    import Input from "$lib/components/ui/Input.svelte";
    import View from "$lib/components/ui/View.svelte";
    import {
        validate,
        validation,
        type Fillable,
        extract,
    } from "$lib/validation";

    import { Phone, Send } from "@lucide/svelte";

    const messageValidator = (message: string) => {
        return message.trim().length >= 10
            ? ""
            : "Message must be at least 10 characters long.";
    };

    let data: Fillable = $state({
        fullName: {
            value: "",
            error: "",
            validator: validation.fullName,
        },
        email: {
            value: "",
            error: "",
            validator: validation.email,
        },
        phoneNumber: {
            value: "",
            error: "",
            validator: validation.phoneNumber,
        },
        message: {
            value: "",
            error: "",
            validator: messageValidator,
        },
    });

    function validateField(fieldName: keyof typeof data) {
        const field = data[fieldName];
        const validationError = field.validator(field.value);
        field.error = validationError;
    }

    function isFormValid(): boolean {
        (Object.keys(data) as (keyof typeof data)[]).forEach(validateField);

        return validate(data) === "";
    }

    let submitStatus = $state({
        loading: false,
        success: false,
        error: null as string | null,
    });

    function handleSubmit(event: Event) {
        event.preventDefault();

        if (!isFormValid()) {
            submitStatus.error = "Please correct the errors in the form.";
            const firstErrorField = Object.keys(data).find(
                (key) => data[key as keyof typeof data].error !== "",
            );
            if (firstErrorField) {
                const inputElement = document.querySelector(
                    `[name="${firstErrorField}"]`,
                ) as HTMLInputElement;
                if (inputElement) inputElement.focus();
            }
            return;
        }

        submitStatus.error = null;
        submitStatus.loading = true;

        const payload = extract(data);

        fetch("/api/submit", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(payload),
        })
            .then((response) => {
                if (!response.ok) {
                    return response.json().then((err) => {
                        throw new Error(
                            err.message ||
                                "Submission failed due to server error.",
                        );
                    });
                }
                return response.json();
            })
            .then((data) => {
                submitStatus.loading = false;
                submitStatus.success = true;
                console.log("Form submitted successfully:", data);
            })
            .catch((error) => {
                submitStatus.loading = false;
                submitStatus.success = false;
                submitStatus.error = error.message;
                console.error("Error submitting form:", error);
            });
    }
</script>

<svelte:head>
    <title>Contact Us | {SITE_NAME}</title>
    <meta
        name="description"
        content="Contact us for any questions or requests for quotes. We are here to help."
    />
</svelte:head>

<main>
    <View align="column" center>
        <div class="hero-section">
            <Phone size="5rem" style="margin-bottom: 2rem;" />
            <h1 data-aos="fade-up" data-aos-duration="800">
                Contact {SITE_NAME}
            </h1>
            <p data-aos="fade-up" data-aos-duration="800">
                Feel free to contact us for any questions or requests for
                quotes, or via Whatsapp or phone.
            </p>
        </div>

        <form
            on:submit={handleSubmit}
            class="contact-form"
            data-aos="fade-up"
            data-aos-duration="800"
        >
            <Input
                type="text"
                name="fullName"
                required
                bind:value={data.fullName.value}
                on:input={() => validateField("fullName")}
                on:blur={() => validateField("fullName")}
                error={data.fullName.error}
                label="Full Name"
                placeholder="John Doe"
            />

            <Input
                type="email"
                name="email"
                required
                bind:value={data.email.value}
                on:input={() => validateField("email")}
                on:blur={() => validateField("email")}
                error={data.email.error}
                label="Email"
                placeholder="your@email.com"
            />

            <Input
                type="tel"
                name="phoneNumber"
                required
                bind:value={data.phoneNumber.value}
                on:input={() => validateField("phoneNumber")}
                on:blur={() => validateField("phoneNumber")}
                error={data.phoneNumber.error}
                label="Phone Number"
                placeholder="0XXX XXX XXX (e.g., 0555123456)"
            />

            <Input
                category="textarea"
                bind:value={data.message.value}
                on:input={() => validateField("message")}
                on:blur={() => validateField("message")}
                error={data.message.error}
                name="message"
                required
                label="Message"
                placeholder="Your message here..."
            />

            <Button
                variant="primary"
                style="width: 100%;"
                large
                iconPosition="right"
                Icon={Send}
                type="submit"
                fullWidth
                disabled={submitStatus.loading}
            >
                {submitStatus.loading ? "Sending..." : "Send Message"}
            </Button>

            <p class="status">
                {#if submitStatus.success}
                    <span class="text-green-500"
                        >Message sent successfully!</span
                    >
                {:else if submitStatus.error}
                    <span class="text-red-500">Error: {submitStatus.error}</span
                    >
                {/if}
            </p>
        </form>
    </View>
</main>

<style>
    .status {
        text-align: center;
        margin-top: 1rem;
        font-size: 0.875rem;
    }

    .text-red-500 {
        color: #ef4444;
    }

    .text-green-500 {
        color: #10b981;
    }

    main {
        min-height: 100vh;
        padding-top: 6rem;
        padding-bottom: 4rem;
    }

    form {
        max-width: 1000px;
        width: 100%;

        background-color: var(--background-light);
        padding: 2rem;
        border-radius: 0.75rem;
        box-shadow:
            0 10px 15px -3px rgba(0, 0, 0, 0.1),
            0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .contact-form {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .hero-section {
        text-align: center;
        max-width: 800px;

        color: var(--color-primary-dark);
        margin-bottom: 3rem;
    }

    .hero-section h1 {
        font-size: clamp(2.5rem, 8vw, 4rem);
        font-weight: 600;
        margin-bottom: 1.5rem;
        color: var(--text-color);
        font-family: var(--font-primary);
    }

    .hero-section p {
        font-size: 1.125rem;
        color: var(--text-light);
        line-height: 1.7;
        max-width: 600px;
        margin: 0 auto;
    }

    @media (max-width: 768px) {
        main {
            padding-top: calc(var(--header-height) + 1rem);
            padding-bottom: 2rem;
        }

        .hero-section {
            margin-bottom: 2.5rem;
            padding: 0 0.5rem;
        }

        .hero-section h1 {
            font-size: 2.5rem;
        }

        .hero-section p {
            font-size: 1rem;
        }
    }

    @media (max-width: 480px) {
        .hero-section h1 {
            font-size: 2rem;
        }
    }
</style>
