<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance'
import { Monitor, Moon, Sun } from 'lucide-vue-next'

const { appearance, updateAppearance } = useAppearance()

const tabs = [
    { value: 'light', Icon: Sun, label: 'Claro' },
    { value: 'dark', Icon: Moon, label: 'Escuro' },
    { value: 'system', Icon: Monitor, label: 'Sistema' },
] as const
</script>

<template>
    <div
        class="inline-flex gap-1 rounded-full border border-slate-200/60 bg-slate-50/80
               px-2 py-1 shadow-sm backdrop-blur-sm
               dark:border-white/10 dark:bg-white/5 dark:shadow-md"
    >
        <button
            v-for="{ value, Icon, label } in tabs"
            :key="value"
            @click="updateAppearance(value)"
            :class="[
                'flex items-center gap-1.5 rounded-full px-4 py-2 text-sm font-medium transition-all',
                
                // Ativo
                appearance === value
                    ? 'bg-white text-emerald-600 shadow dark:bg-emerald-600/20 dark:text-emerald-300'
                    : 'text-slate-600 hover:bg-white/70 dark:text-neutral-300 dark:hover:bg-white/10',
            ]"
        >
            <component
                :is="Icon"
                :class="[
                    'h-4 w-4 transition-colors',
                    appearance === value
                        ? 'text-emerald-600 dark:text-emerald-300'
                        : 'text-slate-500 dark:text-neutral-400'
                ]"
            />

            {{ label }}
        </button>
    </div>
</template>
