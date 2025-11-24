<script setup lang="ts">
defineProps({
    icon: Object,
    title: String,
    description: String
});
</script>

<template>
    <div class="p-6 rounded-xl border border-slate-100 bg-white hover:shadow-xl hover:shadow-emerald-500/5 hover:-translate-y-1 transition-all duration-300 group">
        <div class="w-12 h-12 rounded-lg bg-slate-50 flex items-center justify-center mb-4 group-hover:bg-emerald-50 transition-colors">
            <component :is="icon" />
        </div>
        <h3 class="text-xl font-bold text-slate-800 mb-2">{{ title }}</h3>
        <p class="text-slate-600 leading-relaxed">{{ description }}</p>
    </div>
</template>
