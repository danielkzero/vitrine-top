<script setup lang="ts">
import { useAttrs } from 'vue';
import { Store } from 'lucide-vue-next';

// Não herdar atributos automaticamente para poder mesclar classes manualmente
defineOptions({ inheritAttrs: false });

const attrs = useAttrs();
</script>

<template>
    <div :class="['flex aspect-square size-8 items-center justify-center',
        'rounded-xl bg-sidebar-primary text-sidebar-primary-foreground',
        'shadow-md', attrs.class]">
        <component :is="Store" class="h-6 w-6" />
    </div>
</template>
