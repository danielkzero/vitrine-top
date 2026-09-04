<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'

defineProps<{
  plans: Array<{
    id: number
    code: string
    name: string
    monthly_price: number
    annual_price_total: number
    annual_monthly_equivalent: number
    limits: {
      products: number
      product_images: number | null
      gallery_images: number
      banners: number
    }
    trial_days: number
  }>
  trial_days: number
}>()
</script>

<template>
  <Head title="Planos" />

  <div class="min-h-screen bg-background p-6 text-foreground">
    <div class="mx-auto max-w-5xl space-y-6 rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm">
      <div class="text-center">
        <h1 class="text-2xl font-bold">Planos da plataforma</h1>
        <p class="mt-2 text-sm text-muted-foreground">
          Todos os planos incluem período de teste grátis de {{ trial_days }} dias.
        </p>
      </div>

      <div class="grid md:grid-cols-2 gap-4">
        <article
          v-for="plan in plans"
          :key="plan.id"
          class="rounded-xl border border-border bg-muted/50 p-4"
        >
          <p class="font-semibold text-lg">{{ plan.name }}</p>
          <p class="mt-1 text-sm text-muted-foreground">
            Mensal: R$ {{ plan.monthly_price.toFixed(2).replace('.', ',') }}
          </p>
          <p class="text-sm text-muted-foreground">
            Anual: R$ {{ plan.annual_monthly_equivalent.toFixed(2).replace('.', ',') }}/mês
            (R$ {{ plan.annual_price_total.toFixed(2).replace('.', ',') }} total)
          </p>

          <ul class="mt-3 space-y-1 text-sm text-foreground">
            <li>Produtos: {{ plan.limits.products }}</li>
            <li>Fotos por produto: {{ plan.limits.product_images ?? 'Ilimitadas' }}</li>
            <li>Fotos na galeria: {{ plan.limits.gallery_images }}</li>
            <li>Banners: {{ plan.limits.banners }}</li>
          </ul>
        </article>
      </div>

      <div class="text-center">
        <Link
          href="/register"
          class="inline-flex items-center justify-center rounded-lg bg-emerald-600 text-white px-5 py-2 font-medium"
        >
          Criar conta e iniciar trial
        </Link>
      </div>
    </div>
  </div>
</template>
