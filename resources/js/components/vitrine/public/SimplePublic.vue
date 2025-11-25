<script setup>
defineProps({ page: Object })
</script>

<template>
  <div class="prose max-w-full">
    <div v-html="page.content"></div>
  </div>
</template>

<style scoped>
.prose { line-height: 1.6; }
</style>
