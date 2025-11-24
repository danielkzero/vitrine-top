<script setup lang="ts">
defineProps({
    text: String,
    highlight: Boolean
});
</script>

<template>
    <li class="flex items-start gap-2 text-sm">
        <CheckCircle2 size="16" :class="highlight ? 'text-emerald-500' : 'text-slate-400'" />
        <span :class="highlight ? 'text-slate-800 font-medium' : 'text-slate-600'">
            {{ text }}
        </span>
    </li>
</template>

<script>
import { CheckCircle2 } from "lucide-vue-next";
</script>
