<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { QuillEditor } from '@vueup/vue-quill'
import { SquareCheckBig, UploadCloud, Star, StarOff, Eye, EyeOff } from 'lucide-vue-next'
import * as LucideIcons from 'lucide-vue-next'
import '@vueup/vue-quill/dist/vue-quill.snow.css'
import { formatCurrency } from '@/lib/utils'
import SimplesContent from '@/components/vitrine/SimplesContent.vue'
import LinkContent from '@/components/vitrine/LinkContent.vue'
import GaleryContent from '@/components/vitrine/GaleryContent.vue'
import ReviewContent from '@/components/vitrine/ReviewContent.vue'
import ProductContent from '@/components/vitrine/ProductContent.vue'
// Props do Inertia (agora vem uma única página)resources\js\components\vitrine\GaleryContent.vue
const inertia = usePage()
const page = ref(inertia.props.page || {}) // ✅ página atual
const avaliacoes = inertia.props.avaliacoes || []
const categorias = inertia.props.categorias || []
const produtos = inertia.props.produtos || []

// Controle de abas
const activeTab = ref('geral')

// Preview da imagem de capa
const coverPreview = ref(page.value.cover_image ?? null)

// Função para atualizar imagem de capa
function onCoverSelected(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0]
    if (!file) return

    const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/bmp', 'image/webp']
    if (!validTypes.includes(file.type)) {
        alert('Formato inválido. Use JPG, PNG, BMP ou WEBP.')
        return
    }
    if (file.size > 1024 * 1024) {
        alert('Tamanho máximo 1MB.')
        return
    }

    const reader = new FileReader()
    reader.onload = (e) => {
        coverPreview.value = e.target?.result as string
        page.value.cover_image = coverPreview.value
    }
    reader.readAsDataURL(file)
}

// Breadcrumbs
const breadcrumbs = [
    { title: 'Painel', href: '/painel' },
    { title: 'Páginas', href: '/painel/pages' },
    { title: 'Editar', href: '/painel/pages/edit' },
]

// Opções de ícones para páginas
const iconOptions = [
    { name: 'EyeOff', label: 'Ocultar' },
    { name: 'Home', label: 'Painel' },
    { name: 'Book', label: 'Livro' },
    { name: 'Image', label: 'Galeria' },
    { name: 'Link', label: 'Links' },
    { name: 'BadgeInfo', label: 'Sobre' },
    { name: 'Star', label: 'Destaque' },
    { name: 'User', label: 'Usuário' },
    { name: 'ShoppingCart', label: 'Shop' },
    { name: 'Heart', label: 'Favoritos' },
    { name: 'Package', label: 'Pacotes' },
    { name: 'FileText', label: 'Docs' },
]

// Ícones para os links sociais
const iconLinksOptions = [
    { label: 'Link padrão', value: 'Link' },
    { label: 'Facebook', value: 'Facebook' },
    { label: 'Instagram', value: 'Instagram' },
    { label: 'Twitter', value: 'Twitter' },
    { label: 'YouTube', value: 'Youtube' },
    { label: 'LinkedIn', value: 'Linkedin' },
]

// Controle do dropdown
const showIconPicker = ref(false)

// Alternar ícone
function selectIcon(name: string) {
    page.value.icon = name
    showIconPicker.value = false
}


// Links dinâmicos (caso type seja 'links')
const links = ref(page.value.content || [
    { icon: 'Link', url: '', text: '', showText: true },
])

function addLink() {
    links.value.push({ icon: 'Link', url: '', text: '', showText: true })
}

function removeLink(index: number) {
    links.value.splice(index, 1)
}

// Galeria
function onGallerySelected(event: Event) {
    const files = (event.target as HTMLInputElement).files
    if (!files?.length) return

    const validTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg']
    const newImages: string[] = []

    Array.from(files).forEach(file => {
        if (!validTypes.includes(file.type)) {
            alert(`Arquivo ${file.name} inválido. Use JPG, PNG ou WEBP.`)
            return
        }
        if (file.size > 1024 * 1024) {
            alert(`Arquivo ${file.name} ultrapassa 1MB.`)
            return
        }

        const reader = new FileReader()
        reader.onload = (e) => {
            newImages.push(e.target?.result as string)
            if (!Array.isArray(page.value.content)) {
                page.value.content = []
            }
            page.value.content.push(e.target?.result as string)
        }
        reader.readAsDataURL(file)
    })
}

function removeGalleryImage(index: number) {
    if (Array.isArray(page.value.content)) {
        page.value.content.splice(index, 1)
    }
}

