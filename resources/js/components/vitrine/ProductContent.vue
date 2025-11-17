<script setup lang="ts">
const props = defineProps<{
    categorias: {};
    LucideIcons: typeof LucideIcons;
    removerCategoria: (categoria: any) => void;
    nomeCategoria: (id: number) => any;
    produtos: {};
    formatCurrency: (value: Number | String) => string;
    novaCategoria: string;
    salvarCategoria: () => void;
    novoProduto: { name: string; price: string; description: string; category_id: null; };
    salvarProduto: () => void;
    page: {};
}>()
const showAddCategory = defineModel<boolean>('showAddCategory', { required: true })
const categoriaSelecionada = defineModel<number | null>('categoriaSelecionada', { required: true })
const showAddProduct = defineModel<boolean>('showAddProduct', { required: true })

import { ref, watch } from 'vue'

// cria uma cópia local
const novaCategoriaLocal = ref(props.novaCategoria)

// atualiza se mudar no pai
watch(() => props.novaCategoria, val => {
  novaCategoriaLocal.value = val
})
</script>

<template>
    <div v-if="page.type === 'products'" class="space-y-4">
        <p class="text-gray-600 text-sm mb-2">
            Os <b>produtos</b> são carregados automaticamente a partir do seu catálogo
            cadastrado no sistema. Cada produto pertence a uma <b>categoria</b>, e todas as
            informações são obtidas dinamicamente.
            Aqui você pode <b>adicionar, editar ou remover</b> categorias e produtos,
            mantendo seu catálogo sempre atualizado e organizado.
        </p>

        <!-- Nenhuma categoria -->
        <div v-if="categorias.length === 0" class="text-gray-500 text-sm">
            Nenhuma categoria encontrada. Adicione uma para começar.
        </div>

        <!-- Tabs horizontais de categorias -->
        <div v-else class="overflow-x-auto pb-2">
            <div class="flex gap-2 min-w-max">
                <button @click="showAddCategory = true"
                    class="flex items-center gap-1 bg-emerald-500 text-white px-3 py-1 rounded-full text-sm font-medium hover:bg-emerald-600">
                    <LucideIcons.PlusCircle class="w-4 h-4" />
                    Nova
                </button>

                <button v-for="categoria in [...categorias].reverse()" :key="categoria.id"
                    @click="categoriaSelecionada = categoria.id"
                    class="flex items-center gap-2 whitespace-nowrap px-3 py-1 rounded-full text-sm font-medium" :class="categoriaSelecionada === categoria.id
                        ? 'bg-sky-500 text-white'
                        : 'bg-gray-200 text-gray-700 hover:bg-gray-300'">
                    {{ categoria.name }}
                    <LucideIcons.Trash2 class="w-4 h-4 text-red-500 hover:text-red-600"
                        @click.stop="removerCategoria(categoria)" />
                </button>
            </div>
        </div>

        <!-- Produtos da categoria selecionada -->
        <div v-if="categoriaSelecionada" class="mt-4">
            <div class="flex justify-between items-center mb-2">
                <h3 class="text-lg font-semibold">
                    {{ nomeCategoria(categoriaSelecionada) }}
                </h3>
                <button @click="showAddProduct = true"
                    class="flex items-center gap-1 bg-sky-500 text-white px-3 py-1 rounded-full text-sm hover:bg-sky-600">
                    <LucideIcons.Plus class="w-4 h-4" />
                    Adicionar
                </button>
            </div>

            <div v-for="produto in produtos.filter(p => p.category_id === categoriaSelecionada)" :key="produto.id"
                class="flex items-center justify-between bg-white rounded-lg shadow-sm border p-3 mb-2">
                <div class="flex items-center gap-3">
                    <img v-if="produto.cover_image" :src="produto.cover_image"
                        class="w-12 h-12 rounded-lg object-cover border" />
                    <div>
                        <p class="font-medium text-gray-800">{{ produto.name }}</p>
                        <p class="text-gray-500 text-sm">{{ produto.description }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="font-bold text-emerald-600">{{ formatCurrency(produto.price) }}</p>
                    <p class="text-xs text-gray-400">Estoque: {{ produto.stock }}</p>
                </div>
            </div>

            <div v-if="produtos.filter(p => p.category_id === categoriaSelecionada).length === 0"
                class="text-gray-400 text-sm italic">
                Nenhum produto nesta categoria.
            </div>
        </div>

        <!-- Modal: Adicionar Categoria -->
        <div v-if="showAddCategory" class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
            <div class="bg-white w-11/12 max-w-sm rounded-xl shadow-lg p-4">
                <h3 class="text-lg font-semibold mb-3 text-gray-700">Nova Categoria</h3>
                <input v-model="novaCategoriaLocal" placeholder="Nome da categoria"
                    class="w-full border rounded-lg px-3 py-2 text-sm mb-3" />
                <div class="flex justify-end gap-2">
                    <button @click="showAddCategory = false" class="px-3 py-1 text-gray-500 hover:text-gray-700">
                        Cancelar
                    </button>
                    <button @click="salvarCategoria"
                        class="bg-emerald-500 text-white px-4 py-1 rounded-lg hover:bg-emerald-600">
                        Salvar
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal: Adicionar Produto -->
        <div v-if="showAddProduct" class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
            <div class="bg-white w-11/12 max-w-sm rounded-xl shadow-lg p-4">
                <h3 class="text-lg font-semibold mb-3 text-gray-700">Novo Produto</h3>
                <input v-model="novoProduto.name" placeholder="Nome"
                    class="w-full border rounded-lg px-3 py-2 text-sm mb-2" />
                <input v-model="novoProduto.price" placeholder="Preço"
                    class="w-full border rounded-lg px-3 py-2 text-sm mb-2" />
                <textarea v-model="novoProduto.description" placeholder="Descrição"
                    class="w-full border rounded-lg px-3 py-2 text-sm mb-3"></textarea>
                <div class="flex justify-end gap-2">
                    <button @click="showAddProduct = false" class="px-3 py-1 text-gray-500 hover:text-gray-700">
                        Cancelar
                    </button>
                    <button @click="salvarProduto" class="bg-sky-500 text-white px-4 py-1 rounded-lg hover:bg-sky-600">
                        Salvar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
