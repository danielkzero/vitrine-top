<script setup>
defineProps({ page: Object })
</script>

<template>
  <div>
    <div v-if="Array.isArray(page.content) && page.content.length" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
      <img v-for="(img, idx) in page.content" :key="idx" :src="img" class="w-full h-44 object-cover rounded-lg shadow" />
    </div>
    <div v-else class="text-slate-500">Nenhuma imagem na galeria.</div>
  </div>
</template>
