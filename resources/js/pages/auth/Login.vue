<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <AuthBase
        title="Entre na sua conta"
        description="Insira seu e-mail e senha abaixo para entrar"
    >
        <Head title="Log in" />

        <div v-if="status" class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-center text-sm font-medium text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300">
            {{ status }}
        </div>

        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="mx-auto flex w-full max-w-md flex-col gap-6 rounded-2xl border border-border bg-card p-8 text-card-foreground shadow-xl dark:shadow-black/30"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email">E-mail</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="email@seuemail.com.br"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Senha</Label>
                        <TextLink
                            v-if="canResetPassword"
                            :href="request()"
                            class="text-sm text-muted-foreground hover:text-foreground"
                            :tabindex="5"
                        >
                            Esqueceu sua senha?
                        </TextLink>
                    </div>
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="Senha"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <Label for="remember" class="flex items-center space-x-3 text-sm text-foreground">
                        <Checkbox id="remember" name="remember" :tabindex="3" />
                        <span class="select-none">Lembrar-me</span>
                    </Label>
                </div>

                <Button
                    type="submit"
                    class="mt-4 w-full px-4 py-3 rounded-lg bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-semibold shadow-lg hover:translate-y-[-1px] transition-transform"
                    :tabindex="4"
                    :disabled="processing"
                    data-test="login-button"
                >
                    <Spinner v-if="processing" />
                    Entrar
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground" v-if="canRegister">
                Não tem uma conta?
                <TextLink :href="register()" :tabindex="5" class="font-medium text-emerald-600 dark:text-emerald-400">Cadastre-se</TextLink>
            </div>
        </Form>
    </AuthBase>
</template>
