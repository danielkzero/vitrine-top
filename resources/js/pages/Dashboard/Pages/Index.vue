<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { QuillEditor } from '@vueup/vue-quill'
import { SquareCheckBig, UploadCloud, Star, StarOff } from 'lucide-vue-next';
import * as LucideIcons from 'lucide-vue-next'
import '@vueup/vue-quill/dist/vue-quill.snow.css'

// Props do Inertia
const inertiaPage = usePage()
const inertiaPages = inertiaPage.props.pages
const avaliacoes = inertiaPage.props.avaliacoes

// Cópia local (reativa e mutável)
const pageList = ref([...inertiaPages.data || inertiaPages])

// ID da página selecionada
const selectedPageId = ref(pageList.value[0]?.id ?? null)

// Página completa selecionada
const selectedPage = computed(() =>
    pageList.value.find(p => p.id === selectedPageId.value)
)

const activeTab = ref('geral')
const coverPreview = ref(selectedPage.value?.cover_image ?? null)

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
        if (selectedPage.value) selectedPage.value.cover_image = coverPreview.value
    }
    reader.readAsDataURL(file)
}

const breadcrumbs = [
    { title: 'Início', href: '#' },
    { title: 'Páginas', href: '#' },
]

const iconOptions = [
    { name: 'EyeOff', label: 'Ocultar' },
    { name: 'Home', label: 'Início' },
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


// Lista de ícones disponíveis
const iconLinksOptions = [
    { label: 'Link padrão', value: 'Link' },
    { label: 'Facebook', value: 'Facebook' },
    { label: 'Instagram', value: 'Instagram' },
    { label: 'Twitter', value: 'Twitter' },
    { label: 'YouTube', value: 'Youtube' },
    { label: 'LinkedIn', value: 'Linkedin' },
];

const links = ref([
    {
        icon: 'Link',
        url: '',
        text: '',
        showText: true,
    },
])

// Controle do dropdown
const showIconPicker = ref(false)

// Alternar ícone
function selectIcon(name: string) {
    selectedPage.value.icon = name
    showIconPicker.value = false
}

function addLink() {
    links.value.push({
        icon: 'Link',
        url: '',
        text: '',
        showText: true,
    })
}

function removeLink(index: number) {
    links.value.splice(index, 1)
}

function handleSubmit() {

}


// Função para upload múltiplo
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
            // Garante reatividade mesmo que content seja string no banco
            if (selectedPage.value) {
                if (!Array.isArray(selectedPage.value.content)) {
                    selectedPage.value.content = []
                }
                selectedPage.value.content.push(e.target?.result as string)
            }
        }
        reader.readAsDataURL(file)
    })
}

// Remover imagem
function removeGalleryImage(index: number) {
    if (selectedPage.value?.content && Array.isArray(selectedPage.value.content)) {
        selectedPage.value.content.splice(index, 1)
    }
}
</script>


