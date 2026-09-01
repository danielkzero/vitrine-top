<!-- resources/js/components/dashboard/PageCardSkeleton.vue -->
<template>
  <div
    class="flex animate-pulse flex-col justify-between rounded-xl border border-border bg-card p-5 shadow-sm"
  >
    <!-- Header -->
    <div>
      <div class="flex items-center justify-between mb-3">
        <!-- Ícone + Título -->
        <div class="flex items-center gap-2">
          <div class="h-5 w-5 rounded-md bg-muted"></div>
          <div class="h-4 w-28 rounded bg-muted"></div>
        </div>

        <!-- Badge -->
        <div class="h-5 w-14 rounded-full bg-muted"></div>
      </div>

      <!-- Descrição -->
      <div class="space-y-2 mt-3">
        <div class="h-3 w-full rounded bg-muted"></div>
        <div class="h-3 w-4/5 rounded bg-muted"></div>
        <div class="h-3 w-3/5 rounded bg-muted"></div>
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
