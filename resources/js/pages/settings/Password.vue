<script setup lang="ts">
import PasswordController from '@/actions/App/Http/Controllers/Settings/PasswordController'
import InputError from '@/components/InputError.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import SettingsLayout from '@/layouts/settings/Layout.vue'
import { edit } from '@/routes/user-password'
import { Form, Head } from '@inertiajs/vue3'

import HeadingSmall from '@/components/HeadingSmall.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { type BreadcrumbItem } from '@/types'
import { Lock } from 'lucide-vue-next'

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Configuração de senha',
        href: edit().url,
    },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Configuração de senha" />

        <SettingsLayout>
            <div class="space-y-10">

                <HeadingSmall
                    title="Atualize sua senha"
                    description="Para manter sua conta segura, certifique-se de usar uma senha longa e aleatória."
                />

                <!-- CARD PRINCIPAL -->
                <div
                    class="p-8 rounded-2xl border shadow-sm
                           bg-slate-50/10 dark:bg-white/5
                           transition-colors duration-300"
                >
                    <!-- Header do Card -->
                    <div class="flex items-center gap-3 mb-6">
                        <div
                            class="w-10 h-10 rounded-lg flex items-center justify-center 
                                   bg-slate-50/10 dark:bg-white/5 text-emerald-600"
                        >
                            <Lock class="w-5 h-5" />
                        </div>

                        <h2 class="text-2xl font-bold">Alterar Senha</h2>
                    </div>

                    <!-- Formulário -->
                    <Form
                        v-bind="PasswordController.update.form()"
                        :options="{ preserveScroll: true }"
                        reset-on-success
                        :reset-on-error="[
                            'password',
                            'password_confirmation',
                            'current_password',
                        ]"
                        class="space-y-6"
                        v-slot="{ errors, processing, recentlySuccessful }"
                    >
                        <!-- Senha atual -->
                        <div class="grid gap-2">
                            <Label for="current_password">Senha atual</Label>
                            <Input
                                id="current_password"
                                name="current_password"
                                type="password"
                                autocomplete="current-password"
                                placeholder="Senha atual"
                            />
                            <InputError :message="errors.current_password" />
                        </div>

                        <!-- Nova senha -->
                        <div class="grid gap-2">
                            <Label for="password">Nova senha</Label>
                            <Input
                                id="password"
                                name="password"
                                type="password"
                                autocomplete="new-password"
                                placeholder="Nova senha"
                            />
                            <InputError :message="errors.password" />
                        </div>

                        <!-- Confirmação -->
                        <div class="grid gap-2">
                            <Label for="password_confirmation">Confirmar senha</Label>
                            <Input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                autocomplete="new-password"
                                placeholder="Confirmar senha"
                            />
                            <InputError :message="errors.password_confirmation" />
                        </div>

                        <!-- Botão + Feedback -->
                        <div class="flex items-center gap-4">
                            <Button
                                :disabled="processing"
                                class="flex items-center gap-2 px-6 py-3 text-white font-semibold
                                       bg-gradient-to-r from-emerald-500 to-emerald-600 shadow-lg
                                       hover:shadow-xl hover:-translate-y-0.5 transition-all rounded-lg"
                            >
                                Salvar senha
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
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
