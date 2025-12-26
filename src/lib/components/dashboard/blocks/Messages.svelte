<script lang="ts">
    import Block from "$lib/components/ui/Block.svelte";
    import IconButton from "$lib/components/ui/IconButton.svelte";
    import Input from "$lib/components/ui/Input.svelte";
    import { user } from "$lib/stores";
    import { apiClient } from "$lib/api/client";
    import type { IDoctor } from "$lib/types/users";
    import type { Message } from "$lib/types/message";
    import type { Doctor } from "$lib/types/users/doctor";
    import type { Assistant } from "$lib/types/users/assistant";
    import type { Patient } from "$lib/types/users/patient";
    import { MessagesSquare, Send } from "@lucide/svelte";

    let messages: Message[] = $state(
        (($user as IDoctor)?.messages || []) as Message[],
    );
    let selectedReceiverId = $state<string>("");
    let receiverError = $state<string>("");
    let sending = $state(false);
    let loadError = $state<string>("");
    let recipientsLoading = $state(false);
    let recipientsError = $state<string>("");
    let staffRecipients = $state<Array<Doctor | Assistant>>([]);
    let patientRecipients = $state<Array<Patient>>([]);

    const messageValidator = (msg: string) => {
        return msg.trim().length > 0 ? "" : "Message cannot be empty.";
    };

    let formData = $state({
        message: {
            value: "",
            error: "",
            validator: messageValidator,
        },
    });

    function validateField(fieldName: keyof typeof formData) {
        const field = formData[fieldName];
        const validationError = field.validator
            ? field.validator(String(field.value ?? ""))
            : "";
        field.error = validationError;
    }

    $effect(() => {
        // keep in sync when user store updates
        messages = ((($user as IDoctor)?.messages || []) as Message[]) ?? [];
    });

    async function loadRecipients() {
        recipientsError = "";

        recipientsLoading = true;
        try {
            const res = await apiClient.get<{
                staff: Array<Doctor | Assistant>;
                patients: Patient[];
            }>("/messages/recipients");

            const currentUserId = ($user as any)?.id;

            const staff = (res?.staff || []).filter(
                (u: any) => u?.id && u.id !== currentUserId,
            );
            const patients = (res?.patients || []).filter(
                (u: any) => u?.id && u.id !== currentUserId,
            );

            const staffUnique = new Map<number, any>();
            for (const u of staff) staffUnique.set(u.id, u);
            staffRecipients = Array.from(staffUnique.values());

            const patientUnique = new Map<number, any>();
            for (const u of patients) patientUnique.set(u.id, u);
            patientRecipients = Array.from(patientUnique.values());
        } catch (err) {
            recipientsError =
                err instanceof Error
                    ? err.message
                    : "Failed to load recipients";
            staffRecipients = [];
            patientRecipients = [];
        } finally {
            recipientsLoading = false;
        }
    }

    $effect(() => {
        if ($user) {
            loadRecipients();
        } else {
            staffRecipients = [];
            patientRecipients = [];
        }
    });

    function getRecipientOptions() {
        const all = [...(staffRecipients || []), ...(patientRecipients || [])];
        const uniqueById = new Map<number, any>();
        for (const u of all) {
            if (!u?.id) continue;
            uniqueById.set(u.id, u);
        }

        return Array.from(uniqueById.values()).map((u: any) => ({
            value: String(u.id),
            label:
                u.fullName ||
                `${u.firstName || ""} ${u.lastName || ""}`.trim() ||
                `User #${u.id}`,
        }));
    }

    function getStaffOptions() {
        return (staffRecipients || []).map((u: any) => ({
            value: String(u.id),
            type: u.type,
            label:
                u.fullName ||
                `${u.firstName || ""} ${u.lastName || ""}`.trim() ||
                `User #${u.id}`,
        }));
    }

    function getPatientOptions() {
        return (patientRecipients || []).map((u: any) => ({
            value: String(u.id),
            label:
                u.fullName ||
                `${u.firstName || ""} ${u.lastName || ""}`.trim() ||
                `User #${u.id}`,
        }));
    }

    async function sendMessage() {
        validateField("message");
        receiverError = "";
        loadError = "";

        if (!selectedReceiverId) {
            receiverError = "Please choose a recipient.";
            return;
        }

        if (formData.message.error) {
            return;
        }

        const sender = $user;
        if (!sender) {
            loadError = "You must be logged in to send messages.";
            return;
        }

        sending = true;
        try {
            const receiverId = Number(selectedReceiverId);
            const content = String(formData.message.value);

            // Backend is expected to infer sender from auth, but we include senderId for compatibility.
            const created = await apiClient.post<Message>(
                "/messages",
                {
                    senderId: sender.id,
                    receiverId,
                    content,
                },
                false,
            );

            messages = [created, ...(messages || [])];
            formData.message.value = "";
            formData.message.error = "";
        } catch (err) {
            loadError =
                err instanceof Error ? err.message : "Failed to send message";
            console.error("Send message error:", err);
        } finally {
            sending = false;
        }
    }

    function getConversationMessages(withUserId: number) {
        const meId = $user?.id;
        if (!meId) return [];
        return (messages || [])
            .filter((m) => {
                const a = (m as any)?.sender?.id;
                const b = (m as any)?.receiver?.id;
                return (
                    (a === meId && b === withUserId) ||
                    (a === withUserId && b === meId)
                );
            })
            .slice()
            .sort(
                (x, y) =>
                    new Date(x.createdAt as any).getTime() -
                    new Date(y.createdAt as any).getTime(),
            );
    }

    function getSelectedRecipientLabel() {
        return (
            getRecipientOptions().find((o) => o.value === selectedReceiverId)
                ?.label || `User #${selectedReceiverId}`
        );
    }
