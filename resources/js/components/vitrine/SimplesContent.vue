<script setup lang="ts">
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css'

defineProps<{
  page: any
}>()
</script>

<template>
  <div v-if="page.type === 'simple'" class="space-y-2">
    <label class="text-sm font-medium text-slate-700 dark:text-slate-200">
      Conteúdo da Página
    </label>

    <div
      class="rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm 
             focus-within:ring-2 focus-within:ring-emerald-500 transition-all duration-200"
    >
      <QuillEditor
        class="bg-white dark:bg-slate-900"
        v-model:content="page.content"
        content-type="html"
        placeholder="Digite o conteúdo da página..."
      />
    </div>
  </div>
</template>

<style scoped>
/* ===========================
   Estilização do editor Quill
   =========================== */

/* Editor mínimo */
:deep(.ql-editor) {
  min-height: 14rem;
  padding: 1.25rem;
  font-size: 1rem;
  line-height: 1.65;
  color: #1f2937; /* slate-800 */
}

:global(html.dark .ql-editor) {
  color: #e5e7eb; /* slate-200 */
}

/* Placeholder */
:deep(.ql-editor.ql-blank::before) {
  color: #9ca3af !important; /* slate-400 */
  font-style: normal;
}

/* Container */
:deep(.ql-container) {
  border: none !important;
}

/* Toolbar */
:deep(.ql-toolbar) {
  border: none !important;
  padding: 0.5rem;
  background: #f9fafb; /* slate-50 */
}

:global(html.dark .ql-toolbar) {
  background: #1e293b !important; /* slate-800 */
  border-bottom: 1px solid #334155 !important; /* slate-700 */
}

/* Botões */
:deep(.ql-toolbar button) {
  border-radius: 0.375rem !important; /* rounded-md */
  transition: background 0.2s;
}

:deep(.ql-toolbar button:hover) {
  background-color: #e2e8f0 !important; /* slate-200 */
}

:global(html.dark .ql-toolbar button:hover) {
  background-color: #334155 !important; /* slate-700 */
}

/* Ícones */
:global(html.dark .ql-stroke) {
  stroke: #e5e7eb !important;
}
:global(html.dark .ql-fill) {
  fill: #e5e7eb !important;
}

/* Dropdowns */
:global(html.dark .ql-picker-options) {
  background: #1e293b !important;
  border-color: #475569 !important;
}
:global(html.dark .ql-picker-item) {
  color: #e5e7eb !important;
}
</style>
