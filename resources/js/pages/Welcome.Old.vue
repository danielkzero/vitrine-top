<script setup lang="ts">
import { dashboard, login, register } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';
import AppLogo from '@/components/AppLogo.vue';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div
        class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]"
    >
        <header class="mb-6 w-full max-w-[335px] lg:max-w-4xl text-sm">
            <nav class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <AppLogo />
                </div>

                <div class="flex items-center gap-3">
                    <a
                        href="https://wa.me/5524999699849"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 rounded-sm bg-emerald-500 px-4 py-1.5 text-sm font-medium text-white hover:bg-emerald-600"
                        title="Fale conosco pelo WhatsApp"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12c0 4.97-4.03 9-9 9a9 9 0 01-4.95-1.59L3 21l1.59-3.06A9 9 0 013 12c0-4.97 4.03-9 9-9s9 4.03 9 9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 7a3 3 0 00-4.24 0L11 8.76" />
                        </svg>
                        WhatsApp
                    </a>

                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="inline-block rounded-sm border border-[#19140035] px-4 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC]"
                    >
                        Painel
                    </Link>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="inline-block rounded-sm border border-transparent px-4 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC]"
                        >
                            Entrar
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="register()"
                            class="inline-block rounded-sm border border-[#19140035] px-4 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC]"
                        >
                            Cadastrar-se
                        </Link>
                    </template>
                </div>
            </nav>
        </header>

        <div class="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0">
            <main class="flex w-full max-w-[335px] flex-col-reverse overflow-hidden rounded-lg bg-white p-6 shadow-sm lg:max-w-4xl lg:flex-row lg:items-center">
                <section class="w-full lg:w-1/2 lg:pr-8">
                    <h1 class="mb-3 text-3xl font-extrabold leading-tight">
                        <span class="block text-4xl text-transparent bg-clip-text bg-gradient-to-r to-emerald-600 from-sky-400">vitrine.top</span>
                        Sua vitrine online profissional
                    </h1>
                    <p class="mb-4 text-sm text-gray-700 dark:text-gray-300">
                        Sua empresa merece ter uma vitrine online. Apresente seu catálogo, aumente suas vendas e alcance novos clientes com uma presença digital profissional.
                    </p>

                    <ul class="mb-4 space-y-2 text-sm text-gray-600 dark:text-gray-400">
                        <li>• Catálogo digital moderno</li>
                        <li>• Loja online e vitrine virtual</li>
                        <li>• Aumente conversões e visibilidade</li>
                    </ul>

                    <div class="flex items-center gap-3">
                        <Link href="#" class="rounded-md bg-sky-600 px-4 py-2 text-sm font-medium text-white hover:bg-sky-700">Ver Catálogo</Link>
                        <a href="https://wa.me/5524999699849" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-md border border-emerald-500 px-4 py-2 text-sm font-medium text-emerald-600 hover:bg-emerald-50">Fale conosco (WhatsApp)</a>
                    </div>
                </section>

                <aside class="mb-4 flex w-full items-center justify-center lg:mb-0 lg:w-1/2">
                    <div class="w-full max-w-sm rounded-md border border-gray-100 p-6 text-center">
                        <div class="mb-4 inline-block h-28 w-28 rounded-full bg-gradient-to-r from-sky-400 to-emerald-500"></div>
                        <h3 class="mb-2 text-lg font-semibold">Apresente seu catálogo</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Transforme seus produtos em uma vitrine atraente para clientes.</p>
                    </div>
                </aside>
            </main>
        </div>

        <div class="hidden h-14.5 lg:block"></div>
    </div>
</template>
