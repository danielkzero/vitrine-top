<script setup lang="ts">
import { Head } from '@inertiajs/vue3'

import AppearanceTabs from '@/components/AppearanceTabs.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import { type BreadcrumbItem } from '@/types'

import AppLayout from '@/layouts/AppLayout.vue'
import SettingsLayout from '@/layouts/settings/Layout.vue'
import { edit } from '@/routes/appearance'

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Preferências de exibição',
        href: edit().url,
    },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Preferências de exibição" />

        <SettingsLayout>
            <div class="space-y-10">

                <HeadingSmall 
                    title="Preferências de exibição"
                    description="Atualize o tema, comportamento visual e modo de exibição do aplicativo."
                />

                <!-- CARD PADRÃO DARK/LIGHT -->
                <div
                    class="p-8 rounded-2xl border shadow-sm 
                           bg-slate-50/10 dark:bg-white/5 
                           transition-colors duration-300"
                >
                    <AppearanceTabs />
                </div>

            </div>
        </SettingsLayout>
    </AppLayout>
</template>
