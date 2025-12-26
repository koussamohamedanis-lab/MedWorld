<script lang="ts">
    import Button from "$lib/components/ui/Button.svelte";
    import Input from "$lib/components/ui/Input.svelte";
    import { Users } from "$lib/types/users";
    import { validate, validation, type Fillable } from "$lib/validation";
    import { User as UserIcon, UserPlus } from "@lucide/svelte";
    import { AuthAPI } from "$lib/api";
    import { ApiError } from "$lib/api/client";

    let data: Fillable = $state({
        firstName: {
            value: "",
            error: "",
            validator: validation.name,
        },
        lastName: {
            value: "",
            error: "",
            validator: validation.name,
        },
        email: {
            value: "",
            error: "",
            validator: validation.email,
        },

        password: {
            value: "",
            error: "",
            validator: validation.password,
        },
        phoneNumber: {
            value: "",
            error: "",
            validator: validation.phoneNumber,
        },

        address: {
            value: "",
            error: "",
            validator: validation.notEmpty,
        },

        gender: {
            value: "male",
            error: "",
            validator: validation.notEmpty,
        },
    });

    async function signUp() {
        let error = validate(data);
        if (error) {
            return alert(error);
        }

        const payload: any = {
            firstName: data.firstName.value,
            lastName: data.lastName.value,
            email: data.email.value,
            password: data.password.value,
            phoneNumber: data.phoneNumber.value,
            address: data.address.value,
            gender: data.gender.value,
            type: Users.Patient,
        };

        try {
            const response = await AuthAPI.register(payload);
            if (response && response.user) {
                localStorage.setItem("authToken", response.token);
                localStorage.setItem("user", JSON.stringify(response.user));
                window.location.href = "/dashboard";
                return;
            }
            alert("Registration failed. Please try again.");
        } catch (err) {
            if (err instanceof ApiError) {
                alert(err.message);
            } else {
                alert("An error occurred during registration. Please try again.");
            }
        }
    }
</script>

<main class="auth-container" data-aos="fade-up" data-aos-duration="1200">
    <section class="left">
        <div class="overlay"></div>
    </section>

    <section class="right" data-aos="flip-right" data-aos-duration="1200">
        <div class="form-container">
            <UserPlus size="72" style="margin-bottom: 1rem;" />

            <h2>Create your account</h2>

            <Input
                bind:value={data.firstName.value}
                bind:error={data.firstName.error}
                validation={data.firstName.validator}
                placeholder="First Name"
                type="text"
                theme="secondary"
                required
            />

            <Input
                bind:value={data.lastName.value}
                bind:error={data.lastName.error}
                validation={data.lastName.validator}
                placeholder="Last Name"
                type="text"
                theme="secondary"
                required
            />

            <Input
                bind:value={data.email.value}
                bind:error={data.email.error}
                validation={data.email.validator}
                placeholder="Email"
                type="email"
                theme="secondary"
                required
            />

            <Input
                bind:value={data.password.value}
                bind:error={data.password.error}
                validation={data.password.validator}
                placeholder="Password"
                type="password"
                theme="secondary"
                required
            />

            <Input
                bind:value={data.phoneNumber.value}
                bind:error={data.phoneNumber.error}
                validation={data.phoneNumber.validator}
                placeholder="Phone Number"
                type="tel"
                theme="secondary"
                required
            />

            <Input
                bind:value={data.address.value}
                bind:error={data.address.error}
                validation={data.address.validator}
                placeholder="Address"
                type="text"
                theme="secondary"
                required
            />

            <Input
                category="select"
                bind:value={data.gender.value}
                bind:error={data.gender.error}
                validation={data.gender.validator}
                placeholder="Gender"
                options={[
                    { value: "male", label: "Male" },
                    { value: "female", label: "Female" },
                ]}
                theme="secondary"
                required
            />

            <Button
                onClick={() => {
                    signUp();
                }}
                Icon={UserIcon}
                style="width: 100%;"
                category="primary">Sign Up</Button
            >

            <p class="switch">
                Already have an account?
                <a href="/accounts/login">Log in</a>
                <br />
                <a href="/dashboard">Goto Dashboard</a>
            </p>
        </div>
    </section>
</main>

<style>
    .auth-container {
        display: flex;
        min-height: calc(
            100vh - var(--nav-height) - var(--notification-height)
        );
    }

    .left {
        flex: 1;
        background: url("/cabinet.webp") center/cover no-repeat;
        position: relative;
        color: white;
    }

    .overlay {
        width: 100%;
        height: 100%;

        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;

        background: linear-gradient(
            to bottom,
            rgba(0, 0, 0, 0),
            rgba(0, 0, 0, 0.3)
        );
    }

    .right {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;

        padding: 2rem 0;
    }

    .form-container {
        width: 100%;
        max-width: 380px;
        text-align: center;
    }

    .form-container h2 {
        margin-bottom: 2rem;
    }

    .switch {
        margin-top: 1.5rem;
        font-size: 0.9rem;
    }

    .switch a {
        color: var(--color-primary);
        cursor: pointer;
    }

    @media (max-width: 768px) {
        .auth-container {
            flex-direction: column;
        }
        .left,
        .right {
            flex: none;
            width: 100%;
            height: auto;
        }
    }
</style>
