<script setup lang="ts">
import type { Component } from 'vue'

defineProps<{
    avaliacoes: Array<any>
    Star: Component
    StarOff: Component
    updateStatus: (item: any) => void
    page: Record<string, any>
}>()
</script>

<template>
    <div v-if="page.type === 'reviews' && avaliacoes" class="space-y-4">
        <p class="text-gray-600 text-sm mb-2">
            As <b>avaliações</b> são geradas automaticamente a partir dos comentários
            deixados por clientes em suas páginas.
            Elas são carregadas diretamente do sistema por meio de um controlador.
            Aqui você pode visualizar, aprovar ou rejeitar cada avaliação conforme desejar.
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <div v-for="item in avaliacoes" :key="item.id" 
            class="border border-gray-200 dark:border-white/20 rounded-xl 
                p-4 shadow-sm hover:shadow-md transition flex flex-col 
                justify-between" :class="{
                    'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/50': item.status === 'pending',
                    'bg-green-100 text-green-700 dark:bg-emerald-900/50': item.status === 'approved',
                    'bg-red-100 text-red-700 dark:bg-red-900/50': item.status === 'rejected',
                }">
                <!-- Conteúdo principal -->
                <div>
                    <!-- Cabeçalho: Nome + Data -->
                    <div class="flex items-center justify-between mb-2">
                        <div class="font-semibold text-gray-800 dark:text-white">{{ item.customer_name }}
                        </div>
                        <div class="text-xs text-gray-400 dark:text-gray-200">
                            {{ new Date(item.created_at).toLocaleDateString('pt-BR') }}
                        </div>
                    </div>

                    <!-- Estrelas -->
                    <div class="flex items-center mb-2">
                        <component v-for="n in 5" :key="n" :is="n <= item.rating ? Star : StarOff" class="w-5 h-5"
                            :class="n <= item.rating ? 'text-yellow-400 dark:text-yellow-500' : 'text-gray-300 dark:text-white'" />
                    </div>

                    <!-- Comentário -->
                    <p class="text-gray-700 dark:text-white text-sm leading-relaxed mb-4">
                        {{ item.comment || 'Sem comentário.' }}
                    </p>
                </div>

                <!-- Rodapé fixo: Status -->
                <div class="flex items-center gap-2 mt-auto pt-3 border-t border-gray-100">
                    <label class="text-sm text-gray-600 dark:text-white font-medium">Status:</label>
                    <select v-model="item.status" @change="updateStatus(item)"
                        class="border border-gray-300 rounded-xl px-3 py-1 text-sm bg-white">
                        <option value="pending">Pendente</option>
                        <option value="approved">Aprovado</option>
                        <option value="rejected">Rejeitado</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</template>
