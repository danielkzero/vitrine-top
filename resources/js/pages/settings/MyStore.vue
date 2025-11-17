<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import SettingsLayout from '@/layouts/settings/Layout.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { edit } from '@/routes/store';

import { reactive } from 'vue'
import {
    Store,
    Palette,
    Image as ImageIcon,
    Phone,
    MessageCircle,
    Instagram,
    Facebook,
    Upload,
    Save,
} from 'lucide-vue-next'

import { usePage } from '@inertiajs/vue3'

const page = usePage()
const user = page.props.auth.user

const store = reactive({
    business_name: user.business_name ?? "",
    slug: user.slug ?? "",
    description: user.description ?? "",
    logo_url: user.logo_url ?? "",
    banner_url: user.banner_url ?? "",
    theme_color: user.theme_color ?? "#6366f1",
    phone: user.phone_primary ?? "",
    whatsapp: user.whatsapp ?? "",
    instagram: user.instagram ?? "",
    facebook: user.facebook ?? ""
})

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Configurações da Loja',
        href: edit().url,
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Minha Loja" />

        <SettingsLayout>
            <div class="space-y-8">
                <HeadingSmall title="Configurações da Loja"
                    description="Gerencie sua vitrine, aparência e informações públicas" />

                <!-- Seção Básica -->
                <div class="space-y-6">
                    <h2 class="text-lg font-medium flex items-center gap-2">
                        <Store class="w-5 h-5" />
                        Informações Básicas
                    </h2>

                    <div class="grid gap-4">
                        <div class="grid gap-2">
                            <Label>Nome da loja *</Label>
                            <Input v-model="store.business_name" placeholder="Minha Loja" />
                        </div>

                        <div class="grid gap-2">
                            <Label>Slug *</Label>
                            <div class="flex items-center">vitrine.top/<Input v-model="store.slug" placeholder="minha-loja" /></div>
                        </div>

                        <div class="grid gap-2">
                            <Label>Descrição</Label>
                            <Textarea v-model="store.description" :rows="4" />
                        </div>
                    </div>
                </div>

                <!-- Seção de Design -->
                <div class="space-y-6">
                    <h2 class="text-lg font-medium flex items-center gap-2">
                        <Palette class="w-5 h-5" />
                        Design e Aparência
                    </h2>

                    <div class="space-y-6">
                        <!-- Logo -->
                        <div class="grid gap-2">
                            <Label>Logo da Loja</Label>
                            <div class="flex items-center gap-4 mt-2">
                                <img v-if="store.logo_url" :src="store.logo_url"
                                    class="w-20 h-20 rounded-lg border object-cover" />

                                <div>
                                    <input id="logo-upload" type="file" class="hidden" />
                                    <Button variant="outline">
                                        <Upload class="w-4 h-4 mr-2" />
                                        Enviar Logo
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <!-- Banner -->
                        <div class="grid gap-2">
                            <Label>Banner</Label>
                            <div class="mt-2">
                                <img v-if="store.banner_url" :src="store.banner_url"
                                    class="w-full h-40 rounded-lg border object-cover" />

                                <input id="banner-upload" type="file" class="hidden" />

                                <Button variant="outline" class="mt-2">
                                    <Upload class="w-4 h-4 mr-2" />
                                    Enviar Banner
                                </Button>
                            </div>
                        </div>

                        <!-- Cor -->
                        <div class="grid gap-2">
                            <Label>Cor Principal</Label>
                            <div class="flex gap-4 mt-2">
                                <input type="color" v-model="store.theme_color" class="w-16 h-10 rounded" />
                                <Input v-model="store.theme_color" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seção de Contato -->
                <div class="space-y-6">
                    <h2 class="text-lg font-medium flex items-center gap-2">
                        <Phone class="w-5 h-5" />
                        Contato
                    </h2>

                    <div class="grid gap-4">
                        <div class="grid gap-2">
                            <Label>Telefone</Label>
                            <Input v-model="store.phone" placeholder="(11) 99999-0000" />
                        </div>

                        <div class="grid gap-2">
                            <Label class="flex items-center gap-2">
                                <MessageCircle class="w-4 h-4" />
                                WhatsApp
                            </Label>
                            <Input v-model="store.whatsapp" placeholder="5511999990000" />
                        </div>

                        <div class="grid gap-2">
                            <Label class="flex items-center gap-2">
                                <Instagram class="w-4 h-4" />
                                Instagram
                            </Label>
                            <Input v-model="store.instagram" placeholder="@minhaloja" />
                        </div>

                        <div class="grid gap-2">
                            <Label class="flex items-center gap-2">
                                <Facebook class="w-4 h-4" />
                                Facebook
                            </Label>
                            <Input v-model="store.facebook" placeholder="facebook.com/minhaloja" />
                        </div>
                    </div>
                </div>

                <!-- Botão Salvar -->
                <div class="flex justify-end">
                    <Button class="flex items-center gap-2">
                        <Save class="w-4 h-4" />
                        Salvar Configurações
                    </Button>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>