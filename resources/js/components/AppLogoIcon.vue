<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { Store } from 'lucide-vue-next';
defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-xl 
        bg-sidebar-primary text-sidebar-primary-foreground bg-gradient-to-r 
        to-emerald-600 from-sky-400 shadow-md">
        <component :is="Store" class="h-6 w-6" />
    </div>    
</template>
