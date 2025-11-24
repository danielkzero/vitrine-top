<script setup lang="ts">
/**
 * Edit.vue (refatorado)
 * - Código limpo, uso mínimo de imports
 * - Normaliza dados vindos do Inertia
 * - Funções de manipulação de imagens robustas
 * - Envio com tratamento de erro
 */

import { ref, computed, watch } from 'vue'
import { Head, usePage, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AppLayout from '@/layouts/AppLayout.vue'
import SimplesContent from '@/components/vitrine/SimplesContent.vue'
import LinkContent from '@/components/vitrine/LinkContent.vue'
import GaleryContent from '@/components/vitrine/GaleryContent.vue'
import ReviewContent from '@/components/vitrine/ReviewContent.vue'
import ProductContent from '@/components/vitrine/ProductContent.vue'
import DropzoneFile from '@/components/ui/dropzone-file/DropzoneFile.vue'
import { formatCurrency } from '@/lib/utils'
import { SquareCheckBig, Star, StarOff, Eye, EyeOff } from 'lucide-vue-next'
import * as LucideIcons from 'lucide-vue-next'

// ---------- Inertia props / normalização ----------
const inertia = usePage()
const rawPage = inertia.props.page ?? {}
const rawAvaliacoes = inertia.props.avaliacoes ?? []
const rawCategorias = inertia.props.categorias ?? []
const rawProdutos = inertia.props.produtos ?? []

// Transformação defensiva: garante arrays e campos esperados
const page = ref<any>({
    ...rawPage,
})
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
    { title: 'Editar', href: '/painel/pages/edit' },
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

    console.log('Capa selecionada:', file)

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

async function salvarCategoria() {
    const name = novaCategoria.value?.trim()
    if (!name) return
    categorias.value.push({ id: Date.now(), name })
    categoriaSelecionada.value = categorias.value[categorias.value.length - 1].id

    await router.post(route('painel.pages.update', page.value.key), {
        categorias: categorias.value
    }, { preserveScroll: true })

    console.log('Categoria salva:', name)

    novaCategoria.value = ''
    showAddCategory.value = false
}

function removerCategoria(categoria: any) {
    if (!confirm('Ao remover esta categoria, todos os produtos dela serão excluídos. Deseja continuar?')) return
    const id = categoria.id
    // remove produtos localmente
    for (let i = produtos.value.length - 1; i >= 0; i--) {
        if (produtos.value[i].category_id === id) produtos.value.splice(i, 1)
    }
    const idx = categorias.value.findIndex(c => c.id === id)
    if (idx !== -1) categorias.value.splice(idx, 1)
    if (categoriaSelecionada.value === id) categoriaSelecionada.value = categorias.value.length ? categorias.value[0].id : null
}

function editarProduto(produto: any) {
    novoProduto.value = JSON.parse(JSON.stringify(produto))
    showAddProduct.value = true
}

/**
 * Salva produto local + envia via Inertia (apenas o produto salvo)
 * Backend será responsável por CRUD completo.
 */
async function salvarProduto() {
    if (!novoProduto.value.name || !novoProduto.value.price) return
    novoProduto.value.category_id = categoriaSelecionada.value

    // Atualiza localmente
    if (novoProduto.value.id) {
        const index = produtos.value.findIndex((p: any) => p.id === novoProduto.value.id)
        if (index !== -1) produtos.value[index] = JSON.parse(JSON.stringify(novoProduto.value))
    } else {
        novoProduto.value.id = null
        produtos.value.push(JSON.parse(JSON.stringify(novoProduto.value)))
    }

    // Envia para backend (apenas o produto atual para otimizar)
    sending.value = true
    try {
        await router.post(route('painel.pages.update', page.value.key), {
            page: page.value,
            produtos: [novoProduto.value],
            categorias: categorias.value
        }, { preserveScroll: true })
    } catch (e) {
        // Opcional: tratar erro
        console.error(e)
    } finally {
        sending.value = false
    }

    // reset
    novoProduto.value = {
        id: null, name: '', price: '', discount_price: '', category_id: categoriaSelecionada.value,
        stock: '', description: '', is_public: true, featured: false, images: []
    }
    showAddProduct.value = false
}

// ---------- Imagens de produto (recebe Array<File> ou FileList, opcionalmente productId) ----------
/**
 * Espera: files: File[] | FileList, productId?: number
 * se productId informado procura produto por id; se não, insere em novoProduto.images
 */
function onProductImageSelected(files: File[] | FileList, productId?: number | null) {
    novoProduto.value.images = []
    if (!files) return;

    // se veio do removeFile(): já vem filesPreview completo
    const isPreviewList = Array.isArray(files) && files[0] && (files[0].url || files[0].file);

    // Se for lista completa (remove)
    if (isPreviewList) {
        novoProduto.value.images = [];

        files.forEach((item: any) => {
            if (item.file) {
                // imagem nova
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
                // imagem antiga convertida novamente
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

    // Se for apenas novas imagens (handleFiles)
    const newFileArray: File[] = Array.from(files as any);

    // MANTÉM AS ANTIGAS
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

</script>

<template>

    <Head title="Gerenciamento de páginas" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <main class="flex-1 md:px-6 gap-6 container mx-auto">
            <div v-if="page">
                <form class="space-y-4" @submit.prevent.stop="handleSubmit">
                    <!-- Tabs -->
                    <div class="border-b border-gray-200 dark:border-white/10 px-4 md:px-0">
                        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                            <button v-for="tab in tabs" :key="tab.name" @click.prevent="activeTab = tab.name"
                                :class="activeTab === tab.name
                                    ? 'border-primary text-primary dark:text-primary font-semibold'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-white'"
                                class="cursor-pointer whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none">
                                {{ tab.label }}
                            </button>
                        </nav>
                    </div>

                    <!-- GENERAL TAB -->
                    <div v-if="activeTab === 'general'" class="md:border p-4 rounded-xl space-y-4 md:shadow">
                        <h2
                            class="text-2xl font-extrabold flex gap-2 p-3 border bg-gray-100/50 dark:bg-white/10 rounded-xl">
                            <component :is="SquareCheckBig" class="h-6 w-6 text-sky-400" />
                            <span
                                class="text-transparent bg-clip-text bg-gradient-to-r to-emerald-600 from-sky-400">Conteúdo
                                da página</span>
                        </h2>

                        <!-- title + icon -->
                        <div>
                            <label class="block text-sm font-medium mb-1">Título e ícone</label>
                            <div class="flex items-center gap-3">
                                <component v-if="page.icon" :is="LucideIcons[page.icon]"
                                    class="h-6 w-6 text-sky-500 cursor-pointer hover:scale-110"
                                    @click="showIconPicker = !showIconPicker" />
                                <input v-model="page.title" type="text" placeholder="Título da página"
                                    class="flex-1 border rounded-xl px-3 py-2" />
                                <button type="button" @click="page.is_active = !page.is_active"
                                    class="inline-flex items-center text-sm font-medium rounded-xl px-2 py-2"
                                    :class="page.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-500'">
                                    <component :is="page.is_active ? Eye : EyeOff" class="w-6 h-6" />
                                </button>
                            </div>

                            <div v-if="showIconPicker"
                                class="mt-2 grid md:grid-cols-6 grid-cols-3 gap-2 border p-3 rounded-xl bg-white dark:bg-white/10 shadow-lg">
                                <button v-for="opt in iconOptions" :key="opt.name" type="button"
                                    @click="page.icon = opt.name; showIconPicker = false"
                                    class="p-2 hover:bg-gray-100 rounded-xl">
                                    <component :is="LucideIcons[opt.name]" class="h-6 w-6" />
                                    <div class="text-xs mt-1 text-gray-500">{{ opt.label }}</div>
                                </button>
                            </div>
                        </div>

                        <!-- conteúdo -->
                        <div>
                            <label class="block text-sm font-medium mb-1">Conteúdo</label>

                            <SimplesContent :page="page" />

                            <LinkContent v-if="page.type === 'links'" :page="page" :links="links"
                                :LucideIcons="LucideIcons" :removeLink="removeLink" :iconLinksOptions="iconLinksOptions"
                                :addLink="addLink" />

                            <GaleryContent v-if="page.type === 'gallery'" :page="page"
                                :removeGalleryImage="(idx) => page.value.content?.splice(idx, 1)"
                                :LucideIcons="LucideIcons" :onGallerySelected="(e) => onGallerySelected(e)" />

                            <ReviewContent v-if="page.type === 'reviews'" :page="page" :avaliacoes="avaliacoes"
                                :Star="Star" :StarOff="StarOff" :updateStatus="() => { }" />

                            <ProductContent v-if="page.type === 'products'" :page="page" :categorias="categorias"
                                :produtos="produtos" :formatCurrency="formatCurrency" :novaCategoria="novaCategoria"
                                :LucideIcons="LucideIcons" :removerCategoria="removerCategoria"
                                :nomeCategoria="nomeCategoria" :salvarCategoria="salvarCategoria"
                                :salvarProduto="salvarProduto" :editarProduto="editarProduto"
                                :onCoverSelected="onProductImageSelected" v-model:novoProduto="novoProduto"
                                v-model:categoriaSelecionada="categoriaSelecionada"
                                v-model:showAddCategory="showAddCategory" v-model:showAddProduct="showAddProduct" />
                        </div>
                    </div>

                    <!-- SEO TAB -->
                    <div v-if="activeTab === 'seo'" class="md:border p-4 rounded-xl space-y-4 md:shadow">
                        <h2
                            class="text-2xl font-extrabold flex gap-2 p-3 border bg-gray-100/50 dark:bg-white/10 rounded-xl">
                            <component :is="SquareCheckBig" class="h-6 w-6 text-sky-400" />
                            <span
                                class="text-transparent bg-clip-text bg-gradient-to-r to-emerald-600 from-sky-400">SEO</span>
                        </h2>

                        <div>
                            <label class="block text-sm font-medium mb-1">Título SEO</label>
                            <input v-model="page.seo_title" type="text" class="w-full border rounded-xl px-3 py-2" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Descrição SEO</label>
                            <textarea v-model="page.seo_description"
                                class="w-full border rounded-xl px-3 py-2 h-20"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Imagem de Capa</label>
                            <DropzoneFile :onCoverSelected="onCoverSelected"
                                titleFileTypes="<span class='font-semibold'>Clique para enviar</span> ou arraste e solte"
                                displayFileTypes="SVG, PNG, JPG ou GIF (MAX. 800x400px)" />
                        </div>

                        <div v-if="coverPreview" class="mt-4 flex justify-center">
                            <img :src="coverPreview" alt="Prévia da capa"
                                class="max-h-48 rounded-xl shadow border object-cover" />
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" :disabled="sending"
                            class="px-4 py-2 bg-primary text-white rounded-xl hover:bg-primary/80">
                            {{ sending ? 'Salvando...' : 'Salvar Alterações' }}
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </AppLayout>
</template>

<style scoped>
li:active {
    cursor: grabbing;
}
</style>
