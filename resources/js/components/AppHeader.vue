<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    NavigationMenu,
    NavigationMenuItem,
    NavigationMenuList,
    navigationMenuTriggerStyle,
} from '@/components/ui/navigation-menu';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { getInitials } from '@/composables/useInitials';
import { toUrl, urlIsActive } from '@/lib/utils';
import { painel } from '@/routes';
import type { BreadcrumbItem, NavItem } from '@/types';
import { InertiaLinkProps, Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Menu, Search } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItem[];
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const auth = computed(() => page.props.auth);

const isCurrentRoute = computed(
    () => (url: NonNullable<InertiaLinkProps['href']>) =>
        urlIsActive(url, page.url),
);

const activeItemStyles = computed(
    () => (url: NonNullable<InertiaLinkProps['href']>) =>
        isCurrentRoute.value(toUrl(url))
            ? 'text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100'
            : '',
);

const mainNavItems: NavItem[] = [
    {
        title: 'Painel',
        href: painel(),
        icon: LayoutGrid,
    },
];

const rightNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <div>
        <!-- NAVBAR MAIN WRAPPER -->
        <div
            class="border-b border-slate-200/60 bg-white/70 backdrop-blur-md 
                   dark:bg-neutral-900/60 dark:border-neutral-800/60 supports-[backdrop-filter]:backdrop-blur-md"
        >
            <div class="mx-auto flex h-16 items-center px-4 md:max-w-7xl">
                
                <!-- MOBILE MENU BUTTON -->
                <div class="lg:hidden">
                    <Sheet>
                        <SheetTrigger as-child>
                            <Button
                                variant="ghost"
                                size="icon"
                                class="mr-2 h-9 w-9 hover:bg-slate-200/40 dark:hover:bg-white/10"
                            >
                                <Menu class="h-5 w-5 opacity-80" />
                            </Button>
                        </SheetTrigger>

                        <!-- MOBILE SIDEBAR -->
                        <SheetContent side="left" class="w-[300px] p-6 bg-white dark:bg-neutral-900">
                            <SheetTitle class="sr-only">Menu de navegação</SheetTitle>

                            <SheetHeader class="flex justify-start text-left">
                                <AppLogoIcon
                                    class="size-6 text-black dark:text-white bg-gradient-to-r from-sky-400 to-emerald-600"
                                />
                            </SheetHeader>

                            <div class="flex h-full flex-1 flex-col justify-between space-y-4 py-6">
                                <!-- MAIN NAV ITEMS -->
                                <nav class="-mx-3 space-y-1">
                                    <Link
                                        v-for="item in mainNavItems"
                                        :key="item.title"
                                        :href="item.href"
                                        :class="[
                                            'flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium transition-all',
                                            'hover:bg-slate-100 dark:hover:bg-neutral-800',
                                            activeItemStyles(item.href)
                                        ]"
                                    >
                                        <component :is="item.icon" class="h-5 w-5" />
                                        {{ item.title }}
                                    </Link>
                                </nav>

                                <!-- RIGHT NAV LINKS -->
                                <div class="flex flex-col space-y-4">
                                    <a
                                        v-for="item in rightNavItems"
                                        :key="item.title"
                                        :href="toUrl(item.href)"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="flex items-center space-x-2 text-sm font-medium hover:text-emerald-600 transition"
                                    >
                                        <component :is="item.icon" class="h-5 w-5" />
                                        <span>{{ item.title }}</span>
                                    </a>
                                </div>
                            </div>
                        </SheetContent>
                    </Sheet>
                </div>

                <!-- LOGO -->
                <Link :href="painel()" class="flex items-center gap-x-2">
                    <AppLogo />
                </Link>

                <!-- DESKTOP NAV -->
                <div class="hidden h-full lg:flex lg:flex-1">
                    <NavigationMenu class="ml-10 flex h-full items-stretch">
                        <NavigationMenuList class="flex h-full items-stretch space-x-2">
                            <NavigationMenuItem
                                v-for="item in mainNavItems"
                                :key="item.title"
                                class="relative flex h-full items-center"
                            >
                                <Link
                                    :href="item.href"
                                    :class="[
                                        navigationMenuTriggerStyle(),
                                        'h-9 px-3 cursor-pointer rounded-md flex items-center gap-2',
                                        'hover:bg-slate-100/70 dark:hover:bg-neutral-800/60',
                                        activeItemStyles(item.href)
                                    ]"
                                >
                                    <component :is="item.icon" class="h-4 w-4" />
                                    {{ item.title }}
                                </Link>

                                <!-- ACTIVE BAR -->
                                <div
                                    v-if="isCurrentRoute(item.href)"
                                    class="absolute bottom-0 left-0 h-0.5 w-full bg-emerald-600 dark:bg-emerald-500"
                                ></div>
                            </NavigationMenuItem>
                        </NavigationMenuList>
                    </NavigationMenu>
                </div>

                <!-- RIGHT ACTIONS -->
                <div class="ml-auto flex items-center space-x-2">

                    <!-- SEARCH -->
                    <Button
                        variant="ghost"
                        size="icon"
                        class="group h-9 w-9 hover:bg-slate-200/40 dark:hover:bg-white/10"
                    >
                        <Search class="size-5 opacity-80 group-hover:opacity-100" />
                    </Button>

                    <!-- RIGHT LINKS WITH TOOLTIP (Desktop only) -->
                    <div class="hidden space-x-1 lg:flex">
                        <TooltipProvider :delay-duration="0">
                            <Tooltip v-for="item in rightNavItems" :key="item.title">
                                <TooltipTrigger>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        as-child
                                        class="group h-9 w-9 hover:bg-slate-200/40 dark:hover:bg-white/10"
                                    >
                                        <a
                                            :href="toUrl(item.href)"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            <component :is="item.icon" class="size-5 opacity-80 group-hover:opacity-100" />
                                        </a>
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{{ item.title }}</p>
                                </TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                    </div>

                    <!-- USER MENU -->
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button
                                variant="ghost"
                                size="icon"
                                class="relative size-10 w-auto rounded-full p-[3px] hover:bg-slate-200/40 dark:hover:bg-white/10"
                            >
                                <Avatar class="size-8 overflow-hidden rounded-full shadow-sm">
                                    <AvatarImage
                                        v-if="auth.user.avatar"
                                        :src="auth.user.avatar"
                                        :alt="auth.user.name"
                                    />
                                    <AvatarFallback
                                        class="rounded-full bg-neutral-200 dark:bg-neutral-700 text-black dark:text-white font-semibold"
                                    >
                                        {{ getInitials(auth.user?.name) }}
                                    </AvatarFallback>
                                </Avatar>
                            </Button>
                        </DropdownMenuTrigger>

                        <DropdownMenuContent align="end" class="w-56">
                            <UserMenuContent :user="auth.user" />
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
        </div>

        <!-- BREADCRUMBS -->
        <div
            v-if="props.breadcrumbs.length > 1"
            class="flex w-full border-b border-slate-200/60 dark:border-neutral-800/60"
        >
            <div class="mx-auto flex h-12 w-full items-center px-4 md:max-w-7xl text-neutral-500">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </div>
        </div>
    </div>
</template>
