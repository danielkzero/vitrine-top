<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { routes } from '@/config/routes'
import { formatDate } from '@/lib/utils'

const props = defineProps<{
  reviews: Array<any>
  stats: {
    total: number
    approved: number
    pending: number
    rejected: number
    average_rating: number
  }
}>()

const breadcrumbs = [
  { title: 'Painel', href: routes.painel.index },
  { title: 'Avaliacoes', href: routes.painel.reviews.index },
]

function updateStatus(reviewId: number, status: 'pending' | 'approved' | 'rejected') {
  router.patch(`/painel/reviews/${reviewId}`, { status }, { preserveScroll: true })
}

function removeReview(reviewId: number) {
  if (!window.confirm('Deseja realmente remover esta avaliação?')) return

  router.delete(`/painel/reviews/${reviewId}`, { preserveScroll: true })
}

function statusClass(status: string) {
  if (status === 'approved') return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
  if (status === 'rejected') return 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300'
  return 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300'
}
</script>

<template>
  <Head title="Avaliacoes" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="container mx-auto px-4 py-6 space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-foreground">Avaliacoes</h1>
        <p class="text-sm text-muted-foreground">Gerencie reputação e aprovação dos feedbacks da loja.</p>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        <article class="rounded-xl border border-border bg-card p-4 text-card-foreground">
          <p class="text-xs text-muted-foreground">Total</p>
          <p class="text-xl font-bold">{{ stats.total }}</p>
        </article>
        <article class="rounded-xl border border-border bg-card p-4 text-card-foreground">
          <p class="text-xs text-muted-foreground">Aprovadas</p>
          <p class="text-xl font-bold text-emerald-700 dark:text-emerald-300">{{ stats.approved }}</p>
        </article>
        <article class="rounded-xl border border-border bg-card p-4 text-card-foreground">
          <p class="text-xs text-muted-foreground">Pendentes</p>
          <p class="text-xl font-bold text-amber-700 dark:text-amber-300">{{ stats.pending }}</p>
        </article>
        <article class="rounded-xl border border-border bg-card p-4 text-card-foreground">
          <p class="text-xs text-muted-foreground">Rejeitadas</p>
          <p class="text-xl font-bold text-rose-700 dark:text-rose-300">{{ stats.rejected }}</p>
        </article>
        <article class="rounded-xl border border-border bg-card p-4 text-card-foreground">
          <p class="text-xs text-muted-foreground">Nota media</p>
          <p class="text-xl font-bold text-indigo-700 dark:text-indigo-300">{{ stats.average_rating }}</p>
        </article>
      </div>

      <section class="overflow-x-auto rounded-2xl border border-border bg-card text-card-foreground shadow-sm">
        <table class="w-full text-sm">
          <thead class="bg-muted text-muted-foreground">
            <tr>
              <th class="px-4 py-3 text-left">Cliente</th>
              <th class="px-4 py-3 text-left">Produto</th>
              <th class="px-4 py-3 text-left">Nota</th>
              <th class="px-4 py-3 text-left">Comentario</th>
              <th class="px-4 py-3 text-left">Status</th>
              <th class="px-4 py-3 text-left">Data</th>
              <th class="px-4 py-3 text-left">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="!reviews.length" class="border-t">
              <td colspan="7" class="px-4 py-4 text-muted-foreground">Nenhuma avaliação cadastrada.</td>
            </tr>

            <tr v-for="review in reviews" :key="review.id" class="border-t align-top">
              <td class="px-4 py-3">
                <p class="font-medium">{{ review.customer_name }}</p>
                <p class="text-xs text-muted-foreground">{{ review.whatsapp || '-' }}</p>
              </td>
              <td class="px-4 py-3">{{ review.product?.name || '-' }}</td>
              <td class="px-4 py-3">{{ review.rating }}/5</td>
              <td class="max-w-xs px-4 py-3 text-muted-foreground">{{ review.comment || '-' }}</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs" :class="statusClass(review.status)">{{ review.status }}</span>
              </td>
              <td class="px-4 py-3 text-muted-foreground">{{ formatDate(review.created_at) }}</td>
              <td class="px-4 py-3 space-y-2">
                <select class="w-full rounded border border-input bg-background px-2 py-1 text-xs text-foreground" :value="review.status" @change="(e: any) => updateStatus(review.id, e.target.value)">
                  <option value="pending">pending</option>
                  <option value="approved">approved</option>
                  <option value="rejected">rejected</option>
                </select>
                <button class="w-full rounded border border-rose-200 text-rose-600 px-2 py-1 text-xs" @click="removeReview(review.id)">Excluir</button>
              </td>
            </tr>
          </tbody>
        </table>
      </section>
    </div>
  </AppLayout>
</template>
