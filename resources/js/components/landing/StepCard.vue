<script setup lang="ts">
defineProps({
    number: String,
    title: String,
    description: String,
    icon: Object
});
</script>

<template>
    <div class="relative z-10 bg-slate-50 md:bg-transparent p-4 md:p-0 rounded-lg text-center">
        <div class="w-12 h-12 mx-auto bg-white border-2 border-emerald-500 text-emerald-600 rounded-full flex items-center justify-center text-xl font-bold mb-4 shadow-sm relative">
            <component :is="icon" />

            <div class="absolute -top-2 -right-2 w-6 h-6 bg-slate-900 text-white text-xs rounded-full flex items-center justify-center border-2 border-white">
                {{ number }}
            </div>
        </div>

        <h3 class="font-bold text-slate-800 mb-2">{{ title }}</h3>
        <p class="text-slate-600 text-sm">{{ description }}</p>
    </div>
</template>
