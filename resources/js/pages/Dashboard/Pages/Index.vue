<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { GripVertical } from 'lucide-vue-next';
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css';

// Exemplo de páginas carregadas do backend
const pages = reactive([
    { id: 1, key: 'principal', title: 'Principal', icon: '', content: 'aaaaa', cover_image: '', seo_title: '', seo_description: '', is_active: true, type: 'simple', order: 1 },
    { id: 2, key: 'catalogo', title: 'Catálogo', icon: '', content: 'bbbb', cover_image: '', seo_title: '', seo_description: '', is_active: true, type: 'products', order: 2 },
    { id: 3, key: 'galeria', title: 'Galeria', icon: '', content: 'cccc', cover_image: '', seo_title: '', seo_description: '', is_active: false, type: 'simple', order: 3 },
    { id: 4, key: 'links', title: 'Links', icon: '', content: '', cover_image: '', seo_title: '', seo_description: '', is_active: false, type: 'links', order: 4 },
    { id: 5, key: 'sobre', title: 'Sobre', icon: '', content: '', cover_image: '', seo_title: '', seo_description: '', is_active: false, type: 'simple', order: 5 },
    { id: 6, key: 'avaliacoes', title: 'Avaliações', icon: '', content: '', cover_image: '', seo_title: '', seo_description: '', is_active: true, type: 'reviews', order: 6 },
]);

// Página selecionada
const selectedPage = ref(pages[0]);

// Tabs
const activeTab = ref('geral');

// Cover image preview
const coverPreview = ref(selectedPage.value.cover_image);

// Drag-and-drop
const dragIndex = ref<number | null>(null);

function onDragStart(index: number) {
    dragIndex.value = index;
}
function onDrop(index: number) {
    if (dragIndex.value === null) return;
    const movedItem = pages.splice(dragIndex.value, 1)[0];
    pages.splice(index, 0, movedItem);
    dragIndex.value = null;
    pages.forEach((p, i) => (p.order = i + 1));
}

// Cover image selection
function onCoverSelected(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (!file) return;
    const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/bmp', 'image/webp'];
    if (!validTypes.includes(file.type)) {
        alert('Formato inválido. Use JPG, PNG, BMP ou WEBP.');
        return;
    }
    if (file.size > 1024 * 1024) {
        alert('Tamanho máximo 1MB.');
        return;
    }
    const reader = new FileReader();
    reader.onload = (e) => {
        coverPreview.value = e.target?.result as string;
        selectedPage.value.cover_image = coverPreview.value;
    };
    reader.readAsDataURL(file);
}

const breadcrumbs = [
    { title: 'Início', href: '#' },
    { title: 'Páginas', href: '#' },
];
</script>

<template>

    <Head title="Páginas" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full gap-6 p-4">

            <!-- Menu lateral -->
            <aside class="w-60 border-r p-4 bg-gray-50 rounded-lg">
                <h2 class="text-lg font-bold mb-4">Páginas</h2>
                <ul class="space-y-2">
                    <li v-for="(page, index) in pages" :key="page.id" draggable="true" @dragstart="onDragStart(index)"
                        @dragover.prevent @drop="onDrop(index)" @click="selectedPage = page" :class="[
                            'flex items-center justify-between px-3 py-2 rounded cursor-grab hover:bg-gray-200',
                            selectedPage.id === page.id
                                ? 'bg-blue-100 font-semibold'
                                : page.is_active
                                    ? 'bg-white'
                                    : 'bg-gray-100 text-gray-400'
                        ]">
                        <div class="flex items-center space-x-2">
                            <GripVertical class="w-4 h-4 text-gray-400 cursor-grab" />
                            <span>{{ page.title }}</span>
                        </div>
                        <span v-if="!page.is_active" class="text-xs italic">(Inativa)</span>
                    </li>
                </ul>
            </aside>

            <!-- Formulário de edição -->
            <main v-if="selectedPage" class="flex-1 bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-bold mb-4">Editando: {{ selectedPage.title }}</h2>

                <!-- Tabs -->
                <div class="mb-4 border-b">
                    <nav class="-mb-px flex space-x-4">
                        <button @click="activeTab = 'geral'"
                            :class="activeTab === 'geral' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500'"
                            class="py-2 px-1 text-sm font-medium">Geral</button>
                        <button @click="activeTab = 'conteudo'"
                            :class="activeTab === 'conteudo' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500'"
                            class="py-2 px-1 text-sm font-medium">Conteúdo</button>
                        <button @click="activeTab = 'seo'"
                            :class="activeTab === 'seo' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500'"
                            class="py-2 px-1 text-sm font-medium">SEO</button>
                    </nav>
                </div>

                <form class="space-y-4">
                    <!-- Tab Geral -->
                    <div v-if="activeTab === 'geral'" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Título</label>
                            <input v-model="selectedPage.title" type="text" class="w-full border rounded px-3 py-2" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Ícone</label>
                            <input v-model="selectedPage.icon" type="text" class="w-full border rounded px-3 py-2" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Imagem de Capa</label>
                            <input type="file" accept=".jpg,.jpeg,.png,.bmp,.webp" @change="onCoverSelected" />
                            <div v-if="coverPreview" class="mt-2">
                                <img :src="coverPreview" class="w-24 h-24 object-cover border rounded" />
                            </div>
                        </div>

                        <div>
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" v-model="selectedPage.is_active" />
                                <span>Ativa</span>
                            </label>
                        </div>
                    </div>

                    <!-- Tab Conteúdo -->
                    <div v-if="activeTab === 'conteudo'" >
                        <label class="block text-sm font-medium mb-1">Conteúdo</label>
                        <QuillEditor 
                            class="border rounded px-3 py-2"
                            style="min-height: 250px">asdasda</QuillEditor>
                    </div>

                    <input v-model="selectedPage.title"  />

                    {{ selectedPage.content }}


                    <!-- Tab SEO -->
                    <div v-if="activeTab === 'seo'" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Título SEO</label>
                            <input v-model="selectedPage.seo_title" type="text"
                                class="w-full border rounded px-3 py-2" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Descrição SEO</label>
                            <textarea v-model="selectedPage.seo_description"
                                class="w-full border rounded px-3 py-2 h-20"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Tipo</label>
                            <select v-model="selectedPage.type" class="w-full border rounded px-3 py-2">
                                <option value="products">Produtos</option>
                                <option value="reviews">Avaliações</option>
                                <option value="links">Links</option>
                                <option value="simple">Simples</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded hover:bg-primary/80">
                        Salvar Alterações
                    </button>
                </form>
            </main>
        </div>
    </AppLayout>
</template>

<style scoped>
li:active {
    cursor: grabbing;
}
</style>
