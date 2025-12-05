<script setup lang="ts">
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController'
import { edit } from '@/routes/profile'
import { send } from '@/routes/verification'
import { Form, Head, Link, usePage } from '@inertiajs/vue3'

import DeleteUser from '@/components/DeleteUser.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import InputError from '@/components/InputError.vue'

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

import AppLayout from '@/layouts/AppLayout.vue'
import SettingsLayout from '@/layouts/settings/Layout.vue'
import { type BreadcrumbItem } from '@/types'

import { User } from 'lucide-vue-next'

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>()

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Configurações do perfil',
        href: edit().url,
    },
]

const page = usePage()
const user = page.props.auth.user
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Configurações do perfil" />

        <SettingsLayout>
            <div class="space-y-10">

                <HeadingSmall
                    title="Perfil do usuário"
                    description="Atualize suas informações de nome e e-mail"
                />

                <!-- CARD PRINCIPAL -->
                <div
                    class="p-8 rounded-2xl border shadow-sm 
                           bg-slate-50/10 dark:bg-white/5
                           transition-colors duration-300"
                >
                    <!-- Header -->
                    <div class="flex items-center gap-3 mb-6">
                        <div
                            class="w-10 h-10 rounded-lg flex items-center justify-center
                                   bg-slate-50/10 dark:bg-white/5 
                                   text-emerald-600 dark:text-emerald-400"
                        >
                            <User class="w-5 h-5" />
                        </div>

                        <h2 class="text-2xl font-bold">Informações de Perfil</h2>
                    </div>

                    <!-- FORMULÁRIO -->
                    <Form
                        v-bind="ProfileController.update.form()"
                        class="space-y-6"
                        v-slot="{ errors, processing, recentlySuccessful }"
                    >
                        <!-- NOME -->
                        <div class="grid gap-2">
                            <Label for="name">Nome</Label>
                            <Input
                                id="name"
                                name="name"
                                :default-value="user.name"
                                autocomplete="name"
                                required
                                placeholder="Nome completo"
                            />
                            <InputError :message="errors.name" />
                        </div>

                        <!-- EMAIL -->
                        <div class="grid gap-2">
                            <Label for="email">E-mail</Label>
                            <Input
                                id="email"
                                name="email"
                                type="email"
                                :default-value="user.email"
                                autocomplete="username"
                                required
                                placeholder="Seu e-mail"
                            />
                            <InputError :message="errors.email" />
                        </div>

                        <!-- VERIFICAÇÃO DE EMAIL -->
                        <div v-if="mustVerifyEmail && !user.email_verified_at" class="space-y-2">
                            <p class="text-sm text-neutral-600 dark:text-neutral-300">
                                Seu e-mail ainda não foi verificado.
                                <Link
                                    :href="send()"
                                    as="button"
                                    class="text-emerald-600 underline underline-offset-4
                                           hover:text-emerald-500 transition"
                                >
                                    Clique aqui para reenviar o e-mail de verificação.
                                </Link>
                            </p>

                            <div
                                v-if="status === 'verification-link-sent'"
                                class="text-sm font-medium text-green-600 dark:text-green-400"
                            >
                                Um novo link de verificação foi enviado para o seu e-mail.
                            </div>
                        </div>

                        <!-- BOTÃO -->
                        <div class="flex items-center gap-4">
                            <Button
                                :disabled="processing"
                                class="flex items-center gap-2 px-6 py-3 text-white font-semibold
                                       bg-gradient-to-r from-emerald-500 to-emerald-600 shadow-lg
                                       hover:shadow-xl hover:-translate-y-0.5 transition-all rounded-lg"
                            >
                                Salvar
                            </Button>

                            <Transition
                                enter-active-class="transition ease-in-out"
                                enter-from-class="opacity-0"
                                leave-active-class="transition ease-in-out"
                                leave-to-class="opacity-0"
                            >
                                <p
                                    v-show="recentlySuccessful"
                                    class="text-sm text-neutral-600 dark:text-neutral-300"
                                >
                                    Salvo.
                                </p>
                            </Transition>
                        </div>
                    </Form>
                </div>

                <!-- DELETE USER -->
                <DeleteUser />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
