<script setup lang="ts">

import { ref, watch } from 'vue'
import { Head, usePage, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

import AppLayout from '@/layouts/AppLayout.vue'
import PageTabs from '@/components/dashboard/pages/PageTabs.vue'
import PageGeneralSection from '@/components/dashboard/pages/PageGeneralSection.vue'
import PageSeoSection from '@/components/dashboard/pages/PageSeoSection.vue'
import PageFormActions from '@/components/dashboard/pages/PageFormActions.vue'

import SimplesContent from '@/components/vitrine/SimplesContent.vue'
import LinkContent from '@/components/vitrine/LinkContent.vue'
import GaleryContent from '@/components/vitrine/GaleryContent.vue'
import ReviewContent from '@/components/vitrine/ReviewContent.vue'
import ProductContent from '@/components/dashboard/products/ProductContent.vue'


// ---------- Inertia props / normalização ----------
const inertia = usePage()
const rawPage = inertia.props.page ?? {}
const rawAvaliacoes = inertia.props.avaliacoes ?? []
const rawCategorias = inertia.props.categorias ?? []
const rawProdutos = inertia.props.produtos ?? []

// Transformação defensiva: garante arrays e campos esperados
const page = ref<any>({ ...rawPage })
const avaliacoes = ref<any[]>(Array.isArray(rawAvaliacoes) ? rawAvaliacoes : [])
const categorias = ref<any[]>(Array.isArray(rawCategorias) ? rawCategorias : [])
const produtos = ref<any[]>(
  (Array.isArray(rawProdutos) ? rawProdutos : []).map(p => ({
    ...p,
    images: Array.isArray(p.images) ? p.images : [],
    imagensParaExcluir: Array.isArray(p.imagensParaExcluir) ? p.imagensParaExcluir : []
  }))
)

// ---------- UI state ----------
const coverPreview = ref<string | null>(page.value.cover_image ?? null)
const activeTab = ref<'general' | 'seo'>('general')
const sending = ref(false)
const showIconPicker = ref(false)

// ---------- Constants ----------
const VALID_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/jpg', 'image/bmp', 'image/webp']
const MAX_IMAGE_BYTES = 1024 * 1024 // 1MB

const tabs = [
  { name: 'general', label: 'Geral' },
  { name: 'seo', label: 'SEO' },
]

const breadcrumbs = [
  { title: 'Painel', href: '/painel' },
  { title: 'Páginas', href: '/painel/pages' },
  { title: 'Editar', href: `/painel/pages/edit/${page.value.key ?? ''}` },
]

const iconOptions = [
  { name: 'EyeOff', label: 'Ocultar' }, { name: 'Home', label: 'Painel' }, { name: 'Book', label: 'Livro' },
  { name: 'Image', label: 'Galeria' }, { name: 'Link', label: 'Links' }, { name: 'BadgeInfo', label: 'Sobre' },
  { name: 'Star', label: 'Destaque' }, { name: 'User', label: 'Usuário' }, { name: 'ShoppingCart', label: 'Shop' },
  { name: 'Heart', label: 'Favoritos' }, { name: 'Package', label: 'Pacotes' }, { name: 'FileText', label: 'Docs' }
]

const iconLinksOptions = [
  { label: 'Link padrão', value: 'Link' }, { label: 'Facebook', value: 'Facebook' },
  { label: 'Instagram', value: 'Instagram' }, { label: 'Twitter', value: 'Twitter' },
  { label: 'YouTube', value: 'Youtube' }, { label: 'LinkedIn', value: 'Linkedin' },
]

// ---------- Helpers ----------
function isImageFile(file: File) {
  return VALID_IMAGE_TYPES.includes(file.type)
}

function validateFile(file: File) {
  if (!isImageFile(file)) return `Formato inválido: ${file.name}`
  if (file.size > MAX_IMAGE_BYTES) return `Arquivo grande: ${file.name} (máx 1MB)`
  return null
}

// Ao mudar page.cover_image externamente atualiza preview
watch(() => page.value.cover_image, v => {
  coverPreview.value = v ?? null
})

// ---------- Cover (page) ----------
function onCoverSelected(event: Event) {
  const input = event.target as HTMLInputElement
  const file = input?.files?.[0]
  if (!file) return

  const err = validateFile(file)
  if (err) { alert(err); return }

  const reader = new FileReader()
  reader.onload = e => {
    const result = e.target?.result as string
    coverPreview.value = result
    page.value.cover_image = result
  }
  reader.readAsDataURL(file)
}

// ---------- Links (when page.type === 'links') ----------
const links = ref<any[]>(
  page.value.type === 'links' && page.value.content ? safeParseJSON(page.value.content, []) : [{ icon: 'Link', url: '', text: '', showText: true }]
)

function safeParseJSON(value: any, fallback = []) {
  try { return JSON.parse(value) } catch { return fallback }
}
function addLink() { links.value.push({ icon: 'Link', url: '', text: '', showText: true }) }
function removeLink(i: number) { links.value.splice(i, 1) }

// ---------- Produtos / categorias (local CRUD) ----------
const categoriaSelecionada = ref<number | null>(categorias.value.length ? categorias.value[categorias.value.length - 1].id : null)
const showAddCategory = ref(false)
const showAddProduct = ref(false)
const novaCategoria = ref('')

const novoProduto = ref<any>({
  id: null, name: '', price: '', discount_price: '', category_id: categoriaSelecionada.value,
  stock: '', description: '', is_public: true, featured: false, images: [], imagensParaExcluir: []
})

function nomeCategoria(id: number) {
  const c = categorias.value.find((x: any) => x.id === id)
  return c ? c.name : ''
}

async function salvarCategoria(categoriaNome: string) {
  const name = categoriaNome?.trim();
  if (!name) return;

  // Criar categoria nova sem ID
  categorias.value.push({ name });

  console.log('Categorias enviadas:', categorias.value);

  await router.post(
    route('painel.pages.update', page.value.key),
    { 
      page: page.value,
      categorias: categorias.value },
    { preserveScroll: true }
  );

  novaCategoria.value = '';
  showAddCategory.value = false;

  // Seleciona automaticamente a última categoria criada
  categoriaSelecionada.value = categorias.value.length
    ? categorias.value[categorias.value.length - 1].id ?? null
    : null;
}

async function removerCategoria(categoria: any) {
  if (!confirm('Ao remover esta categoria, todos os produtos dela serão excluídos. Deseja continuar?')) 
    return;

  const id = categoria.id;

  if (!id) {
    // Categoria ainda não existe no backend → só remover localmente
    categorias.value = categorias.value.filter(c => c !== categoria);
    produtos.value = produtos.value.filter(p => p.category_id !== id);
    return;
  }

  try {
    // ⚡ chama backend DELETE
    await router.delete(route('painel.categories.destroy', id), {
      preserveScroll: true,
    });

    // Remover produtos da categoria
    produtos.value = produtos.value.filter(p => p.category_id !== id);

    // Remover categoria local
    categorias.value = categorias.value.filter(c => c.id !== id);

    // Ajustar categoria selecionada
    if (categoriaSelecionada.value === id) {
      categoriaSelecionada.value = categorias.value.length
        ? categorias.value[0].id
        : null;
    }
  } catch (e) {
    console.error(e);
    alert('Erro ao remover categoria.');
  }
}


function editarProduto(produto: any) {
  novoProduto.value = JSON.parse(JSON.stringify(produto))
  showAddProduct.value = true
}

async function salvarProduto(produtoRecebido: any) {
  if (!produtoRecebido.name || !produtoRecebido.price) return

  // Usa o category_id vindo do modal (correto)
  const produtoProcessado = JSON.parse(JSON.stringify(produtoRecebido))

  // Atualiza localmente
  if (produtoProcessado.id) {
    const index = produtos.value.findIndex((p: any) => p.id === produtoProcessado.id)
    if (index !== -1) produtos.value[index] = produtoProcessado
  } else {
    produtoProcessado.id = null
    produtos.value.push(produtoProcessado)
  }

  // Envia para o backend
  sending.value = true
  try {
    await router.post(route('painel.pages.update', page.value.key), {
      page: page.value,
      produtos: [produtoProcessado],
      categorias: categorias.value
    }, { preserveScroll: true })
  } finally {
    sending.value = false
  }

  showAddProduct.value = false
}

// ---------- Imagens de produto (recebe Array<File> ou FileList, opcionalmente productId) ----------
function onProductImageSelected(files: File[] | FileList, productId?: number | null) {
  novoProduto.value.images = []
  if (!files) return;

  const isPreviewList = Array.isArray(files) && files[0] && (files[0].url || files[0].file);

  if (isPreviewList) {
    novoProduto.value.images = [];

    files.forEach((item: any) => {
      if (item.file) {
        const reader = new FileReader();
        reader.onload = e => {
          novoProduto.value.images.push({
            id: null,
            image_path: null,
            image_base64: e.target?.result,
            is_cover: false
          });
        };
        reader.readAsDataURL(item.file);
      } else if (item.url) {
        novoProduto.value.images.push({
          id: null,
          image_path: null,
          image_base64: item.url,
          is_cover: false
        });
      }
    });

    return;
  }

  const newFileArray: File[] = Array.from(files as any);

  const oldImages = [...novoProduto.value.images];

  novoProduto.value.images = [...oldImages];

  newFileArray.forEach(file => {
    const err = validateFile(file);
    if (err) return;

    const reader = new FileReader();
    reader.onload = e => {
      novoProduto.value.images.push({
        id: null,
        image_path: null,
        image_base64: e.target?.result,
        is_cover: false
      });
    };
    reader.readAsDataURL(file);
  });
}

// ---------- Submit geral da página ----------
async function handleSubmit() {
  if (page.value.type === 'links') {
    page.value.content = JSON.stringify(links.value)
  }

  sending.value = true
  console.log(page.value)
  try {
    await router.post(route('painel.pages.update', page.value.key), {
      page: page.value,
      produtos: produtos.value,
      categorias: categorias.value
    }, { preserveScroll: true })
  } catch (e) {
    console.error('Erro ao enviar', e)
  } finally {
    sending.value = false
  }
}

async function cancel() {
  //cancelar
  await router.get(route('painel.pages.index'));
}

</script>

<template>

  <Head title="Gerenciamento de páginas" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <main class="flex-1 md:px-6 gap-6 container mx-auto">
      <div v-if="page">
        <form class="space-y-4" @submit.prevent.stop="handleSubmit">

          <!-- Tabs -->
          <PageTabs v-model="activeTab" :tabs="tabs" />

          <!-- GENERAL TAB -->
          <PageGeneralSection v-if="activeTab === 'general'" :page="page" :icon-options="iconOptions">

            <!-- Conteúdo dinâmico (reaproveita componentes já existentes) -->
            <SimplesContent :page="page" />

            <LinkContent v-if="page.type === 'links'" :page="page" :links="links" :removeLink="removeLink"
              :iconLinksOptions="iconLinksOptions" :addLink="addLink" />

            <GaleryContent v-if="page.type === 'gallery'" :page="page"
              :removeGalleryImage="(idx) => page.value.content?.splice(idx, 1)"
              :onGallerySelected="(e) => onProductImageSelected(e)" />

            <ReviewContent v-if="page.type === 'reviews'" :page="page" :avaliacoes="avaliacoes"
              :updateStatus="() => { }" />

            <ProductContent v-if="page.type === 'products'" :page="page" :categorias="categorias" :produtos="produtos"
              :novaCategoria="novaCategoria" :removerCategoria="removerCategoria" :nomeCategoria="nomeCategoria"
              :salvarCategoria="salvarCategoria" :salvarProduto="salvarProduto" :editarProduto="editarProduto"
              :onCoverSelected="onProductImageSelected" />

          </PageGeneralSection>

          <!-- SEO TAB -->
          <PageSeoSection v-if="activeTab === 'seo'" :page="page" :cover-preview="coverPreview"
            :on-cover-selected="onCoverSelected" />

          <!-- Actions -->
          <div class="flex justify-end">
            <PageFormActions :sending="sending" v-on:cancel="cancel" />
          </div>

        </form>
      </div>
    </main>
  </AppLayout>
</template>
