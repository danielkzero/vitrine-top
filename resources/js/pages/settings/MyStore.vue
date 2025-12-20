<script setup lang="ts">
import { Head, usePage, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import SettingsLayout from '@/layouts/settings/Layout.vue'

import HeadingSmall from '@/components/HeadingSmall.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import DropzoneFile from '@/components/ui/dropzone-file/DropzoneFile.vue'

import { Store, Palette, Upload, Save } from 'lucide-vue-next'
import { reactive, ref } from 'vue'
import { edit } from '@/routes/store'
import { route } from 'ziggy-js'
import type { BreadcrumbItem } from '@/types'
import { getIcon } from '@/lib/iconMap'

const page = usePage()
const user = page.props.auth.user

// Dados principais (AGORA usando image_url, não base64)
const store = reactive({
    business_name: user.business_name ?? "",
    subtitle: user.subtitle ?? "",
    slug: user.slug ?? "",
    description: user.description ?? "",
    logo_path: user.logo_path ?? "",              // <--- URL da logo salva
    background_path: user.background_path ?? "",  // <--- URL da imagem salva
    theme_color: user.theme_color ?? "#6366f1",
    whatsapp: user.whatsapp ?? "",

    // arquivos reais
    logo_file: null as File | null,
    background_file: null as File | null,
})

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Configurações da Loja',
        href: edit().url,
    },
]

/* ===========================================================
   LOGO
=========================================================== */
async function handleLogoUpload(event: Event) {
    const target = event.target as HTMLInputElement
    const file = target.files?.[0]
    if (!file) return

    // preview
    store.logo_path = URL.createObjectURL(file)
    store.logo_file = file
}

/* ===========================================================
   BACKGROUND
=========================================================== */
async function handleBackground(files: any[]) {
    if (!files.length) return

    const f = files[0].file
    if (!f) return

    store.background_path = URL.createObjectURL(f)
    store.background_file = f
}

/* ===========================================================
   SALVAR CONFIGURAÇÕES (Agora com FormData)
=========================================================== */
function saveStore() {
    const form = new FormData()

    form.append("business_name", store.business_name)
    form.append("subtitle", store.subtitle)
    form.append("slug", store.slug)
    form.append("description", store.description)
    form.append("theme_color", store.theme_color)
    form.append("whatsapp", store.whatsapp)

    if (store.logo_file) {
        form.append("logo_file", store.logo_file)
    }

    if (store.background_file) {
        form.append("background_file", store.background_file)
    }

    form.append("_method", "PUT")

    router.post(route("store.update"), form, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            alert("Configurações atualizadas!")
            router.visit(route("painel.index"), { replace: true })
        }
    })
}

</script>


