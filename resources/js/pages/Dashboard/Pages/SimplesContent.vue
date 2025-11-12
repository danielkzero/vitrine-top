<script setup lang="ts">
import { QuillEditor } from '@vueup/vue-quill'
defineProps<{
    page: {};
}>()
</script>

<template>
    <!-- Tipo: Simples (texto com Quill) -->
    <div v-if="page.type === 'simple'">
        <QuillEditor class="border px-3 py-2" v-model:content="page.content" content-type="html" />
    </div>
</template>
