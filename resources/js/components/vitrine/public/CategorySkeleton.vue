<script setup lang="ts">
const widths = [
    'w-10',
    'w-14',
    'w-20',
    'w-24',
    'w-32',
]

const randomWidth = () =>
    widths[Math.floor(Math.random() * widths.length)]
</script>

<template>
    <div class="animate-pulse bg-white rounded-xl shadow-sm max-w-32">
        <div class="flex items-center gap-2 p-2">
            <div class="h-4 bg-slate-100 rounded" :class="randomWidth()" />
            <div class="h-4 bg-slate-100 rounded" :class="randomWidth()" />
            <div class="h-4 bg-slate-100 rounded" :class="randomWidth()" />
        </div>
    </div>
</template>
