<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Store } from 'lucide-vue-next';
</script>

<template>
    <div
        class="flex aspect-square size-10 items-center justify-center rounded-full bg-sidebar-primary text-sidebar-primary-foreground  bg-gradient-to-r to-emerald-600 from-sky-400">
        <!--<AppLogoIcon class="size-5 fill-current text-white dark:text-black" />-->
        <component :is="Store" class="h-6 w-6" />
    </div>
    <div class="text-left text-sm">
        <h2 class="font-extrabold leading-none">
            <span class="text-transparent bg-clip-text bg-gradient-to-r to-emerald-600 from-sky-400">
                Vitrine
            </span>
        </h2>
    </div>
</template>
