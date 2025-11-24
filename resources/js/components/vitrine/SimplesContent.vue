<script setup lang="ts">
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css'
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
<style scoped>

/* Conteúdo interno do editor (sem usar @apply) */
:deep(.ql-editor) {
    min-height: 15rem;
    line-height: 1.6;
    /* leading-relaxed */
}


/* placeholder do editor */
:deep(.ql-editor.ql-blank::before) {
    color: #9ca3af;
    /* text-gray-400 */
}

/* toolbar e container */
:deep(.ql-toolbar) {
    /* border-gray-200 */
    border-top-left-radius: 0.8rem;
    border-top-right-radius: 0.8rem;
}

:deep(.ql-container) {
    border-bottom-left-radius: 0.8rem;
    border-bottom-right-radius: 0.8rem;
}

/* Editor - texto */
:global(html.dark .ql-editor) {
  color: #e5e7eb; /* text-gray-200 */
}

/* Container */
:global(html.dark .ql-container) {
  border-color: rgb(var(--color-gray-custom-10)); /* gray-700 */
}

/* Toolbar fundo */
:global(html.dark .ql-toolbar) {
  /* cor de fundo --color-white mais 10% de opatidade */
  background-color: rgb(var(--color-gray-custom)); /* 80% opaco */
  border-color: rgb(var(--color-gray-custom-10));
}

/* Ícones (stroke e fill) */
:global(html.dark .ql-toolbar .ql-stroke) {
  stroke: #e5e7eb !important;
}

:global(html.dark .ql-toolbar button:hover) {
  background-color: rgb(var(--color-gray-custom-30)) !important;
}
:global(html.dark .ql-toolbar .ql-active) {
  background-color: rgb(var(--color-gray-custom-30)) !important;
}

:global(html.dark .ql-toolbar .ql-fill:hover) {
  fill: rgb(var(--color-gray-custom-30)) !important;
}

/* Dropdown labels */
:global(html.dark .ql-picker-label) {
  color: #e5e7eb !important;
  background-color: rgb(var(--color-gray-custom)) !important;
}

/* Dropdown options */
:global(html.dark .ql-picker-options) {
  background-color: rgb(var(--color-gray-custom)) !important;
  border-color: rgb(var(--color-gray-custom-10)) !important;
}

/* Ícones dentro das opções */
:global(html.dark .ql-picker-options .ql-picker-item) {
  color: #e5e7eb !important;
}

/* Ícones dentro das opções */
:global(html.dark .ql-picker-options .ql-selected) {
  color: #e5e7eb !important;
  background-color: rgb(var(--color-gray-custom-30)) !important;
}

:global(html.dark .ql-picker-options .ql-picker-item:hover) {
  color: #e5e7eb !important;
  background-color: rgb(var(--color-gray-custom-20)) !important;
}

</style>
