<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue'
import TwoFactorRecoveryCodes from '@/components/TwoFactorRecoveryCodes.vue'
import TwoFactorSetupModal from '@/components/TwoFactorSetupModal.vue'

import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'

import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth'
import AppLayout from '@/layouts/AppLayout.vue'
import SettingsLayout from '@/layouts/settings/Layout.vue'

import { disable, enable, show } from '@/routes/two-factor'
import { BreadcrumbItem } from '@/types'

import { Form, Head } from '@inertiajs/vue3'
import { ShieldBan, ShieldCheck, Shield } from 'lucide-vue-next'

import { onUnmounted, ref } from 'vue'

interface Props {
    requiresConfirmation?: boolean
    twoFactorEnabled?: boolean
}

withDefaults(defineProps<Props>(), {
    requiresConfirmation: false,
    twoFactorEnabled: false,
})

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Autenticação de dois fatores',
        href: show.url(),
    },
]

const { hasSetupData, clearTwoFactorAuthData } = useTwoFactorAuth()
const showSetupModal = ref<boolean>(false)

onUnmounted(() => clearTwoFactorAuthData())
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Autenticação de dois fatores" />

        <SettingsLayout>
            <div class="space-y-10">

                <HeadingSmall
                    title="Autenticação de dois fatores"
                    description="Gerencie as configurações de autenticação extra para proteger sua conta."
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
                                   bg-slate-50/10 dark:bg-white/5 
                                   text-emerald-600 dark:text-emerald-400"
                        >
                            <Shield class="w-5 h-5" />
                        </div>

                        <h2 class="text-2xl font-bold">Segurança adicional</h2>
                    </div>

                    <!-- ========================= -->
                    <!-- 2FA DESATIVADO -->
                    <!-- ========================= -->
                    <div
                        v-if="!twoFactorEnabled"
                        class="space-y-6"
                    >
                        <Badge variant="destructive">Desativado</Badge>

                        <p class="text-muted-foreground dark:text-neutral-300 leading-relaxed">
                            A autenticação de dois fatores adiciona uma camada extra de segurança.
                            Ao ativá-la, você precisará inserir um PIN gerado por um aplicativo TOTP
                            ao fazer login.
                        </p>

                        <div>
                            <!-- Continuação de setup pendente -->
                            <Button
                                v-if="hasSetupData"
                                @click="showSetupModal = true"
                                class="flex items-center gap-2 px-6 py-3 font-semibold
                                       bg-gradient-to-r from-emerald-500 to-emerald-600 text-white shadow-lg
                                       hover:shadow-xl hover:-translate-y-0.5 transition-all"
                            >
                                <ShieldCheck class="w-5 h-5" />
                                Continuar configuração
                            </Button>

                            <!-- Ativar 2FA -->
                            <Form
                                v-else
                                v-bind="enable.form()"
                                @success="showSetupModal = true"
                                v-slot="{ processing }"
                            >
                                <Button
                                    type="submit"
                                    :disabled="processing"
                                    class="flex items-center gap-2 px-6 py-3 font-semibold
                                           bg-gradient-to-r from-emerald-500 to-emerald-600 text-white shadow-lg
                                           hover:shadow-xl hover:-translate-y-0.5 transition-all"
                                >
                                    <ShieldCheck class="w-5 h-5" />
                                    Ativar 2FA
                                </Button>
                            </Form>
                        </div>
                    </div>

                    <!-- ========================= -->
                    <!-- 2FA ATIVADO -->
                    <!-- ========================= -->
                    <div
                        v-else
                        class="space-y-6"
                    >
                        <Badge variant="default">Ativo</Badge>

                        <p class="text-muted-foreground dark:text-neutral-300 leading-relaxed">
                            A autenticação de dois fatores está habilitada. Sempre que você fizer login,
                            será solicitado a fornecer um PIN gerado por seu aplicativo TOTP.
                        </p>

                        <!-- Recovery Codes -->
                        <TwoFactorRecoveryCodes />

                        <!-- Desativar -->
                        <Form v-bind="disable.form()" v-slot="{ processing }">
                            <Button
                                variant="destructive"
                                type="submit"
                                :disabled="processing"
                                class="flex items-center gap-2"
                            >
                                <ShieldBan class="w-5 h-5" />
                                Desativar 2FA
                            </Button>
                        </Form>
                    </div>
                </div>

                <!-- Modal -->
                <TwoFactorSetupModal
                    v-model:isOpen="showSetupModal"
                    :requiresConfirmation="requiresConfirmation"
                    :twoFactorEnabled="twoFactorEnabled"
                />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
