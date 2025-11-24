<!-- resources/js/components/dashboard/pages/PageContentDynamic.vue -->
<template>
  <div>
    <SimplesContent v-if="type === 'simple' || type === 'simples' || !type" :page="page" />

    <LinkContent
      v-else-if="type === 'links'"
      :page="page"
      :links="links"
      :LucideIcons="LucideIcons"
      :removeLink="removeLink"
      :iconLinksOptions="iconLinksOptions"
      :addLink="addLink"
    />

    <GaleryContent
      v-else-if="type === 'gallery' || type === 'galeria'"
      :page="page"
      :removeGalleryImage="removeGalleryImage"
      :LucideIcons="LucideIcons"
      :onGallerySelected="onGallerySelected"
    />

    <ReviewContent
      v-else-if="type === 'reviews' || type === 'avaliacoes'"
      :page="page"
      :avaliacoes="avaliacoes"
      :Star="Star"
      :StarOff="StarOff"
      :updateStatus="updateStatus"
    />

    <ProductContent
      v-else-if="type === 'products' || type === 'produtos'"
      :page="page"
      :categorias="categorias"
      :produtos="produtos"
      :formatCurrency="formatCurrency"
      :novaCategoria="novaCategoria"
      :LucideIcons="LucideIcons"
      :removerCategoria="removerCategoria"
      :nomeCategoria="nomeCategoria"
      :salvarCategoria="salvarCategoria"
      :salvarProduto="salvarProduto"
      :editarProduto="editarProduto"
      :onCoverSelected="onProductImageSelected"
      v-model:novoProduto="novoProduto"
      v-model:categoriaSelecionada="categoriaSelecionada"
      v-model:showAddCategory="showAddCategory"
      v-model:showAddProduct="showAddProduct"
    />

    <template v-else>
      <p class="text-sm text-slate-500">Tipo de página não suportado.</p>
    </template>
  </div>
</template>

<script setup lang="ts">
/**
 * Este wrapper espera que o componente pai (Edit.vue) passe TODAS as props
 * necessárias referenciadas abaixo — assim mantemos toda lógica no pai.
 */
import SimplesContent from '@/components/vitrine/SimplesContent.vue'
import LinkContent from '@/components/vitrine/LinkContent.vue'
import GaleryContent from '@/components/vitrine/GaleryContent.vue'
import ReviewContent from '@/components/vitrine/ReviewContent.vue'
import ProductContent from '@/components/vitrine/ProductContent.vue'

const props = defineProps({
  type: String,
  page: Object,
  links: Array,
  LucideIcons: Object,
  removeLink: Function,
  iconLinksOptions: Array,
  addLink: Function,

  removeGalleryImage: Function,
  onGallerySelected: Function,

  avaliacoes: Array,
  Star: Object,
  StarOff: Object,
  updateStatus: Function,

  categorias: Array,
  produtos: Array,
  formatCurrency: Function,
  novaCategoria: Object,
  removerCategoria: Function,
  nomeCategoria: Function,
  salvarCategoria: Function,
  salvarProduto: Function,
  editarProduto: Function,
  onProductImageSelected: Function,
  novoProduto: Object,
  categoriaSelecionada: Object,
  showAddCategory: Object,
  showAddProduct: Object
})
</script>
