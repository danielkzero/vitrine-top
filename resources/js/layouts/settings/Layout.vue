<script setup lang="ts">
import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'
import { Separator } from '@/components/ui/separator'
import { toUrl, urlIsActive } from '@/lib/utils'

import { edit as editAppearance } from '@/routes/appearance'
import { edit as editProfile } from '@/routes/profile'
import { edit as editStore } from '@/routes/store'
import { show } from '@/routes/two-factor'
import { edit as editPassword } from '@/routes/user-password'

import { type NavItem } from '@/types'
import { Link } from '@inertiajs/vue3'

import {
    User,
    Store,
    KeyRound,
    Shield,
    Palette
} from 'lucide-vue-next'

const sidebarNavItems: NavItem[] = [
    {
        title: 'Perfil',
        href: editProfile(),
        icon: User,
    },
    {
        title: 'Loja',
        href: editStore(),
        icon: Store,
    },
    {
        title: 'Senha',
        href: editPassword(),
        icon: KeyRound,
    },
    {
        title: 'Autenticação de 2 fatores',
        href: show(),
        icon: Shield,
    },
    {
        title: 'Tema',
        href: editAppearance(),
        icon: Palette,
    },
]

const currentPath =
    typeof window !== undefined ? window.location.pathname : ''
</script>

<template>
    <div class="px-4 py-6">
        <Heading
            title="Configurações"
            description="Gerencie seu perfil e as configurações da sua conta."
        />

        <div class="flex flex-col lg:flex-row lg:space-x-12">
            <!-- SIDEBAR -->
            <aside class="w-full max-w-xl lg:w-52">
                <nav class="flex flex-col space-y-1">
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="toUrl(item.href)"
                        variant="ghost"
                        as-child
                        class="w-full justify-start h-10 gap-3 rounded-lg
                               text-left transition-all
                               hover:bg-slate-200/40 dark:hover:bg-white/10"
                        :class="{
                            'bg-slate-200/60 dark:bg-white/10 text-emerald-600 dark:text-emerald-400 font-semibold':
                                urlIsActive(item.href, currentPath),
                        }"
                    >
                        <Link :href="item.href">
                            <component
                                :is="item.icon"
                                class="h-4 w-4 opacity-80"
                            />
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="my-6 lg:hidden" />

            <!-- CONTENT -->
            <div class="flex-1 md:max-w-2xl">
                <section class="max-w-xl space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
