<!-- resources/js/components/dashboard/PageCardSkeleton.vue -->
<template>
  <div
    class="bg-white border border-slate-100 rounded-xl p-5 shadow-sm animate-pulse flex flex-col justify-between"
  >
    <!-- Header -->
    <div>
      <div class="flex items-center justify-between mb-3">
        <!-- Ícone + Título -->
        <div class="flex items-center gap-2">
          <div class="w-5 h-5 rounded-md bg-slate-200"></div>
          <div class="h-4 w-28 bg-slate-200 rounded"></div>
        </div>

        <!-- Badge -->
        <div class="h-5 w-14 bg-slate-200 rounded-full"></div>
      </div>

      <!-- Descrição -->
      <div class="space-y-2 mt-3">
        <div class="h-3 w-full bg-slate-200 rounded"></div>
        <div class="h-3 w-4/5 bg-slate-200 rounded"></div>
        <div class="h-3 w-3/5 bg-slate-200 rounded"></div>
      </div>
    </div>

    <!-- Footer -->
    <div class="flex justify-between items-center mt-5">
      <div class="h-6 w-20 bg-amber-200/40 rounded-full"></div>

      <div class="h-8 w-20 bg-sky-200/40 rounded-lg"></div>
    </div>
  </div>
</template>

<style scoped>
/* Elegant shimmer effect */
.animate-pulse {
  animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
  0%,
  100% {
    opacity: 0.7;
  }
  50% {
    opacity: 1;
  }
}
</style>
