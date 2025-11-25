<!-- resources/js/components/dashboard/PageCard.vue -->
<script setup lang="ts">
import BaseButton from '@/components/ui/button/BaseButton.vue'
import { getIcon } from '@/lib/iconMap'
import { computed } from 'vue'

interface Props {
    title: string
    icon: string
    type?: string
    description?: string
    active?: boolean
    editHref: string
}

const props = withDefaults(defineProps<Props>(), {
    type: 'Desconhecido',
    description: 'Sem descrição SEO.',
    active: false
})

const statusClasses = computed(() => {
    return props.active
        ? 'bg-emerald-100 text-emerald-700'
        : 'bg-gray-200 text-gray-600'
})
</script>

<template>
    <div
        class="bg-white border border-slate-100 rounded-xl p-5 shadow-sm hover:shadow-md transition group flex flex-col justify-between">
        <!-- Header -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <!-- Ícone + título -->
                <div class="flex items-center gap-2">
                    <component :is="getIcon(icon)" class="w-5 h-5 text-sky-600" />
                    <h2 class="font-semibold text-slate-800 truncate">{{ title }}</h2>
                </div>

                <!-- Badge -->
                <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full"
                    :class="statusClasses">
                    <component :is="getIcon(active ? 'Eye' : 'EyeOff')" class="w-4 h-4 mr-1" />
                    {{ active ? 'Ativa' : 'Inativa' }}
                </span>
            </div>

            <!-- Descrição -->
            <p class="text-slate-600 text-sm line-clamp-3">
                {{ description }}
            </p>
        </div>

        <!-- Footer -->
        <div class="flex justify-between items-center mt-5">
            <!-- Tipo -->
            <span class="text-xs px-3 py-1 rounded-full bg-amber-100 text-amber-700">
                Tipo: {{ type }}
            </span>

            <!-- Botão Editar -->
            <BaseButton as="Link" :href="[editHref]" size="sm" variant="primary" leading-icon="FileEdit">
                Editar
            </BaseButton>
        </div>
    </div>
</template>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