function handleSubmit() {
    // Em breve: enviar via Inertia.post(route('painel.pages.update', page.value.key), page.value)
    console.log('Salvar página:', page.value)
}




// ================================
// Produtos e Categorias (CRUD local)
// ================================
const categoriaSelecionada = ref<number | null>(categorias.length ? categorias[0].id : null)
const showAddCategory = ref(false)
const showAddProduct = ref(false)
const novaCategoria = ref('')
const novoProduto = ref({ name: '', price: '', description: '', category_id: null })

function nomeCategoria(id: number) {
    const c = categorias.find((c: any) => c.id === id)
    return c ? c.name : ''
}

function salvarCategoria() {
    if (!novaCategoria.value.trim()) return
    categorias.push({ id: Date.now(), name: novaCategoria.value })
    categoriaSelecionada.value = Date.now()
    novaCategoria.value = ''
    showAddCategory.value = false
}

function removerCategoria(categoria: any) {
    if (confirm('Ao remover esta categoria, todos os produtos dela serão excluídos. Deseja continuar?')) {
        const id = categoria.id
        produtos.splice(0, produtos.length, ...produtos.filter(p => p.category_id !== id))
        const idx = categorias.findIndex((c: any) => c.id === id)
        if (idx !== -1) categorias.splice(idx, 1)
        if (categoriaSelecionada.value === id) categoriaSelecionada.value = null
    }
}

