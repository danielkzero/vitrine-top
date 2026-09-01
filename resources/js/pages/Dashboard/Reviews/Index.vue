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
  if (!window.confirm('Deseja realmente remover esta avaliacao?')) return

  router.delete(`/painel/reviews/${reviewId}`, { preserveScroll: true })
}

function statusClass(status: string) {
  if (status === 'approved') return 'bg-emerald-100 text-emerald-700'
  if (status === 'rejected') return 'bg-rose-100 text-rose-700'
  return 'bg-amber-100 text-amber-700'
}
</script>

<template>
  <Head title="Avaliacoes" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="container mx-auto px-4 py-6 space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Avaliacoes</h1>
        <p class="text-sm text-slate-500">Gerencie reputacao e aprovacao dos feedbacks da loja.</p>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        <article class="rounded-xl border bg-white p-4">
          <p class="text-xs text-slate-500">Total</p>
          <p class="text-xl font-bold">{{ stats.total }}</p>
        </article>
        <article class="rounded-xl border bg-white p-4">
          <p class="text-xs text-slate-500">Aprovadas</p>
          <p class="text-xl font-bold text-emerald-700">{{ stats.approved }}</p>
        </article>
        <article class="rounded-xl border bg-white p-4">
          <p class="text-xs text-slate-500">Pendentes</p>
          <p class="text-xl font-bold text-amber-700">{{ stats.pending }}</p>
        </article>
        <article class="rounded-xl border bg-white p-4">
          <p class="text-xs text-slate-500">Rejeitadas</p>
          <p class="text-xl font-bold text-rose-700">{{ stats.rejected }}</p>
        </article>
        <article class="rounded-xl border bg-white p-4">
          <p class="text-xs text-slate-500">Nota media</p>
          <p class="text-xl font-bold text-indigo-700">{{ stats.average_rating }}</p>
        </article>
      </div>

      <section class="rounded-2xl border bg-white shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-slate-50 text-slate-500">
            <tr>
              <th class="px-4 py-3 text-left">Cliente</th>
              <th class="px-4 py-3 text-left">Produto</th>
              <th class="px-4 py-3 text-left">Nota</th>
              <th class="px-4 py-3 text-left">Comentario</th>
              <th class="px-4 py-3 text-left">Status</th>
              <th class="px-4 py-3 text-left">Data</th>
              <th class="px-4 py-3 text-left">Acoes</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="!reviews.length" class="border-t">
              <td colspan="7" class="px-4 py-4 text-slate-500">Nenhuma avaliacao cadastrada.</td>
            </tr>

            <tr v-for="review in reviews" :key="review.id" class="border-t align-top">
              <td class="px-4 py-3">
                <p class="font-medium">{{ review.customer_name }}</p>
                <p class="text-xs text-slate-500">{{ review.whatsapp || '-' }}</p>
              </td>
              <td class="px-4 py-3">{{ review.product?.name || '-' }}</td>
              <td class="px-4 py-3">{{ review.rating }}/5</td>
              <td class="px-4 py-3 max-w-xs text-slate-600">{{ review.comment || '-' }}</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs" :class="statusClass(review.status)">{{ review.status }}</span>
              </td>
              <td class="px-4 py-3 text-slate-500">{{ formatDate(review.created_at) }}</td>
              <td class="px-4 py-3 space-y-2">
                <select class="w-full rounded border px-2 py-1 text-xs" :value="review.status" @change="(e: any) => updateStatus(review.id, e.target.value)">
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
