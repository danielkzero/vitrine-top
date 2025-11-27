<script setup lang="ts">
import { getIcon } from '@/lib/iconMap';

</script>

<template>

    <!-- Hero / Card Demonstrativo -->
    <div class="w-full max-w-[380px] mx-auto relative mt-6">

        <!-- Glow de fundo (mantido) -->
        <div class="absolute -inset-1 bg-gradient-to-r from-sky-400 to-emerald-600 rounded-2xl blur opacity-30">
        </div>

        <!-- Card principal -->
        <div
            class="relative bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden max-h-[620px] flex flex-col">

            <!-- Barra fake de navegador (mantida) -->
            <div class="bg-slate-50 px-4 py-3 border-b border-slate-100 flex items-center gap-2">
                <div class="flex gap-1.5">
                    <div class="w-3 h-3 rounded-full bg-red-400"></div>
                    <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                    <div class="w-3 h-3 rounded-full bg-green-400"></div>
                </div>
                <div class="flex-1 bg-white h-6 rounded-md border border-slate-200 mx-4"></div>
            </div>

            <!-- Conteúdo scrollável -->
            <div class="p-4 bg-slate-50 overflow-y-auto flex-1 space-y-4">
                <!-- ================================ -->
                <!-- INFO DA LOJA FAKE                -->
                <!-- ================================ -->
                <div class="flex items-center gap-3">
                    <div
                        class="w-14 h-14 rounded-full bg-gradient-to-tr from-sky-400 to-emerald-500 shadow-lg flex items-center justify-center text-white font-bold text-xl">
                        VT
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Loja Exemplo</h3>
                        <p class="text-emerald-600 text-xs">Aberto agora • Entrega em 1h</p>
                    </div>
                </div>

                <!-- ================================ -->
                <!-- HEADER FAKE DE BUSCA + AÇÕES     -->
                <!-- ================================ -->
                <div class="flex items-center gap-3">

                    <!-- Botão menu -->
                    <button class="p-2 bg-white rounded-xl shadow-sm border border-slate-200">
                        <component :is="getIcon('ShoppingCart')" fill="currentColor" class="w-5 h-5 text-slate-700" />
                    </button>

                    <!-- Campo de busca -->
                    <div class="flex-1 relative">
                        <button class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                            <component :is="getIcon('Search')" class="w-4 h-4" />
                        </button>

                        <input type="search" placeholder="Pesquisar produtos..." disabled
                            class="w-full pl-10 pr-3 py-2 rounded-xl bg-white border border-slate-200 text-sm text-slate-400 select-none" />
                    </div>

                    <!-- Alternar view -->
                    <button class="p-2 bg-white rounded-xl shadow-sm border border-slate-200">
                        <component :is="getIcon('LayoutGrid')" class="w-5 h-5 text-slate-700" />
                    </button>
                </div>

                <!-- ================================ -->
                <!-- BANNERCAROUSEL FAKE (PLACEHOLDER) -->
                <!-- ================================ -->
                <div class="w-full">
                    <div class="relative w-full overflow-hidden rounded-xl shadow-md bg-white">

                        <!-- Slides fake -->
                        <div class="flex transition-transform duration-500" :style="{ transform: 'translateX(0%)' }">
                            <div v-for="i in 3" :key="i"
                                class="min-w-full h-32 bg-slate-200 flex items-center justify-center text-slate-500 text-sm font-medium">
                                Banner {{ i }}
                            </div>
                        </div>

                        <!-- Indicadores fake -->
                        <div class="absolute bottom-2 inset-x-0 flex justify-center gap-1.5">
                            <span v-for="i in 3" :key="i" class="w-2 h-2 bg-white/60 rounded-full shadow"></span>
                        </div>
                    </div>
                </div>

                <!-- ========================================= -->
                <!-- CATEGORIAS FAKE (NOVO BLOCO)               -->
                <!-- ========================================= -->
                <div class="mt-1 overflow-x-auto pb-1">
                    <div class="flex gap-2">

                        <!-- Botão Todas -->
                        <button class="px-3 py-1.5 rounded-xl text-sm whitespace-nowrap bg-emerald-500 text-white"
                            disabled>
                            Todas
                        </button>

                        <!-- Categorias fake -->
                        <button v-for="i in 4" :key="i"
                            class="px-3 py-1.5 rounded-xl text-sm whitespace-nowrap bg-white border border-slate-200 text-slate-700 flex items-center gap-2 select-none"
                            disabled>
                            <component :is="getIcon('Store')" class="w-4 h-4 text-emerald-500" />
                            Categoria {{ i }}
                        </button>

                    </div>
                </div>


                <!-- ================================ -->
                <!-- MINI PRODUTOS FAKE (MANTIDOS)    -->
                <!-- ================================ -->
                <div class="grid grid-cols-2 gap-4">
                    <div v-for="i in 4" :key="i"
                        class="bg-white p-3 rounded-lg border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                        <div
                            class="bg-slate-100 h-20 rounded-md mb-3 flex items-center justify-center text-slate-300 text-xs">
                            Foto
                        </div>

                        <div class="h-3 w-3/4 bg-slate-200 rounded mb-2"></div>

                        <div class="flex items-center justify-between">
                            <div class="h-3 w-1/2 bg-emerald-100 rounded"></div>

                            <div
                                class="w-6 h-6 bg-emerald-500 rounded-full flex items-center justify-center text-white">
                                <component :is="getIcon('Plus')" class="w-3 h-3" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================================ -->
                <!-- MINI FOOTER STORE                -->
                <!-- ================================ -->
                <div class="bg-white border-t pb-14 mt-6 -mx-4">
                    <div class="container mx-auto px-4 py-6 text-sm text-slate-500 text-center">
                        © {{ new Date().getFullYear() }} Loja Exemplo
                    </div>
                </div>

                <!-- ================================ -->
                <!-- BottomNav Fake                   -->
                <!-- ================================ -->
                <div class="w-full px-4 mt-4 absolute bottom-4 left-0 md:-left-2">
                    <div
                        class="bg-white border-2 border-slate-100 rounded-2xl px-6 py-3 flex justify-between items-center shadow-xl mx-auto">

                        <div class="flex flex-col items-center text-slate-600">
                            <component :is="getIcon('Home')" class="w-6 h-6 mb-1" />
                            <span class="text-[10px] font-medium">Principal</span>

                        </div>

                        <div class="flex flex-col items-center text-emerald-500">
                            <component :is="getIcon('LayoutGrid')" class="w-6 h-6 mb-1" />
                            <span class="text-[10px] font-medium">Catálogo</span>
                            <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full mt-1"></div>
                        </div>

                        <div class="flex flex-col items-center text-slate-500">
                            <component :is="getIcon('Star')" class="w-6 h-6 mb-1" />
                            <span class="text-[10px] font-medium">Avaliações</span>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <!-- ================================ -->
        <!-- Badge flutuante: MANTIDO          -->
        <!-- ================================ -->
        <div
            class="absolute -right-5 top-10 bg-white p-4 rounded-lg shadow-xl border border-slate-100 animate-bounce duration-[3000ms]">
            <div class="flex items-center gap-3">
                <div class="bg-green-100 p-2 rounded-full text-green-600">
                    <component :is="getIcon('MessageCircle')" class="w-5 h-5" />
                </div>
                <div>
                    <p class="text-xs text-slate-500">Novo pedido via WhatsApp</p>
                    <p class="text-sm font-bold text-slate-800">R$ 149,90</p>
                </div>
            </div>
        </div>

    </div>

</template>