<script setup lang="ts">
const props = defineProps({ list: { type: Boolean, default: false } })
</script>

<template>
    <div v-if="!list" class="animate-pulse rounded-xl p-3 bg-slate-100">
        <div class="h-28 bg-slate-200 rounded-lg"></div>
        <div class="mt-2 h-4 bg-slate-200 rounded w-3/4"></div>
        <div class="mt-2 h-4 bg-slate-200 rounded w-1/2"></div>
    </div>

    <div v-else class="animate-pulse flex gap-3 items-center p-3 bg-slate-100 rounded-lg">
        <div class="w-20 h-20 bg-slate-200 rounded-lg"></div>
        <div class="flex-1">
            <div class="h-4 bg-slate-200 rounded w-3/4 mb-2"></div>
            <div class="h-3 bg-slate-200 rounded w-1/2"></div>
        </div>
    </div>
</template>
