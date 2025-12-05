<template>
  <div v-if="page.type === 'reviews'" class="space-y-4">
    <p class="text-slate-600 dark:text-slate-300 text-sm leading-relaxed">
      As avaliações são carregadas automaticamente a partir dos comentários deixados por clientes.
      Aqui você pode <b>visualizar</b>, <b>aprovar</b> ou <b>rejeitar</b> cada avaliação.
    </p>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
      <div
        v-for="item in avaliacoes"
        :key="item.id"
        class="rounded-xl border shadow-sm hover:shadow-lg transition-all p-5 flex flex-col gap-4"
        :class="statusClass(item.status)"
      >
        <!-- Header: Nome + Data -->
        <div>
          <div class="flex items-center justify-between">
            <span class="font-semibold text-slate-800 dark:text-white">
              {{ item.customer_name }}
            </span>

            <span class="text-xs text-slate-400 dark:text-slate-300">
              {{ new Date(item.created_at).toLocaleDateString('pt-BR') }}
            </span>
          </div>

          <!-- Stars -->
          <div class="flex items-center mt-2">
            <component
              v-for="n in 5"
              :key="n"
              :is="getIcon(n <= item.rating ? 'Star' : 'StarOff')"
              class="w-5 h-5"
              :class="n <= item.rating ? 'text-yellow-400' : 'text-slate-300 dark:text-slate-500'"
            />
          </div>
        </div>

        <!-- Comment -->
        <p class="text-sm text-slate-700 dark:text-slate-200 leading-relaxed">
          {{ item.comment || 'Sem comentário.' }}
        </p>

        <!-- Status -->
        <div class="pt-3 mt-auto border-t border-slate-200 dark:border-slate-700">
          <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Status</label>

          <select
            v-model="item.status"
            @change="updateStatus(item)"
            class="mt-1 w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900
                   text-sm px-3 py-1.5 focus:ring-2 focus:ring-emerald-500 transition"
          >
          
            <option value="pending">Pendente</option>
            <option value="approved">Aprovado</option>
            <option value="rejected">Rejeitado</option>
          </select>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { getIcon } from '@/lib/iconMap'

defineProps<{
    avaliacoes: Array<any>
    updateStatus: (item: any) => void
    page: Record<string, any>
}>()
// Usa Tailwind para gerar classes condicionalmente
const statusClass = (status: string) => {
  switch (status) {
    case 'pending':
      return 'bg-yellow-50 dark:bg-yellow-900/30 border-yellow-200 dark:border-yellow-800'
    case 'approved':
      return 'bg-emerald-50 dark:bg-emerald-900/30 border-emerald-200 dark:border-emerald-800'
    case 'rejected':
      return 'bg-red-50 dark:bg-red-900/30 border-red-200 dark:border-red-800'
    default:
      return 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700'
  }
}
</script>

<style scoped>
/* Melhora o select no dark mode */
.dark select option {
  background-color: #0f172a !important; /* slate-900 */
}
</style>