<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Minha Loja" />

        <SettingsLayout>
            <div class="space-y-12">

                <!-- HEADER -->
                <HeadingSmall title="Configurações da Loja"
                    description="Gerencie nome, slug, descrição, logo, imagem de fundo e cor da sua vitrine pública" />

                <!-- ===================== -->
                <!-- CARD: INFORMAÇÕES BÁSICAS -->
                <!-- ===================== -->
                <div
                    class="p-8 rounded-2xl border shadow-sm bg-slate-50/10 dark:bg-white/5 border-slate-200/10 dark:border-white/10">

                    <div class="flex items-center gap-3 mb-6">
                        <div
                            class="w-10 h-10 rounded-lg flex items-center justify-center bg-slate-50/10 dark:bg-white/5 text-emerald-600 dark:text-emerald-400">
                            <Store class="w-5 h-5" />
                        </div>
                        <h2 class="text-2xl font-bold">Informações Básicas</h2>
                    </div>

                    <div class="grid gap-6">
                        <div class="grid gap-2">
                            <Label>Nome da loja *</Label>
                            <Input v-model="store.business_name" placeholder="Minha Loja" />
                        </div>

                        <div class="grid gap-2">
                            <Label>Sub-título</Label>
                            <Input v-model="store.subtitle" placeholder="Bem-vindo à nossa vitrine" />
                        </div>

                        <div class="grid gap-2">
                            <Label>Slug *</Label>
                            <div class="flex items-center gap-1">
                                <span class="text-slate-500 dark:text-slate-400">vitrine.top/</span>
                                <Input v-model="store.slug" placeholder="minha-loja" />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label>Whatsapp</Label>
                            <div class="flex items-center gap-1">
                                <Input v-model="store.whatsapp" placeholder="+5511999999999" />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label>Sobre no rodapé</Label>
                            <Textarea v-model="store.description" :rows="4" placeholder="Descreva sua loja..." />
                        </div>
                    </div>
                </div>

                <!-- ===================== -->
                <!-- CARD: DESIGN E APARÊNCIA -->
                <!-- ===================== -->
                <div
                    class="p-8 rounded-2xl border shadow-sm bg-slate-50/10 dark:bg-white/5 border-slate-200/10 dark:border-white/10">

                    <div class="flex items-center gap-3 mb-6">
                        <div
                            class="w-10 h-10 rounded-lg flex items-center justify-center bg-slate-50/10 dark:bg-white/5 text-emerald-600 dark:text-emerald-400">
                            <Palette class="w-5 h-5" />
                        </div>
                        <h2 class="text-2xl font-bold">Design e Aparência</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-10">
                        <!-- BACKGROUND IMAGE -->
                        <div class="grid sm:col-span-2 gap-3">
                            <Label>Imagem de Fundo</Label>

                            <DropzoneFile :initial-files="[]" :multiple="false" :maxFiles="1"
                                :allowed-extensions="['jpg', 'jpeg', 'png', 'webp']"
                                title-file-types="Clique ou arraste uma imagem"
                                display-file-types="JPG, PNG, WEBP – Máx. 1MB" @onCoverSelected="handleBackground" />

                            <!-- imagem de fundo -->
                            <div class="pt-2">
                                <h2 class="text-lg font-semibold mb-3 dark:text-white">Imagem de fundo</h2>

                                <div v-if="store.background_path">
                                    <div
                                        class="relative rounded-xl overflow-hidden shadow border bg-white dark:bg-slate-800 dark:border-white/10 group">
                                        <img :src="store.background_path" class="w-full h-40 object-cover" />

                                        <button
                                            class="absolute top-2 right-2 p-2 rounded-full bg-black/50 text-white opacity-0 group-hover:opacity-100 transition">
                                            <component :is="getIcon('Trash')" class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>

                                <p v-else class="text-gray-400 dark:text-gray-500 text-sm">Nenhum banner enviado ainda.
                                </p>
                            </div>
                        </div>
                        <!-- LOGO -->
                        <div class="grid gap-3">
                            <Label>Logo da Loja</Label>

                            <div class="flex items-center gap-4">
                                <label for="logo-upload"
                                    class="group cursor-pointer relative w-24 h-24 rounded-full overflow-hidden border border-slate-200/10 dark:border-white/10 shadow-sm flex items-center justify-center bg-slate-50/10 dark:bg-white/5 hover:shadow-md transition">

                                    <img v-if="store.logo_path" :src="store.logo_path"
                                        class="absolute inset-0 w-full h-full object-cover" />

                                    <Store v-else
                                        class="w-8 h-8 text-slate-400 dark:text-white group-hover:text-emerald-600 transition" />

                                    <div
                                        class="absolute inset-0 bg-black/10 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                        <Upload class="w-6 h-6 text-white drop-shadow" />
                                    </div>
                                </label>

                                <input id="logo-upload" type="file" class="hidden" accept="image/*"
                                    @change="handleLogoUpload" />
                            </div>
                        </div>


                        <!-- COR PRINCIPAL -->
                        <div class="grid gap-3">
                            <Label>Cor Principal</Label>

                            <div class="flex items-center gap-4">
                                <label for="theme-color-input"
                                    class="group w-14 h-10 rounded-full border cursor-pointer flex items-center justify-center shadow-sm transition relative border-slate-200/10 dark:border-white/10"
                                    :style="{ backgroundColor: store.theme_color }">

                                    <Palette
                                        class="w-5 h-5 text-white opacity-0 group-hover:opacity-100 transition drop-shadow" />
                                </label>

                                <input id="theme-color-input" type="color" v-model="store.theme_color"
                                    class="absolute opacity-0 w-0 h-0 pointer-events-none" />

                                <Input v-model="store.theme_color" />
                            </div>
                        </div>

                    </div>
                </div>

                <!-- BOTÃO SALVAR -->
                <div class="flex justify-end">
                    <Button @click="saveStore"
                        class="flex items-center gap-2 px-6 py-3 text-white font-semibold bg-gradient-to-r from-emerald-500 to-emerald-600 shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all rounded-lg">
                        <Save class="w-4 h-4" />
                        Salvar Configurações
                    </Button>
                </div>

            </div>
        </SettingsLayout>
    </AppLayout>
</template>