<template>

    <Head title="Gerenciamento de páginas" />
    <AppLayout :breadcrumbs="breadcrumbs">

        <!-- Formulário de edição -->
        <main class="flex-1 bg-gray-100 md:bg-gray-50/50 py-4 px-1 md:py-6 md:px-6 shadow">
            <div class="container mx-auto">
                <!-- Seletor de páginas -->
                <div class="mb-4 text-xl p-3 bg-white rounded-xl border shadow">
                    <label class="block text-sm font-medium mb-1">Selecionar página</label>
                    <select v-model="selectedPageId" class="w-full border rounded-xl px-3 py-2 bg-white">
                        <option v-for="page in pageList" :key="page.id" :value="page.id">
                            {{ page.title }} <span v-if="!page.is_active">(Inativa)</span>
                        </option>
                    </select>
                </div>

                <div v-if="selectedPage">
                    <form class="space-y-4" @submit.prevent="handleSubmit">
                        <!-- Tab Geral -->
                        <div class="border p-4 rounded-xl space-y-4 bg-white shadow">
                            <h2 class="text-2xl font-extrabold leading-none 
                            tracking-tight md:text-2xl flex gap-2 p-3 border bg-gray-100/50 rounded-xl">
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
                                    <component v-if="selectedPage.icon" :is="LucideIcons[selectedPage.icon]"
                                        class="h-6 w-6 text-sky-500 cursor-pointer hover:scale-110 transition"
                                        @click="showIconPicker = !showIconPicker" />

                                    <!-- Input título -->
                                    <input v-model="selectedPage.title" type="text" placeholder="Título da página"
                                        class="flex-1 border rounded-xl px-3 py-2" />

                                    <div>
                                        <label class="flex items-center space-x-2">
                                            <input type="checkbox" v-model="selectedPage.is_active" />
                                            <span>Ativa</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Dropdown de ícones -->
                                <div v-if="showIconPicker"
                                    class="mt-2 grid md:grid-cols-6 grid-cols-3 gap-2 border p-3 rounded-xl bg-white shadow-lg">
                                    <button v-for="name in iconOptions" :key="name" type="button"
                                        @click="selectIcon(name.name)"
                                        class="flex flex-col items-center justify-center p-2 hover:bg-gray-100 rounded-xl transition">
                                        <component :is="LucideIcons[name.name]" class="h-6 w-6 text-gray-700" />
                                        <span class="text-[10px] text-gray-500 mt-1">{{ name.label }}</span>
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Tipo</label>
                                <select v-model="selectedPage.type" class="w-full border rounded-xl px-3 py-2">
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

                                <!-- Tipo: Simples (texto com Quill) -->
                                <div v-if="selectedPage.type === 'simple'">
                                    <QuillEditor class="border px-3 py-2" v-model:content="selectedPage.content"
                                        content-type="html" />
                                </div>

                                <!-- Tipo: Links -->
                                <div v-else-if="selectedPage.type === 'links'" class="space-y-4">
                                    <p class="text-gray-600 text-sm mb-2">
                                        Aqui você pode configurar os <b>links rápidos</b> que aparecem na sua página
                                        pública.
                                        É possível escolher um ícone, definir a URL e personalizar se o texto será
                                        exibido ou não.
                                    </p>
                                    <div
                                        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                        <div v-for="(link, index) in links" :key="index"
                                            class="border border-gray-200 bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition">
                                            <!-- Cabeçalho -->
                                            <div class="flex items-center justify-between mb-3">
                                                <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                                                    <component :is="LucideIcons[link.icon]"
                                                        class="w-5 h-5 text-gray-600" />
                                                    Link #{{ index + 1 }}
                                                </h3>
                                                <button @click="removeLink(index)"
                                                    class="text-red-500 hover:text-red-700 text-sm">
                                                    Remover
                                                </button>
                                            </div>

                                            <!-- Selecionar Ícone -->
                                            <div class="mb-2">
                                                <label class="block text-sm font-medium mb-1">Ícone</label>
                                                <select v-model="link.icon" class="border rounded-xl px-3 py-2 w-full">
                                                    <option v-for="option in iconLinksOptions" :key="option.value"
                                                        :value="option.value">
                                                        {{ option.label }}
                                                    </option>
                                                </select>
                                            </div>

                                            <!-- URL -->
                                            <div class="mb-2">
                                                <label class="block text-sm font-medium mb-1">URL</label>
                                                <input v-model="link.url" type="text" placeholder="https://..."
                                                    class="border rounded-xl px-3 py-2 w-full" />
                                            </div>

                                            <!-- Texto -->
                                            <div class="mb-2">
                                                <label class="block text-sm font-medium mb-1">Texto</label>
                                                <input v-model="link.text" type="text"
                                                    placeholder="Nome do link (opcional)"
                                                    class="border rounded-xl px-3 py-2 w-full" />
                                            </div>

                                            <!-- Mostrar texto -->
                                            <div class="flex items-center gap-2">
                                                <input type="checkbox" v-model="link.showText"
                                                    class="rounded border-gray-300" />
                                                <label class="text-sm text-gray-700">Mostrar texto junto ao
                                                    ícone</label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Botão adicionar -->
                                    <button @click="addLink"
                                        class="bg-blue-600 text-white rounded-xl px-4 py-2 hover:bg-blue-700 transition">
                                        + Adicionar link
                                    </button>
                                </div>

                                <!-- Tipo: Galeria -->
                                <div v-else-if="selectedPage.type === 'gallery'" class="space-y-4">
                                    <p class="text-gray-600 text-sm mb-2">
                                        Aqui você pode gerenciar as <b>fotos da galeria</b> da página.
                                        Você pode enviar novas imagens, visualizar as existentes e remover as que não
                                        desejar mais exibir.
                                    </p>

                                    <!-- Lista de imagens -->
                                    <div v-if="Array.isArray(selectedPage.content) && selectedPage.content.length"
                                        class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                                        <div v-for="(image, index) in selectedPage.content" :key="index"
                                            class="relative group border rounded-xl overflow-hidden shadow hover:shadow-md transition">
                                            <img :src="image" alt="Imagem da galeria"
                                                class="object-cover w-full h-32" />
                                            <button type="button" @click="removeGalleryImage(index)"
                                                class="absolute top-1 right-1 bg-red-600 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition">
                                                <component :is="LucideIcons.Trash" class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Dropzone para adicionar novas imagens -->
                                    <label for="gallery-dropzone" class="flex flex-col items-center justify-center 
                                    w-full h-64 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer 
                                    bg-gray-50 hover:bg-gray-100 transition">
                                        <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center">
                                            <UploadCloud class="w-10 h-10 mb-4 text-gray-500" />
                                            <p class="mb-2 text-sm text-gray-500">
                                                <span class="font-semibold">Clique para enviar</span> ou arraste e solte
                                                imagens
                                            </p>
                                            <p class="text-xs text-gray-400">
                                                JPG, PNG, WEBP (MAX. 1MB por imagem)
                                            </p>
                                        </div>
                                        <input id="gallery-dropzone" type="file" class="hidden" accept="image/*"
                                            multiple @change="onGallerySelected" />
                                    </label>
                                </div>



                                <!-- Tipo: Avaliações -->
                                <div v-else-if="selectedPage.type === 'reviews' && avaliacoes" class="space-y-4">
                                    <p class="text-gray-600 text-sm mb-2">
                                        As <b>avaliações</b> são geradas automaticamente a partir dos comentários
                                        deixados por clientes em suas páginas.
                                        Elas são carregadas diretamente do sistema por meio de um controlador.
                                        Aqui você pode visualizar, aprovar ou rejeitar cada avaliação conforme desejar.
                                    </p>

                                    <div
                                        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                        <div v-for="item in avaliacoes" :key="item.id" class="border border-gray-200  rounded-xl 
                                            p-4 shadow-sm hover:shadow-md transition flex flex-col 
                                            justify-between" :class="{
                                                'bg-yellow-100 text-yellow-700': item.status === 'pending',
                                                'bg-green-100 text-green-700': item.status === 'approved',
                                                'bg-red-100 text-red-700': item.status === 'rejected',
                                            }">
                                            <!-- Conteúdo principal -->
                                            <div>
                                                <!-- Cabeçalho: Nome + Data -->
                                                <div class="flex items-center justify-between mb-2">
                                                    <div class="font-semibold text-gray-800">{{ item.customer_name }}
                                                    </div>
                                                    <div class="text-xs text-gray-400">
                                                        {{ new Date(item.created_at).toLocaleDateString('pt-BR') }}
                                                    </div>
                                                </div>

                                                <!-- Estrelas -->
                                                <div class="flex items-center mb-2">
                                                    <component v-for="n in 5" :key="n"
                                                        :is="n <= item.rating ? Star : StarOff" class="w-5 h-5"
                                                        :class="n <= item.rating ? 'text-yellow-400' : 'text-gray-300'" />
                                                </div>

                                                <!-- Comentário -->
                                                <p class="text-gray-700 text-sm leading-relaxed mb-4">
                                                    {{ item.comment || 'Sem comentário.' }}
                                                </p>
                                            </div>

                                            <!-- Rodapé fixo: Status -->
                                            <div class="flex items-center gap-2 mt-auto pt-3 border-t border-gray-100">
                                                <label class="text-sm text-gray-600 font-medium">Status:</label>
                                                <select v-model="item.status" @change="updateStatus(item)"
                                                    class="border border-gray-300 rounded-xl px-3 py-1 text-sm bg-white">
                                                    <option value="pending">Pendente</option>
                                                    <option value="approved">Aprovado</option>
                                                    <option value="rejected">Rejeitado</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tipo: Produtos -->
                                <div v-else-if="selectedPage.type === 'products'">
                                    <p class="text-gray-600 text-sm">Os produtos vêm de
                                        <b>Dashboard/ProductController.php</b>.
                                    </p>
                                    <div class="border rounded-xl p-4 mt-2 bg-gray-50 text-gray-800">
                                        <p>Aqui você pode renderizar o componente de listagem de produtos.</p>
                                        <!-- Exemplo: <ProductList :products="products" /> -->
                                    </div>
                                </div>
                            </div>


                        </div>


                        <!-- Tab SEO -->
                        <div class="border p-4 rounded-xl space-y-4 bg-white shadow">
                            <h2 class="text-2xl font-extrabold leading-none 
                            tracking-tight md:text-2xl flex gap-2 p-3 border bg-gray-100/50 rounded-xl">
                                <component :is="SquareCheckBig" class="h-6 w-6 text-sky-400" />
                                <span
                                    class="text-transparent bg-clip-text bg-gradient-to-r to-emerald-600 from-sky-400">
                                    SEO
                                </span>
                            </h2>
                            <div>
                                <label class="block text-sm font-medium mb-1">Título SEO</label>
                                <input v-model="selectedPage.seo_title" type="text"
                                    class="w-full border rounded-xl px-3 py-2" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Descrição SEO</label>
                                <textarea v-model="selectedPage.seo_description"
                                    class="w-full border rounded-xl px-3 py-2 h-20"></textarea>
                            </div>

                            <div class="w-full">
                                <label class="block text-sm font-medium mb-1">Imagem de Capa</label>
                                <label for="dropzone-file" class="flex flex-col items-center 
                                    justify-center w-full h-64 border-2 border-dashed 
                                    border-gray-300 rounded-xl cursor-pointer bg-gray-50 
                                    hover:bg-gray-100 dark:bg-gray-700 dark:border-gray-600 
                                    dark:hover:border-gray-500 dark:hover:bg-gray-600 transition">
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

                        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-xl hover:bg-primary/80">
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

/* Conteúdo interno do editor (sem usar @apply) */
:deep(.ql-editor) {
    min-height: 15rem;
    /* equivalente a min-h-60 (15rem) */
    border-radius: 1rem;
    /* rounded-xl ~ 1rem */
    padding: 1rem;
    /* px-4 py-3 = 1rem 0.75rem, escolhi 1rem */
    background-color: #ffffff;
    /* bg-white */
    color: #1f2937;
    /* text-gray-800 */
    line-height: 1.6;
    /* leading-relaxed */
}

/* placeholder do editor */
:deep(.ql-editor.ql-blank::before) {
    color: #9ca3af;
    /* text-gray-400 */
}

/* toolbar e container */
:deep(.ql-toolbar) {
    background-color: #f9fafb;
    /* bg-gray-50 */
    border-bottom: 1px solid #e5e7eb;
    /* border-gray-200 */
    border-top-left-radius: 1rem;
    border-top-right-radius: 1rem;
}

:deep(.ql-container) {
    border-bottom-left-radius: 1rem;
    border-bottom-right-radius: 1rem;
    border: 1px solid #e5e7eb;
}
</style>
