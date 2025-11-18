<script setup lang="ts">
defineProps<{
    links: any;
    LucideIcons: typeof LucideIcons;
    removeLink: (index: number) => void;
    iconLinksOptions: { label: string; value: string; }[];
    addLink: () => void;
    page: {};
}>()
</script>

<template>
    <div v-if="page.type === 'links'" class="space-y-4">
        <p class="text-gray-600 text-sm mb-2">
            Aqui você pode configurar os <b>links rápidos</b> que aparecem na sua página
            pública.
            É possível escolher um ícone, definir a URL e personalizar se o texto será
            exibido ou não.
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <div v-for="(link, index) in links" :key="index"
                class="border border-gray-200 dark:border-white/10 dark:bg-white/10 rounded-xl p-4 shadow-sm hover:shadow-md transition">
                <!-- Cabeçalho -->
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                        <component :is="LucideIcons[link.icon]" class="w-5 h-5 text-gray-600" />
                        Link #{{ index + 1 }}
                    </h3>
                    <button @click="removeLink(index)" class="text-red-500 hover:text-red-700 text-sm">
                        Remover
                    </button>
                </div>

                <!-- Selecionar Ícone -->
                <div class="mb-2">
                    <label class="block text-sm font-medium mb-1">Ícone</label>
                    <select v-model="link.icon" class="border rounded-xl px-3 py-2 w-full">
                        <option v-for="option in iconLinksOptions" :key="option.value" :value="option.value">
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
                    <input v-model="link.text" type="text" placeholder="Nome do link (opcional)"
                        class="border rounded-xl px-3 py-2 w-full" />
                </div>

                <!-- Mostrar texto -->
                <div class="flex items-center gap-2">
                    <input type="checkbox" v-model="link.showText" class="rounded border-gray-300" />
                    <label class="text-sm text-gray-700">Mostrar texto junto ao
                        ícone</label>
                </div>
            </div>
        </div>

        <!-- Botão adicionar -->
        <button @click="addLink" class="bg-blue-600 text-white rounded-xl px-4 py-2 hover:bg-blue-700 transition">
            + Adicionar link
        </button>
    </div>
</template>
