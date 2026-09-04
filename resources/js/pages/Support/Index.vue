<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import {
    ChevronDown,
    ChevronUp,
    LifeBuoy,
    MessageCircle,
    Plus,
    Send,
} from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{ tickets: any[] }>();
const expanded = ref<number | null>(null);
const replies = ref<Record<number, string>>({});
const form = useForm({ subject: '', category: 'question', message: '' });
const labels: Record<string, string> = {
    open: 'Aberto',
    in_progress: 'Em atendimento',
    waiting_customer: 'Aguardando sua resposta',
    resolved: 'Resolvido',
    closed: 'Encerrado',
    question: 'Dúvida',
    suggestion: 'Sugestão',
    technical: 'Apoio técnico',
    billing: 'Pagamento ou cobrança',
    other: 'Outro',
};
function createTicket() {
    form.post('/painel/suporte', {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}
function reply(ticket: any) {
    useForm({ message: replies.value[ticket.id] }).post(
        `/painel/suporte/${ticket.id}/responder`,
        {
            preserveScroll: true,
            onSuccess: () => {
                replies.value[ticket.id] = '';
            },
        },
    );
}
function handleReplyKeydown(event: KeyboardEvent, ticket: any) {
    if (event.key !== 'Enter' || event.shiftKey || event.isComposing) return;
    event.preventDefault();
    if (replies.value[ticket.id]?.trim()) reply(ticket);
}
const date = (value: string) =>
    new Intl.DateTimeFormat('pt-BR', {
        dateStyle: 'short',
        timeStyle: 'short',
    }).format(new Date(value));
</script>

<template>
    <Head title="Atendimento e suporte" />
    <AppLayout
        :breadcrumbs="[
            { title: 'Painel', href: '/painel' },
            { title: 'Suporte', href: '/painel/suporte' },
        ]"
    >
        <div class="mx-auto max-w-5xl space-y-6 p-4 md:p-6">
            <header>
                <div class="flex items-center gap-2 text-primary">
                    <LifeBuoy class="h-5 w-5" /><span
                        class="text-sm font-semibold"
                        >Central de atendimento</span
                    >
                </div>
                <h1 class="mt-1 text-2xl font-bold text-foreground">
                    Como podemos ajudar?
                </h1>
                <p class="text-sm text-muted-foreground">
                    Envie dúvidas, sugestões ou solicitações técnicas e
                    acompanhe todas as respostas.
                </p>
            </header>
            <section
                class="rounded-2xl border border-border bg-card p-5 shadow-sm"
            >
                <h2
                    class="flex items-center gap-2 font-semibold text-foreground"
                >
                    <Plus class="h-4 w-4" /> Abrir novo chamado
                </h2>
                <form
                    class="mt-4 grid gap-3 sm:grid-cols-2"
                    @submit.prevent="createTicket"
                >
                    <input
                        v-model="form.subject"
                        class="rounded-xl border border-input bg-background p-3 text-sm text-foreground sm:col-span-2"
                        placeholder="Resuma o que você precisa"
                    /><select
                        v-model="form.category"
                        class="rounded-xl border border-input bg-background p-3 text-sm text-foreground"
                    >
                        <option
                            v-for="category in [
                                'question',
                                'suggestion',
                                'technical',
                                'billing',
                                'other',
                            ]"
                            :key="category"
                            :value="category"
                        >
                            {{ labels[category] }}
                        </option>
                    </select>
                    <div class="hidden sm:block"></div>
                    <textarea
                        v-model="form.message"
                        rows="4"
                        class="rounded-xl border border-input bg-background p-3 text-sm text-foreground sm:col-span-2"
                        placeholder="Conte os detalhes para que possamos ajudar melhor"
                    ></textarea>
                    <p
                        v-if="Object.keys(form.errors).length"
                        class="text-sm text-destructive sm:col-span-2"
                    >
                        Preencha o assunto e os detalhes do chamado.
                    </p>
                    <button
                        class="rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-primary-foreground sm:col-span-2"
                        :disabled="form.processing"
                    >
                        Enviar chamado
                    </button>
                </form>
            </section>
            <section class="space-y-3">
                <h2 class="font-semibold text-foreground">Seus chamados</h2>
                <article
                    v-for="ticket in tickets"
                    :key="ticket.id"
                    class="overflow-hidden rounded-2xl border border-border bg-card"
                >
                    <button
                        class="flex w-full items-center justify-between gap-3 p-5 text-left"
                        @click="
                            expanded = expanded === ticket.id ? null : ticket.id
                        "
                    >
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <b class="text-foreground"
                                    >{{ ticket.number }} ·
                                    {{ ticket.subject }}</b
                                ><span
                                    class="rounded-full bg-primary/10 px-2 py-1 text-xs font-semibold text-primary"
                                    >{{ labels[ticket.status] }}</span
                                >
                            </div>
                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ labels[ticket.category] }} · Atualizado em
                                {{ date(ticket.last_activity_at) }}
                            </p>
                        </div>
                        <ChevronUp
                            v-if="expanded === ticket.id"
                            class="h-5 w-5"
                        /><ChevronDown v-else class="h-5 w-5" />
                    </button>
                    <div
                        v-if="expanded === ticket.id"
                        class="space-y-3 border-t border-border p-5"
                    >
                        <div
                            v-for="message in ticket.messages"
                            :key="message.id"
                            class="flex gap-3"
                        >
                            <div
                                class="mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-muted"
                            >
                                <MessageCircle class="h-4 w-4" />
                            </div>
                            <div class="flex-1 rounded-xl bg-muted/60 p-3">
                                <div class="flex justify-between gap-2 text-xs">
                                    <b>{{
                                        message.author?.is_admin
                                            ? 'Equipe de atendimento'
                                            : 'Você'
                                    }}</b
                                    ><span class="text-muted-foreground">{{
                                        date(message.created_at)
                                    }}</span>
                                </div>
                                <p
                                    class="mt-1 text-sm whitespace-pre-wrap text-foreground"
                                >
                                    {{ message.message }}
                                </p>
                            </div>
                        </div>
                        <form
                            v-if="ticket.status !== 'closed'"
                            class="flex items-end gap-2"
                            @submit.prevent="reply(ticket)"
                        >
                            <div class="min-w-0 flex-1">
                                <textarea
                                    v-model="replies[ticket.id]"
                                    rows="2"
                                    class="max-h-40 min-h-14 w-full resize-y rounded-xl border border-input bg-background p-3 text-sm"
                                    placeholder="Escreva sua resposta"
                                    @keydown="
                                        handleReplyKeydown($event, ticket)
                                    "
                                ></textarea>
                                <p
                                    class="mt-1 text-[11px] text-muted-foreground"
                                >
                                    Enter envia · Shift + Enter pula uma linha
                                </p>
                            </div>
                            <button
                                class="mb-5 rounded-xl bg-primary p-3 text-primary-foreground disabled:cursor-not-allowed disabled:opacity-50"
                                :disabled="!replies[ticket.id]?.trim()"
                            >
                                <Send class="h-4 w-4" />
                            </button>
                        </form>
                    </div>
                </article>
                <div
                    v-if="!tickets.length"
                    class="rounded-2xl border border-dashed border-border p-10 text-center text-muted-foreground"
                >
                    Você ainda não abriu nenhum chamado.
                </div>
            </section>
        </div>
    </AppLayout>
</template>
