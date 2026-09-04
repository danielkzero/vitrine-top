<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Mail, MessageCircle, Plus, Send } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    client: any;
    notes: any[];
    tickets: any[];
    payments: any[];
}>();
const tab = ref<'history' | 'tickets' | 'payments'>('history');
const profileForm = useForm({
    health: props.client.crm.health,
    score: props.client.crm.score,
    tags: props.client.crm.tags ?? [],
    summary: props.client.crm.summary ?? '',
    next_follow_up_at: props.client.crm.next_follow_up_at?.slice(0, 16) ?? '',
});
const tagsText = ref((props.client.crm.tags ?? []).join(', '));
const noteForm = useForm({ type: 'note', content: '' });
const ticketForm = useForm({
    subject: '',
    message: '',
    category: 'question',
    priority: 'normal',
});
const replies = ref<Record<number, string>>({});

function saveProfile() {
    profileForm.tags = tagsText.value
        .split(',')
        .map((tag: string) => tag.trim())
        .filter(Boolean);
    profileForm.put(`/admin/clientes/${props.client.id}/crm`, {
        preserveScroll: true,
    });
}
function addNote() {
    noteForm.post(`/admin/clientes/${props.client.id}/notas`, {
        preserveScroll: true,
        onSuccess: () => noteForm.reset('content'),
    });
}
function createTicket() {
    ticketForm.post(`/admin/clientes/${props.client.id}/tickets`, {
        preserveScroll: true,
        onSuccess: () => ticketForm.reset(),
    });
}
function updateTicket(ticket: any) {
    useForm({
        status: ticket.status,
        priority: ticket.priority,
        message: replies.value[ticket.id] ?? '',
        is_internal: false,
    }).put(`/admin/tickets/${ticket.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            replies.value[ticket.id] = '';
        },
    });
}
const money = (value: number) =>
    new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value);
const date = (value: string | null) =>
    value
        ? new Intl.DateTimeFormat('pt-BR', {
              dateStyle: 'short',
              timeStyle: 'short',
          }).format(new Date(value))
        : '—';
const labels: Record<string, string> = {
    good: 'Bom cliente',
    attention: 'Atenção',
    risk: 'Risco',
    active: 'Ativo',
    trial: 'Trial',
    expired: 'Expirado',
    cancelled: 'Cancelado',
    past_due: 'Em atraso',
    open: 'Aberto',
    in_progress: 'Em atendimento',
    waiting_customer: 'Aguardando cliente',
    resolved: 'Resolvido',
    closed: 'Encerrado',
    question: 'Dúvida',
    suggestion: 'Sugestão',
    technical: 'Suporte técnico',
    billing: 'Cobrança',
    other: 'Outro',
    note: 'Nota',
    call: 'Ligação',
    whatsapp: 'WhatsApp',
    email: 'E-mail',
    meeting: 'Reunião',
    payment: 'Pagamento',
    support: 'Suporte',
};
</script>

<template>
    <Head :title="`CRM - ${client.business_name || client.name}`" />
    <AppLayout
        :breadcrumbs="[
            { title: 'Administração', href: '/admin' },
            {
                title: client.business_name || client.name,
                href: `/admin/clientes/${client.id}`,
            },
        ]"
    >
        <div class="space-y-6 p-4 md:p-6">
            <header
                class="flex flex-col justify-between gap-4 lg:flex-row lg:items-start"
            >
                <div>
                    <Link href="/admin" class="text-sm text-primary"
                        >← Voltar aos clientes</Link
                    >
                    <h1 class="mt-2 text-2xl font-bold text-foreground">
                        {{ client.business_name || client.name }}
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Ficha completa do cliente e histórico de relacionamento
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a
                        v-if="client.whatsapp"
                        :href="`https://wa.me/${client.whatsapp.replace(/\D/g, '')}`"
                        target="_blank"
                        class="flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white"
                        ><MessageCircle class="h-4 w-4" /> WhatsApp</a
                    ><a
                        :href="`mailto:${client.email}`"
                        class="flex items-center gap-2 rounded-xl border border-border bg-card px-4 py-2.5 text-sm text-foreground"
                        ><Mail class="h-4 w-4" /> E-mail</a
                    >
                </div>
            </header>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-border bg-card p-5">
                    <p class="text-xs text-muted-foreground">Assinatura</p>
                    <p class="mt-2 font-bold text-foreground">
                        {{ client.subscription?.plan_name || 'Sem plano' }}
                    </p>
                    <p class="text-sm text-muted-foreground">
                        {{
                            labels[client.subscription?.status] ||
                            'Sem assinatura'
                        }}
                    </p>
                    <p
                        v-if="
                            ['expired', 'cancelled'].includes(
                                client.subscription?.status,
                            )
                        "
                        class="mt-2 text-xs font-medium text-red-600"
                    >
                        Há {{ client.subscription.days_in_status }} dias neste
                        status
                    </p>
                </div>
                <div class="rounded-2xl border border-border bg-card p-5">
                    <p class="text-xs text-muted-foreground">Valor gerado</p>
                    <p class="mt-2 text-xl font-bold text-emerald-600">
                        {{ money(client.payment_profile.paid_total) }}
                    </p>
                    <p class="text-xs text-muted-foreground">
                        {{ client.payment_profile.paid_count }} pagamentos
                        concluídos
                    </p>
                </div>
                <div class="rounded-2xl border border-border bg-card p-5">
                    <p class="text-xs text-muted-foreground">
                        Problemas financeiros
                    </p>
                    <p
                        class="mt-2 text-xl font-bold"
                        :class="
                            client.payment_profile.problem_count
                                ? 'text-red-600'
                                : 'text-foreground'
                        "
                    >
                        {{ client.payment_profile.problem_count }}
                    </p>
                    <p class="text-xs text-muted-foreground">
                        Falhas ou estornos
                    </p>
                </div>
                <div class="rounded-2xl border border-border bg-card p-5">
                    <p class="text-xs text-muted-foreground">
                        Saúde do relacionamento
                    </p>
                    <p class="mt-2 text-xl font-bold text-foreground">
                        {{ labels[client.crm.health] }}
                    </p>
                    <p class="text-xs text-muted-foreground">
                        Pontuação {{ client.crm.score }}/100
                    </p>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-[1fr_360px]">
                <div class="space-y-6">
                    <div class="rounded-2xl border border-border bg-card p-5">
                        <h2 class="font-semibold text-foreground">
                            Informações do cliente
                        </h2>
                        <div
                            class="mt-4 grid gap-4 text-sm sm:grid-cols-2 lg:grid-cols-3"
                        >
                            <p>
                                <span
                                    class="block text-xs text-muted-foreground"
                                    >Responsável</span
                                >{{ client.name }} {{ client.surname }}
                            </p>
                            <p>
                                <span
                                    class="block text-xs text-muted-foreground"
                                    >E-mail</span
                                >{{ client.email }}
                            </p>
                            <p>
                                <span
                                    class="block text-xs text-muted-foreground"
                                    >Telefone</span
                                >{{
                                    client.phone ||
                                    client.whatsapp ||
                                    'Não informado'
                                }}
                            </p>
                            <p>
                                <span
                                    class="block text-xs text-muted-foreground"
                                    >Endereço</span
                                >{{
                                    [client.address, client.city, client.state]
                                        .filter(Boolean)
                                        .join(', ') || 'Não informado'
                                }}
                            </p>
                            <p>
                                <span
                                    class="block text-xs text-muted-foreground"
                                    >Loja pública</span
                                ><a
                                    v-if="client.slug"
                                    :href="`/${client.slug}`"
                                    target="_blank"
                                    class="text-primary"
                                    >/{{ client.slug }}</a
                                >
                            </p>
                            <p>
                                <span
                                    class="block text-xs text-muted-foreground"
                                    >Cliente desde</span
                                >{{ date(client.created_at) }}
                            </p>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-border bg-card p-5">
                        <h2 class="font-semibold text-foreground">
                            Uso da plataforma
                        </h2>
                        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                            <div
                                v-for="(value, key) in client.usage"
                                :key="key"
                                class="rounded-xl bg-muted/50 p-3"
                            >
                                <p class="text-xl font-bold text-foreground">
                                    {{ value }}
                                </p>
                                <p
                                    class="text-xs text-muted-foreground capitalize"
                                >
                                    {{
                                        (
                                            {
                                                product_images: 'fotos',
                                                products: 'produtos',
                                                pages: 'páginas',
                                                banners: 'banners',
                                                categories: 'categorias',
                                                customers: 'compradores',
                                                orders: 'pedidos',
                                            } as any
                                        )[key]
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-border bg-card">
                        <div
                            class="flex gap-1 overflow-x-auto border-b border-border p-2"
                        >
                            <button
                                v-for="item in [
                                    { k: 'history', l: 'Histórico CRM' },
                                    {
                                        k: 'tickets',
                                        l: `Tickets (${tickets.length})`,
                                    },
                                    {
                                        k: 'payments',
                                        l: `Pagamentos (${payments.length})`,
                                    },
                                ]"
                                :key="item.k"
                                class="rounded-lg px-4 py-2 text-sm whitespace-nowrap"
                                :class="
                                    tab === item.k
                                        ? 'bg-primary text-primary-foreground'
                                        : 'text-muted-foreground'
                                "
                                @click="tab = item.k as any"
                            >
                                {{ item.l }}
                            </button>
                        </div>
                        <div class="p-5">
                            <div v-if="tab === 'history'" class="space-y-4">
                                <form
                                    class="flex flex-col gap-2 sm:flex-row"
                                    @submit.prevent="addNote"
                                >
                                    <select
                                        v-model="noteForm.type"
                                        class="rounded-lg border border-input bg-background p-2.5 text-sm"
                                    >
                                        <option
                                            v-for="type in [
                                                'note',
                                                'call',
                                                'whatsapp',
                                                'email',
                                                'meeting',
                                                'payment',
                                                'support',
                                            ]"
                                            :key="type"
                                            :value="type"
                                        >
                                            {{ labels[type] }}
                                        </option></select
                                    ><input
                                        v-model="noteForm.content"
                                        class="min-w-0 flex-1 rounded-lg border border-input bg-background p-2.5 text-sm"
                                        placeholder="Registre uma conversa, decisão ou observação..."
                                    /><button
                                        class="rounded-lg bg-primary px-4 text-primary-foreground"
                                    >
                                        <Plus class="h-4 w-4" />
                                    </button>
                                </form>
                                <div
                                    v-for="note in notes"
                                    :key="note.id"
                                    class="border-l-2 border-primary/40 pl-4"
                                >
                                    <div class="flex justify-between gap-3">
                                        <b class="text-sm text-foreground">{{
                                            labels[note.type]
                                        }}</b
                                        ><span
                                            class="text-xs text-muted-foreground"
                                            >{{ date(note.occurred_at) }}</span
                                        >
                                    </div>
                                    <p
                                        class="mt-1 text-sm whitespace-pre-wrap text-foreground"
                                    >
                                        {{ note.content }}
                                    </p>
                                    <p
                                        class="mt-1 text-xs text-muted-foreground"
                                    >
                                        por {{ note.author?.name || 'Sistema' }}
                                    </p>
                                </div>
                                <p
                                    v-if="!notes.length"
                                    class="py-8 text-center text-sm text-muted-foreground"
                                >
                                    Nenhuma interação registrada.
                                </p>
                            </div>
                            <div
                                v-else-if="tab === 'tickets'"
                                class="space-y-4"
                            >
                                <form
                                    class="grid gap-2 rounded-xl bg-muted/50 p-4 sm:grid-cols-2"
                                    @submit.prevent="createTicket"
                                >
                                    <input
                                        v-model="ticketForm.subject"
                                        class="rounded-lg border border-input bg-background p-2.5 text-sm sm:col-span-2"
                                        placeholder="Assunto do novo ticket"
                                    /><select
                                        v-model="ticketForm.category"
                                        class="rounded-lg border border-input bg-background p-2.5 text-sm"
                                    >
                                        <option
                                            v-for="type in [
                                                'question',
                                                'suggestion',
                                                'technical',
                                                'billing',
                                                'other',
                                            ]"
                                            :key="type"
                                            :value="type"
                                        >
                                            {{ labels[type] }}
                                        </option></select
                                    ><select
                                        v-model="ticketForm.priority"
                                        class="rounded-lg border border-input bg-background p-2.5 text-sm"
                                    >
                                        <option value="low">Baixa</option>
                                        <option value="normal">Normal</option>
                                        <option value="high">Alta</option>
                                        <option value="urgent">
                                            Urgente
                                        </option></select
                                    ><textarea
                                        v-model="ticketForm.message"
                                        class="rounded-lg border border-input bg-background p-2.5 text-sm sm:col-span-2"
                                        placeholder="Descrição do atendimento"
                                    ></textarea
                                    ><button
                                        class="rounded-lg bg-primary p-2.5 text-sm font-semibold text-primary-foreground sm:col-span-2"
                                    >
                                        Criar ticket
                                    </button>
                                </form>
                                <article
                                    v-for="ticket in tickets"
                                    :key="ticket.id"
                                    class="rounded-xl border border-border p-4"
                                >
                                    <div
                                        class="flex flex-wrap justify-between gap-2"
                                    >
                                        <div>
                                            <b class="text-foreground"
                                                >{{ ticket.number }} ·
                                                {{ ticket.subject }}</b
                                            >
                                            <p
                                                class="text-xs text-muted-foreground"
                                            >
                                                {{ labels[ticket.category] }}
                                            </p>
                                        </div>
                                        <span
                                            class="text-xs font-semibold text-primary"
                                            >{{ labels[ticket.status] }}</span
                                        >
                                    </div>
                                    <div
                                        class="mt-3 max-h-52 space-y-2 overflow-y-auto"
                                    >
                                        <div
                                            v-for="message in ticket.messages"
                                            :key="message.id"
                                            class="rounded-lg p-3 text-sm"
                                            :class="
                                                message.is_internal
                                                    ? 'bg-amber-100 text-amber-900 dark:bg-amber-950 dark:text-amber-100'
                                                    : 'bg-muted text-foreground'
                                            "
                                        >
                                            <b class="text-xs">{{
                                                message.is_internal
                                                    ? 'Nota interna'
                                                    : message.author?.name
                                            }}</b>
                                            <p>{{ message.message }}</p>
                                        </div>
                                    </div>
                                    <div
                                        class="mt-3 grid gap-2 sm:grid-cols-[140px_140px_1fr_auto]"
                                    >
                                        <select
                                            v-model="ticket.status"
                                            class="rounded-lg border border-input bg-background p-2 text-xs"
                                        >
                                            <option
                                                v-for="status in [
                                                    'open',
                                                    'in_progress',
                                                    'waiting_customer',
                                                    'resolved',
                                                    'closed',
                                                ]"
                                                :key="status"
                                                :value="status"
                                            >
                                                {{ labels[status] }}
                                            </option></select
                                        ><select
                                            v-model="ticket.priority"
                                            class="rounded-lg border border-input bg-background p-2 text-xs"
                                        >
                                            <option value="low">Baixa</option>
                                            <option value="normal">
                                                Normal
                                            </option>
                                            <option value="high">Alta</option>
                                            <option value="urgent">
                                                Urgente
                                            </option></select
                                        ><input
                                            v-model="replies[ticket.id]"
                                            class="rounded-lg border border-input bg-background p-2 text-sm"
                                            placeholder="Responder (opcional)"
                                        /><button
                                            class="rounded-lg bg-primary p-2 text-primary-foreground"
                                            @click="updateTicket(ticket)"
                                        >
                                            <Send class="h-4 w-4" />
                                        </button>
                                    </div>
                                </article>
                            </div>
                            <div v-else class="overflow-x-auto">
                                <table class="w-full min-w-[620px] text-sm">
                                    <thead>
                                        <tr
                                            class="text-left text-xs text-muted-foreground"
                                        >
                                            <th class="pb-3">ID</th>
                                            <th>Status</th>
                                            <th>Método</th>
                                            <th>Data</th>
                                            <th class="text-right">Valor</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="payment in payments"
                                            :key="payment.id"
                                            class="border-t border-border"
                                        >
                                            <td class="py-3">
                                                #{{ payment.id }}
                                            </td>
                                            <td>{{ payment.status }}</td>
                                            <td>{{ payment.method }}</td>
                                            <td>
                                                {{
                                                    date(
                                                        payment.paid_at ||
                                                            payment.created_at,
                                                    )
                                                }}
                                            </td>
                                            <td
                                                class="text-right font-semibold"
                                            >
                                                {{
                                                    money(
                                                        Number(payment.amount),
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <aside class="space-y-5">
                    <div class="rounded-2xl border border-border bg-card p-5">
                        <h2 class="font-semibold text-foreground">
                            Perfil CRM
                        </h2>
                        <div class="mt-4 space-y-3">
                            <label class="block text-sm"
                                >Saúde<select
                                    v-model="profileForm.health"
                                    class="mt-1 w-full rounded-lg border border-input bg-background p-2.5"
                                >
                                    <option value="good">Bom cliente</option>
                                    <option value="attention">Atenção</option>
                                    <option value="risk">Risco</option>
                                </select></label
                            ><label class="block text-sm"
                                >Pontuação (0–100)<input
                                    v-model.number="profileForm.score"
                                    type="number"
                                    min="0"
                                    max="100"
                                    class="mt-1 w-full rounded-lg border border-input bg-background p-2.5" /></label
                            ><label class="block text-sm"
                                >Tags<input
                                    v-model="tagsText"
                                    class="mt-1 w-full rounded-lg border border-input bg-background p-2.5"
                                    placeholder="vip, recorrente, técnico" /></label
                            ><label class="block text-sm"
                                >Próximo acompanhamento<input
                                    v-model="profileForm.next_follow_up_at"
                                    type="datetime-local"
                                    class="mt-1 w-full rounded-lg border border-input bg-background p-2.5" /></label
                            ><label class="block text-sm"
                                >Resumo do relacionamento<textarea
                                    v-model="profileForm.summary"
                                    rows="6"
                                    class="mt-1 w-full rounded-lg border border-input bg-background p-2.5"
                                    placeholder="Preferências, contexto e pontos importantes..."
                                ></textarea></label
                            ><button
                                class="w-full rounded-lg bg-primary py-2.5 text-sm font-semibold text-primary-foreground"
                                @click="saveProfile"
                            >
                                Salvar perfil
                            </button>
                        </div>
                    </div>
                </aside>
            </section>
        </div>
    </AppLayout>
</template>