function salvarProduto() {
    if (!novoProduto.value.name || !novoProduto.value.price) return
    novoProduto.value.category_id = categoriaSelecionada.value
    produtos.push({ id: Date.now(), ...novoProduto.value })
    novoProduto.value = { name: '', price: '', description: '', category_id: null }
    showAddProduct.value = false
}
function updateStatus(item: any) {
    console.log(`Atualizando status da avaliação #${item.id} para: ${item.status}`)
    // Exemplo: se quiser futuramente mandar para o backend:
    // Inertia.post(route('painel.reviews.update', item.id), { status: item.status })
}
</script>
<template>

    <Head title="Gerenciamento de páginas" />
    <AppLayout :breadcrumbs="breadcrumbs">

        <!-- Formulário de edição -->
        <main class="flex-1 py-4 md:py-6 md:px-6 gap-6 p-6 px-4 container mx-auto">
            <div class="">

                <div v-if="page">
                    <form class="space-y-4" @submit.prevent="handleSubmit">
                        <!-- Tab Geral -->
                        <div class="md:border p-4 rounded-xl space-y-4 md:shadow">
                            <h2
                                class="text-2xl font-extrabold leading-none 
                            tracking-tight md:text-2xl flex gap-2 p-3 border bg-gray-100/50 dark:bg-white/10 rounded-xl">
                                <component :is="SquareCheckBig" class="h-6 w-6 text-sky-400" />
                                <span
                                    class="text-transparent bg-clip-text bg-gradient-to-r to-emerald-600 from-sky-400">
                                    Conteúdo da página
                                </span>
                            </h2>

                            <!-- Campo título + ícone -->
                            <div>
                                <label class="block text-sm font-medium mb-1">Título e ícone</label>
                                <div class="flex items-center gap-3">
                                    <!-- Preview do ícone -->
                                    <component v-if="page.icon" :is="LucideIcons[page.icon]"
                                        class="h-6 w-6 text-sky-500 cursor-pointer hover:scale-110 transition"
                                        @click="showIconPicker = !showIconPicker" />

                                    <!-- Input título -->
                                    <input v-model="page.title" type="text" placeholder="Título da página"
                                        class="flex-1 border rounded-xl px-3 py-2 w-2" />

                                    <span @click="page.is_active = !page.is_active"
                                        class="cursor-pointer inline-flex items-center text-sm font-medium rounded-xl px-2 py-2"
                                        :class="page.is_active
                                            ? 'bg-emerald-100 text-emerald-700'
                                            : 'bg-gray-200 text-gray-500'">
                                        <component :is="page.is_active ? Eye : EyeOff" class="w-6 h-6" />
                                    </span>
                                </div>

                                <!-- Dropdown de ícones -->
                                <div v-if="showIconPicker"
                                    class="mt-2 grid md:grid-cols-6 grid-cols-3 gap-2 border p-3 rounded-xl bg-white dark:bg-white/10 shadow-lg">
                                    <button v-for="name in iconOptions" :key="name" type="button"
                                        @click="selectIcon(name.name)"
                                        class="cursor-pointer flex flex-col items-center justify-center p-2 hover:bg-gray-100 dark:hover:bg-white/10 rounded-xl transition">
                                        <component :is="LucideIcons[name.name]"
                                            class="h-6 w-6 text-gray-700 dark:text-gray-100" />
                                        <span class="text-[10px] text-gray-500 dark:text-gray-200 mt-1">{{ name.label
                                            }}</span>
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Tipo</label>
                                <select v-model="page.type" class="w-full border rounded-xl px-3 py-2 dark:bg-black">
                                    <option value="simple">Simples</option>
                                    <option value="products">Produtos</option>
                                    <option value="gallery">Galeria</option>
                                    <option value="reviews">Avaliações</option>
                                    <option value="links">Links</option>
                                </select>
                            </div>

                            <!-- Condicional do conteúdo -->
                            <div>
                                <label class="block text-sm font-medium mb-1">Conteúdo</label>

                                <SimplesContent :page="page" />

                                <!-- Tipo: Links -->
                                <LinkContent :page="page" :links="links" :LucideIcons="LucideIcons"
                                    :removeLink="removeLink" :iconLinksOptions="iconLinksOptions" :addLink="addLink" />

                                <!-- Tipo: Galeria -->
                                <GaleryContent :page="page" :removeGalleryImage="removeGalleryImage"
                                    :LucideIcons="LucideIcons" :onGallerySelected="onGallerySelected" />

                                <!-- Tipo: Avaliações -->
                                <ReviewContent :page="page" :avaliacoes="avaliacoes" :Star="Star" :StarOff="StarOff"
                                    :updateStatus="updateStatus" />

                                <!-- Tipo: Produtos -->
                                <ProductContent :page="page" :categorias="categorias" :LucideIcons="LucideIcons"
                                    :removerCategoria="removerCategoria" :nomeCategoria="nomeCategoria"
                                    :produtos="produtos" :formatCurrency="formatCurrency" :novaCategoria="novaCategoria"
                                    :salvarCategoria="salvarCategoria" :novoProduto="novoProduto"
                                    :salvarProduto="salvarProduto" v-model:showAddCategory="showAddCategory"
                                    v-model:categoriaSelecionada="categoriaSelecionada"
                                    v-model:showAddProduct="showAddProduct" />
                            </div>
                        </div>


                        <!-- Tab SEO -->
                        <div class="md:border p-4 rounded-xl space-y-4 md:shadow">
                            <h2 class="text-2xl font-extrabold leading-none 
                            tracking-tight md:text-2xl flex gap-2 p-3 border bg-gray-100/50 dark:bg-white/10 rounded-xl">
                                <component :is="SquareCheckBig" class="h-6 w-6 text-sky-400" />
                                <span
                                    class="text-transparent bg-clip-text bg-gradient-to-r to-emerald-600 from-sky-400">
                                    SEO
                                </span>
                            </h2>
                            <div>
                                <label class="block text-sm font-medium mb-1">Título SEO</label>
                                <input v-model="page.seo_title" type="text"
                                    class="w-full border rounded-xl px-3 py-2" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Descrição SEO</label>
                                <textarea v-model="page.seo_description"
                                    class="w-full border rounded-xl px-3 py-2 h-20"></textarea>
                            </div>

                            <div class="w-full">
                                <label class="block text-sm font-medium mb-1">Imagem de Capa</label>
                                <label for="dropzone-file" class="flex flex-col items-center 
                                    justify-center w-full h-64 border-2 border-dashed 
                                    border-gray-300 rounded-xl cursor-pointer bg-gray-50 
                                    hover:bg-gray-100 dark:bg-black dark:border-white/20 
                                    dark:hover:border-white/30 dark:hover:bg-white/10 transition">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center">
                                        <UploadCloud class="w-10 h-10 mb-4 text-gray-500 dark:text-gray-400" />
                                        <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                                            <span class="font-semibold">Clique para enviar</span> ou arraste e solte
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            SVG, PNG, JPG ou GIF (MAX. 800x400px)
                                        </p>
                                    </div>

                                    <input id="dropzone-file" type="file" class="hidden" @change="onCoverSelected" />
                                </label>
                            </div>

                            <div v-if="coverPreview" class="mt-4 flex justify-center">
                                <img :src="coverPreview" alt="Prévia da capa"
                                    class="max-h-48 rounded-xl shadow border object-cover" />
                            </div>
                        </div>

                        <button type="submit"
                            class="mx-4 md:mx-0 px-4 py-2 bg-primary dark:text-gray-800 text-white rounded-xl hover:bg-primary/80">
                            Salvar Alterações
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </AppLayout>
</template>

<style scoped>
li:active {
    cursor: grabbing;
}


</style>
