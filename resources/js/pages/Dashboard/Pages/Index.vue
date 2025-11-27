<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, usePage, router } from '@inertiajs/vue3'
import PageCard from '@/components/dashboard/PageCard.vue'
import PageCardSkeleton from '@/components/dashboard/PageCardSkeleton.vue'
import { routes } from '@/config/routes'
import draggable from 'vuedraggable'
import { ref } from 'vue'
import { getIcon } from '@/lib/iconMap'
import { route } from 'ziggy-js'

const { props } = usePage()
const pagesOriginal = props.pages?.data || []

const pages = ref([...pagesOriginal]) // ← precisamos tornar reativo para arrastar

const loading = pages.value.length === 0

const Breadcrumbs = [
  { title: 'Painel', href: routes.painel.index },
  { title: 'Páginas', href: '#' }
]

// =========== SALVAR ORDEM ===============
function saveOrder() {
  const ordered = pages.value.map((p, index) => ({
    id: p.id,
    order: index + 1
  }))

  router.post(
    route('painel.pages.reorder'),
    { pages: ordered },
    {
      preserveScroll: true,
      onSuccess: () => {
        console.log("Ordem salva com sucesso!")
      }
    }
  )
}
</script>

<template>

  <Head title="Gerenciamento de Páginas" />

  <AppLayout :breadcrumbs="Breadcrumbs">
    <main class="flex flex-col gap-6 p-6 px-4 container mx-auto">

      <div>
        <h1 class="text-3xl font-bold text-gray-900">Gerenciamento de Páginas</h1>
        <p class="text-gray-500">
          Arraste para reorganizar suas páginas.
        </p>
      </div>

      <!-- LISTA DE PÁGINAS COM DRAGGABLE -->
      <draggable
        v-model="pages"
        item-key="id"
        handle=".drag-handle"
        class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6"
        @end="saveOrder"
      >

        <template #item="{ element }">

          <div class="relative">

            <!-- Handle para arrastar -->
            <div
              class="drag-handle absolute top-2 right-2 p-2 cursor-grab text-gray-400 hover:text-gray-700"
              title="Arrastar para reordenar"
            >
              <component :is="getIcon('Grip')"></component>
            </div>

            <!-- CARD -->
            <PageCard
              :title="element.title"
              :description="element.seo_description"
              :icon="element.icon"
              :type="element.type"
              :active="element.is_active"
              :editHref="`/painel/pages/edit/${element.key}`"
            />

          </div>

        </template>

      </draggable>

      <!-- Sem páginas -->
      <div v-if="!loading && pages.length === 0" class="text-center text-gray-500 mt-10">
        Nenhuma página encontrada.
      </div>

    </main>
  </AppLayout>

</template>
