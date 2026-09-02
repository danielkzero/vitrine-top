<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue'
import NavMain from '@/components/NavMain.vue'
import NavUser from '@/components/NavUser.vue'
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar'
import { Link, usePage } from '@inertiajs/vue3'
import { ShieldCheck } from 'lucide-vue-next'
import { computed } from 'vue'
import type { AppPageProps } from '@/types'
import AppLogo from './AppLogo.vue'

// Importa os menus configurados
import { mainNavItems, footerNavItems } from '@/config/navigation'

const page = usePage<AppPageProps>()
const navigationItems = computed(() => page.props.auth.user?.is_admin
    ? [...mainNavItems, { title: 'Administração', href: '/admin', icon: ShieldCheck }]
    : mainNavItems,
)
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <!-- Cabeçalho -->
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <!-- substitui route('painel.index') -->
                        <Link href="/painel">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <!-- Conteúdo principal -->
        <SidebarContent>
            <NavMain :items="navigationItems" />
        </SidebarContent>

        <!-- Rodapé -->
        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>

    <slot />
</template>
