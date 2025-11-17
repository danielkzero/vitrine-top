<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import { FileEdit, Globe, Eye, EyeOff, FileText } from 'lucide-vue-next'
import { routes } from '@/config/routes'
import * as LucideIcons from 'lucide-vue-next'

const { props } = usePage()
const pages = props.pages.data || []
const Breadcrumbs = [
  { title: 'Painel', href: routes.painel.index },
  { title: 'Páginas', href: '#' }
]
</script>

<template>

  <Head title="Gerenciamento de Páginas" />


  <AppLayout :breadcrumbs="Breadcrumbs">

    <main class="flex flex-col gap-6 p-6 px-4 container mx-auto">
      <div>
        <!-- Cabeçalho -->
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
          Gerenciamento de Páginas
        </h1>
        <p class="text-gray-500">
          Abaixo estão listadas todas as <b>páginas</b> do seu painel.
          Clique em <b>“Editar”</b> para modificar o conteúdo, o tipo (produtos, galeria, etc.)
          ou ajustar as configurações de SEO.
        </p>
      </div>

      <div>
        <!-- Lista de páginas -->
        <div v-if="pages.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
          <div v-for="page in pages" :key="page.id"
            class="bg-white dark:bg-white/10 dark:border-white/20 dark:text-white border rounded-xl p-4 shadow-sm hover:shadow-md transition flex flex-col justify-between">
            <div>
              <!-- Ícone + Título -->
              <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                  <component :is="page.icon ? LucideIcons[page.icon] : Globe" class="w-5 h-5 text-sky-500 dark:text-white" />
                  <h2 class="font-semibold text-gray-800 dark:text-white truncate">
                    {{ page.title }}
                  </h2>
                </div>

                <span class="inline-flex items-center text-xs font-medium rounded-full px-2 py-0.5" :class="page.is_active
                  ? 'bg-emerald-100 text-emerald-700'
                  : 'bg-gray-200 text-gray-500 dark:text-gray-900'">
                  <component :is="page.is_active ? Eye : EyeOff" class="w-4 h-4 mr-1" />
                  {{ page.is_active ? 'Ativa' : 'Inativa' }}
                </span>
              </div>


              <p class="text-gray-600 dark:text-white text-sm line-clamp-3">
                {{ page.seo_description || 'Sem descrição SEO.' }}
              </p>
            </div>
            <!-- Ações -->
            <div class="flex justify-between items-center mt-4">
              <p class="text-gray-600 text-sm line-clamp-3 bg-amber-300 dark:bg-amber-200 dark:text-gray-800 dark:rounded-xl p-2 px-4 rounded-full">
                {{ 'Tipo: ' + page.type || 'Sem tipo relacionado.' }}
              </p>
              <Link :href="`/painel/pages/edit/${page.key}`"
                class="flex items-center gap-2 bg-sky-600 hover:bg-sky-700 text-white text-sm px-3 py-2 rounded-xl transition">
                <FileEdit class="w-4 h-4" /> Editar
              </Link>
            </div>
          </div>
        </div>

        <div v-else class="text-center text-gray-500 mt-10">
          Nenhuma página encontrada.
        </div>
      </div>
    </main>
  </AppLayout>
</template>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
