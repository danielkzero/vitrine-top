<script setup lang="ts">
defineProps<{ page: Record<string, any> }>()
</script>

<template>
  <div class="prose max-w-full md:max-w-none md:bg-transparent md:p-0 md:shadow-none md:backdrop-blur-none">
    <div v-html="page.content"></div>
  </div>
</template>

<style scoped>
.prose { line-height: 1.6; }
</style>
