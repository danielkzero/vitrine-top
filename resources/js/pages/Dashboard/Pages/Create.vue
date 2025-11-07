<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { routes } from '@/config/routes';

const form = useForm({
    title: '',
    icon: '',
    content: '',
    is_active: true,
    type: 'simple'
});

const breadcrumbs = [
    { title: 'Início', href: routes.painel.index },
    { title: 'Páginas', href: routes.painel.pages.index },
    { title: 'Nova Página', href: routes.painel.pages.create },
];
</script>

<template>

    <Head title="Nova Página" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 max-w-2xl mx-auto">
            <h1 class="text-2xl font-bold mb-4">Nova Página</h1>

            <form @submit.prevent="form.post(routes.painel.pages.store)">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Título</label>
                        <input v-model="form.title" class="w-full border rounded p-2" required />
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Ícone</label>
                        <input v-model="form.icon" class="w-full border rounded p-2" />
                    </div>

                    <select v-model="form.type" class="w-full border rounded p-2" required>
                        <option value="products">Produtos</option>
                        <option value="reviews">Avaliações</option>
                        <option value="links">Links</option>
                        <option value="simple">Simples</option>
                    </select>


                    <div>
                        <label class="block text-sm font-medium mb-1">Conteúdo</label>
                        <textarea v-model="form.content" class="w-full border rounded p-2 min-h-[150px]" />
                    </div>

                    <div class="flex items-center space-x-2">
                        <input type="checkbox" v-model="form.is_active" />
                        <span>Ativa</span>
                    </div>

                    <div class="flex justify-end mt-4">
                        <button type="submit" class="bg-primary text-white px-4 py-2 rounded hover:bg-primary/80"
                            :disabled="form.processing">
                            Salvar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