</script>

<Block group="messages" title="Messages" Icon={MessagesSquare}>
    {#if loadError}
        <p class="error">Error: {loadError}</p>
    {/if}

    <div class="messaging">
        <aside class="sidebar">
            <Input
                category="select"
                theme="secondary"
                showLabel={true}
                label="Recipient"
                placeholder="Choose recipient"
                bind:value={selectedReceiverId}
                bind:error={receiverError}
                options={getRecipientOptions()}
                disabled={sending || recipientsLoading}
            />

            {#if recipientsError}
                <p class="error">Error: {recipientsError}</p>
            {/if}

            <div class="recipient-list" aria-label="Recipients">
                {#if recipientsLoading}
                    <p class="no-recipients">Loading recipients...</p>
                {:else}
                    <div class="recipient-section">
                        <div class="section-title">Staff</div>
                        {#each getStaffOptions() as opt}
                            <button
                                type="button"
                                class:selected={opt.value ===
                                    selectedReceiverId}
                                onclick={() => (selectedReceiverId = opt.value)}
                            >
                                {opt.label} ({opt.type})
                            </button>
                        {/each}
                        {#if getStaffOptions().length === 0}
                            <p class="no-recipients">No staff available.</p>
                        {/if}
                    </div>

                    <div class="recipient-section">
                        <div class="section-title">Patients</div>
                        {#each getPatientOptions() as opt}
                            <button
                                type="button"
                                class:selected={opt.value ===
                                    selectedReceiverId}
                                onclick={() => (selectedReceiverId = opt.value)}
                            >
                                {opt.label}
                            </button>
                        {/each}
                        {#if getPatientOptions().length === 0}
                            <p class="no-recipients">No patients available.</p>
                        {/if}
                    </div>
                {/if}
            </div>
        </aside>

        <section class="chat">
            {#if selectedReceiverId}
                <header class="chat-header">
                    <h3>
                        Chat with
                        {getSelectedRecipientLabel()}
                    </h3>
                </header>

                <div class="chat-messages">
                    {#each getConversationMessages(Number(selectedReceiverId)) as message}
                        <div
                            class="bubble"
                            class:mine={message.sender?.id === $user?.id}
                        >
                            <div class="meta">
                                <span class="sender">
                                    {message.sender?.id === $user?.id
                                        ? "You"
                                        : message.sender?.fullName ||
                                          `${message.sender?.firstName || ""} ${message.sender?.lastName || ""}`.trim() ||
                                          `User #${message.sender?.id}`}
                                </span>
                                <span class="date">
                                    {new Date(
                                        message.createdAt,
                                    ).toLocaleString()}
                                </span>
                            </div>
                            <div class="content">{message.content}</div>
                        </div>
                    {/each}
                    {#if getConversationMessages(Number(selectedReceiverId)).length === 0}
                        <p class="no-messages">No messages yet. Say hi.</p>
                    {/if}
                </div>

                <div class="composer">
                    <Input
                        bind:value={formData.message.value}
                        bind:error={formData.message.error}
                        validation={formData.message.validator}
                        placeholder="Type a message..."
                        disabled={sending}
                    />
                    <IconButton
                        Icon={Send}
                        label={sending ? "Sending..." : "Send"}
                        onclick={sendMessage}
                        disabled={sending}
                    />
                </div>
            {:else}
                <div class="empty-chat">
                    <p>Select a recipient to start chatting.</p>
                </div>
            {/if}
        </section>
    </div>
</Block>

<style>
    .messaging {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 1rem;
        min-height: 420px;
    }

    .sidebar {
        border: 1px solid var(--border-color, #e5e7eb);
        border-radius: 1rem;
        padding: 1rem;
        background: var(--background-secondary, #f9fafb);
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .recipient-list {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        overflow: auto;
        padding-right: 0.25rem;
    }

    .recipient-section {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .section-title {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-muted, #6b7280);
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-top: 0.25rem;
    }

    .recipient-list button {
        text-align: left;
        width: 100%;
        border: 1px solid var(--border-color, #e5e7eb);
        background: #fff;
        border-radius: 0.75rem;
        padding: 0.75rem;
        cursor: pointer;
    }

    .recipient-list button.selected {
        border-color: #111827;
        background: #f3f4f6;
    }

    .no-recipients {
        margin: 0;
        color: var(--text-muted, #6b7280);
        font-style: italic;
    }

    .chat {
        border: 1px solid var(--border-color, #e5e7eb);
        border-radius: 1rem;
        overflow: hidden;
        background: #fff;
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .chat-header {
        padding: 1rem;
        background: var(--background-secondary, #f9fafb);
        border-bottom: 1px solid var(--border-color, #e5e7eb);
    }

    .chat-header h3 {
        margin: 0;
        font-size: 1rem;
        font-weight: 600;
    }

    .chat-messages {
        padding: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        overflow: auto;
        flex: 1;
    }

    .bubble {
        max-width: 80%;
        border: 1px solid var(--border-color, #e5e7eb);
        border-radius: 1rem;
        padding: 0.75rem;
        background: #fff;
    }

    .bubble.mine {
        margin-left: auto;
        background: #f3f4f6;
    }

    .meta {
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.35rem;
        color: var(--text-muted, #6b7280);
        font-size: 0.8rem;
    }

    .content {
        color: var(--text-secondary, #374151);
        white-space: pre-wrap;
        word-break: break-word;
    }

    .composer {
        padding: 0.75rem 1rem;
        border-top: 1px solid var(--border-color, #e5e7eb);
        display: flex;
        gap: 0.75rem;
        align-items: flex-end;
        background: #fff;
    }

    .empty-chat {
        padding: 2rem;
        color: var(--text-muted, #6b7280);
    }

    h3 {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-primary, #111827);
        margin: 0;
    }

    span {
        font-size: 0.85rem;
        color: var(--text-muted, #6b7280);
    }

    p {
        font-size: 0.95rem;
        color: var(--text-secondary, #374151);
        line-height: 1.5;
        margin: 0;
    }

    p:empty::before {
        content: "No content";
        color: #9ca3af;
        font-style: italic;
    }

    .no-messages {
        text-align: center;
        padding: 2rem 0;
        color: var(--text-muted, #6b7280);
        font-style: italic;
    }

    .error {
        margin: 0 0 1rem;
        color: #b91c1c;
    }
</style>
