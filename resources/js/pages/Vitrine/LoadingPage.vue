<script setup lang="ts">
defineProps<{
	user: any;
}>()
const load = defineModel<any>('load', { required: true })
</script>

<template>
	<div class="flex flex-col items-center justify-center min-h-screen py-20">
	    <div class="relative w-20 h-20 flex items-center justify-center">
	      <!-- CÍRCULO ANIMADO -->
	      <div class="absolute inset-0 rounded-full border-4 border-amber-300/30 animate-ping"></div>
	
	      <!-- LOGO ROTACIONANDO -->
	      <img :src="load?.logo_path" class="w-20 h-20 object-cover rounded-3xl shadow-md animate-spin-slow" />
	    </div>
	
	    <p class="mt-6 text-lg font-medium animate-pulse" :style="{ color: load?.theme_color }">
	      Carregando... 
	    </p>
	  </div>
</template>
