<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

const props = defineProps<{ page: any }>();
</script>

<template>
  <Head :title="props.page.seo_title || props.page.title" />

  <div class="max-w-3xl mx-auto py-10 px-4">
    <h1 class="text-3xl font-bold mb-6">{{ props.page.title }}</h1>

    <img
      v-if="props.page.cover_image"
      :src="props.page.cover_image"
      class="rounded-lg mb-6 w-full object-cover"
    />

    <div class="prose max-w-none" v-html="props.page.content"></div>
  </div>
</template>
