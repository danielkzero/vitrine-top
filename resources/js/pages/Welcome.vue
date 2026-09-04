<script setup lang="ts">
import { login, register } from '@/routes';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Head } from '@inertiajs/vue3';

import AppLogoIcon from '@/components/AppLogoIcon.vue';
import BaseButton from '@/components/ui/button/BaseButton.vue';

// iconMap centralizado
import iconMap, { getIcon } from '@/lib/iconMap';

import content from '@/data/welcomeContent';
import DemoStorePreview from './DemoStorePreview.vue';

const isMenuOpen = ref(false);
const scrolled = ref(false);

const toggleMenu = () => (isMenuOpen.value = !isMenuOpen.value);

function onScroll() {
    scrolled.value = window.scrollY > 20;
}

onMounted(() => {
    window.addEventListener('scroll', onScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', onScroll);
});

type PlanCard = {
    code: 'basic' | 'medium' | 'plus' | 'premium';
    name: string;
    monthly_price: number;
    annual_price_total: number;
    annual_monthly_equivalent: number;
    limits: {
        products: number;
        product_images: number | null;
        gallery_images: number;
        banners: number;
    };
    trial_days: number;
};

const props = withDefaults(
    defineProps<{
        canRegister: boolean;
        planCatalog: PlanCard[];
        trialDays: number;
    }>(),
    {
        canRegister: true,
        planCatalog: () => [],
        trialDays: 14,
    },
);

const planOrder: Record<PlanCard['code'], number> = {
    basic: 1,
    medium: 2,
    plus: 3,
    premium: 4,
};

const orderedPlans = computed(() =>
    [...props.planCatalog].sort((a, b) => planOrder[a.code] - planOrder[b.code]),
);

function formatBrl(value: number): string {
    return value.toFixed(2).replace('.', ',');
}
</script>


<template>

    <Head>
        <link rel="icon" href="/store.svg" type="image/svg+xml">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div class="min-h-screen bg-[#FDFDFC] font-sans text-[#1b1b18]">
        <!-- Navbar -->
        <nav
            :class="['fixed top-0 left-0 right-0 z-50 transition-all duration-300', scrolled ? 'bg-white/90 backdrop-blur-md shadow-sm py-3' : 'bg-transparent py-5']">
            <div class="max-w-7xl mx-auto px-6 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <AppLogoIcon class="bg-gradient-to-r to-emerald-600 from-sky-400" />
                    <span class="text-xl font-bold tracking-tight text-slate-800">vitrine<span
                            class="text-emerald-600">.top</span></span>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center gap-8">
                    <a href="#recursos"
                        class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors">Recursos</a>
                    <a href="#como-funciona"
                        class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors">Como
                        Funciona</a>
                    <a href="#planos"
                        class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors">Planos</a>
                    <a href="#trial"
                        class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors">Período de teste</a>
                </div>

                <div class="hidden md:flex items-center gap-4">
                    <BaseButton v-if="$page.props.auth.user" as="Link" href="/painel" variant="ghost" size="sm">
                        Painel
                    </BaseButton>
                    <template v-else>
                        <BaseButton as="Link" :href="login()" variant="ghost" size="sm">
                            Entrar
                        </BaseButton>

                        <BaseButton v-if="props.canRegister" as="Link" :href="register()" variant="pill" size="sm"
                            trailing-icon="ArrowRight">
                            Criar conta grátis
                        </BaseButton>
                    </template>
                </div>

                <!-- Mobile Toggle -->
                <button @click="toggleMenu" class="md:hidden text-slate-700">
                    <svg v-if="isMenuOpen" xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor">
                        <path d="M6 18L18 6M6 6l12 12" stroke-width="1.5" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor">
                        <path d="M4 6h16M4 12h16M4 18h16" stroke-width="1.5" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </button>
            </div>

            <!-- Mobile Menu Dropdown -->
            <div v-if="isMenuOpen"
                class="absolute top-full left-0 right-0 bg-white shadow-lg border-t border-slate-100 p-6 md:hidden flex flex-col gap-4">
                <a href="#recursos" @click="isMenuOpen = false"
                    class="text-base font-medium text-slate-600">Recursos</a>
                <a href="#como-funciona" @click="isMenuOpen = false" class="text-base font-medium text-slate-600">Como
                    Funciona</a>
                <a href="#planos" @click="isMenuOpen = false" class="text-base font-medium text-slate-600">Planos</a>
                <a href="#trial" @click="isMenuOpen = false" class="text-base font-medium text-slate-600">Período de teste</a>
                <hr class="border-slate-100" />
                <a v-if="$page.props.auth.user" href="/painel"
                    class="text-base font-medium text-emerald-600">Painel</a>
                <template v-else>
                    <a href="/login" class="text-base font-medium text-slate-600">Entrar</a>
                     <BaseButton v-if="props.canRegister" as="Link" :href="register()" variant="dark" size="lg"
                            trailing-icon="ArrowRight">
                            Criar conta grátis
                        </BaseButton>
                </template>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative pt-32 pb-20 lg:pt-40 lg:pb-32 overflow-hidden">
            <div
                class="absolute top-0 right-0 -z-10 w-96 h-96 bg-sky-200/40 rounded-full blur-3xl translate-x-1/2 -translate-y-1/4">
            </div>
            <div
                class="absolute bottom-0 left-0 -z-10 w-96 h-96 bg-emerald-200/30 rounded-full blur-3xl -translate-x-1/3 translate-y-1/4">
            </div>

            <div class="max-w-7xl mx-auto px-6">
                <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-20">
                    <!-- Hero Content -->
                    <div class="flex-1 text-center lg:text-left">
                        <div
                            class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-700 text-xs font-semibold mb-6 uppercase tracking-wider">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            {{ content.hero.prelabel }}
                        </div>

                        <h1
                            class="text-4xl lg:text-6xl font-extrabold tracking-tight text-slate-900 leading-[1.1] mb-6">
                            {{ content.hero.titleLine1 }} <br />
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-400 to-emerald-600">{{
                                content.hero.titleGradient }}</span>
                        </h1>

                        <p class="text-lg text-slate-600 mb-8 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                            {{ content.hero.paragraph }}
                        </p>

                        <div class="flex flex-col sm:flex-row items-center gap-4 justify-center lg:justify-start">
                            <a href="#planos"
                                class="w-full sm:w-auto px-8 py-4 rounded-lg bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-semibold shadow-lg hover:-translate-y-1 transition-all duration-300 flex items-center justify-center gap-2">{{
                                    content.hero.ctaPrimary }}
                                <component :is="getIcon('ArrowRight')" class="w-5 h-5" />
                            </a>
                            <a href="https://wa.me/5524999699849" target="_blank" rel="noopener noreferrer"
                                class="w-full sm:w-auto px-8 py-4 rounded-lg bg-white border border-slate-200 text-slate-700 font-semibold hover:bg-slate-50 transition-all flex items-center justify-center gap-2">
                                <component :is="getIcon('MessageCircle')" class="w-5 h-5 text-emerald-500" />
                                Falar no WhatsApp
                            </a>
                        </div>

                        <div
                            class="mt-8 flex items-center justify-center lg:justify-start gap-4 text-sm text-slate-500">
                            <div v-for="(b, i) in content.hero.badges" :key="i" class="flex items-center gap-1">
                                <component :is="getIcon('CheckCircle2')" class="w-4 h-4 text-emerald-500" />
                                {{ b }}
                            </div>
                        </div>
                    </div>

                    <!-- Hero / Card Demonstrativo -->
                    <DemoStorePreview />

                </div>
            </div>
        </section>

        <!-- ================================ -->
        <!-- Categories Section               -->
        <!-- ================================ -->
        <section id="categorias" class="py-20 bg-slate-50">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center max-w-3xl mx-auto mb-8">
                    <h2 class="text-3xl font-bold text-slate-900 mb-4">{{ content.categories.introTitle }}</h2>
                    <p class="text-slate-600">{{ content.categories.introText }}</p>
                </div>

                <div class="flex flex-wrap justify-center gap-3 mt-8">
                    <span v-for="(c, idx) in content.categories.items" :key="idx"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-100 rounded-full shadow-sm text-sm text-slate-700">
                        <component :is="iconMap[c.icon] || getIcon('Store')" class="w-4 h-4 text-emerald-500" />
                        {{ c.name }}
                    </span>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="recursos" class="py-20 bg-white border-t border-slate-100">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <h2 class="text-3xl font-bold text-slate-900 mb-4">Tudo que você precisa para vender mais</h2>
                    <p class="text-slate-600">Nossa plataforma foi desenhada para simplificar a vida do empreendedor e
                        facilitar a compra do cliente.</p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="(f, idx) in content.features" :key="idx"
                        class="p-6 rounded-xl border border-slate-100 bg-white hover:shadow-xl hover:shadow-emerald-500/5 hover:-translate-y-1 transition-all duration-300 group">
                        <div
                            class="w-12 h-12 rounded-lg bg-slate-50 flex items-center justify-center mb-4 group-hover:bg-emerald-50 transition-colors">
                            <component :is="iconMap[f.icon]" class="w-6 h-6 text-slate-700" />
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-2">{{ f.title }}</h3>
                        <p class="text-slate-600 leading-relaxed">{{ f.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- How it Works Section -->
        <section id="como-funciona" class="py-20 bg-slate-50">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <h2 class="text-3xl font-bold text-slate-900 mb-4">Como funciona?</h2>
                    <p class="text-slate-600">Comece a vender em menos de 5 minutos. O processo é simples e intuitivo.
                    </p>
                </div>

                <div class="grid md:grid-cols-4 gap-8 relative">
                    <div class="hidden md:block absolute top-6 left-0 right-0 h-0.5 bg-emerald-500 z-0"></div>


                    <div v-for="(s, i) in content.steps" :key="i"
                        class="relative z-10 bg-slate-50 md:bg-transparent p-4 md:p-0 rounded-lg text-center">
                        <div
                            class="w-12 h-12 mx-auto bg-white border-2 border-emerald-500 text-emerald-600 rounded-full flex items-center justify-center text-xl font-bold mb-4 shadow-sm relative">
                            <component :is="iconMap[s.icon]" class="w-5 h-5" />
                            <div
                                class="absolute -top-2 -right-2 w-6 h-6 bg-slate-900 text-white text-xs rounded-full flex items-center justify-center border-2 border-white">
                                {{ s.number }}</div>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800 mb-2">{{ s.title }}</h3>
                        <p class="text-sm text-slate-600">{{ s.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Plans Section -->
        <section id="planos" class="py-24 bg-gradient-to-b from-white to-slate-50">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <h2 class="text-4xl font-extrabold text-slate-900 mb-4">Planos Para Crescer</h2>
                    <p class="text-slate-600">Escolha o plano ideal para sua fase. O Plano Médio é o mais equilibrado para vender com escala.</p>
                </div>

                <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-6 items-stretch">
                    <article
                        v-for="plan in orderedPlans"
                        :key="plan.code"
                        class="relative rounded-3xl p-6 border transition-all duration-300 hover:-translate-y-1"
                        :class="plan.code === 'medium'
                            ? 'bg-gradient-to-b from-emerald-600 to-emerald-500 text-white border-emerald-500 shadow-2xl shadow-emerald-500/30 xl:scale-105'
                            : 'bg-white border-slate-200 text-slate-900 hover:shadow-xl'"
                    >
                        <div v-if="plan.code === 'medium'" class="absolute -top-3 left-1/2 -translate-x-1/2">
                            <span class="px-4 py-1 rounded-full text-xs font-bold tracking-wide bg-slate-900 text-white">
                                MAIS ESCOLHIDO
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-bold">{{ plan.name }}</h3>
                            <span
                                class="text-[11px] uppercase font-semibold px-2 py-1 rounded-full"
                                :class="plan.code === 'medium' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700'"
                            >
                                {{ plan.code }}
                            </span>
                        </div>

                        <div class="mt-5">
                            <p class="text-sm opacity-90">Mensal</p>
                            <p class="text-3xl font-extrabold">R$ {{ formatBrl(plan.monthly_price) }}</p>
                            <p class="text-xs mt-1 opacity-80">Anual: R$ {{ formatBrl(plan.annual_monthly_equivalent) }}/mês</p>
                            <p class="text-xs opacity-80">Total anual: R$ {{ formatBrl(plan.annual_price_total) }}</p>
                        </div>

                        <ul class="mt-5 space-y-2 text-sm">
                            <li>Produtos: {{ plan.limits.products }}</li>
                            <li>Fotos/produto: {{ plan.limits.product_images ?? 'Ilimitadas' }}</li>
                            <li>Galeria: {{ plan.limits.gallery_images }} fotos</li>
                            <li>Banners: {{ plan.limits.banners }}</li>
                            <li>Período de teste: {{ plan.trial_days }} dias</li>
                        </ul>

                        <BaseButton
                            v-if="props.canRegister"
                            :variant="plan.code === 'medium' ? 'dark' : 'primary'"
                            block
                            class="mt-6"
                            :href="register()"
                            as="Link"
                        >
                            Quero este plano
                        </BaseButton>
                    </article>
                </div>
            </div>
        </section>

        <section id="trial" class="py-20 bg-slate-50 border-t border-slate-100">
            <div class="max-w-5xl mx-auto px-6">
                <div class="rounded-3xl border border-slate-200 bg-white p-8 md:p-10">
                    <h2 class="text-3xl font-bold text-slate-900 mb-4">Como funciona o trial de {{ props.trialDays }} dias</h2>
                    <div class="grid md:grid-cols-3 gap-4 text-sm text-slate-700">
                        <div class="rounded-xl bg-slate-50 p-4 border">
                            <p class="font-semibold mb-2">1. Cadastro imediato</p>
                            <p>Ao criar a conta, você entra com acesso completo durante o período de teste.</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-4 border">
                            <p class="font-semibold mb-2">2. Aviso de regularização</p>
                            <p>No fim do trial, a plataforma exige escolha de plano e pagamento para continuar.</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-4 border">
                            <p class="font-semibold mb-2">3. Continuidade da vitrine</p>
                            <p>Com assinatura paga em dia, painel e vitrine pública seguem ativos sem bloqueios.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 bg-slate-900 text-white relative overflow-hidden">
            <div class="max-w-4xl mx-auto px-6 text-center relative z-10">
                <h2 class="text-3xl md:text-4xl font-bold mb-6">Pronto para profissionalizar seu negócio?</h2>
                <p class="text-slate-300 mb-8 text-lg">Junte-se a nós e a mais lojistas que usam o Vitrine Top para
                    organizar suas vendas online.</p>
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="#planos"
                        class="px-8 py-4 rounded-lg bg-emerald-500 hover:bg-emerald-400 text-white font-bold transition-colors">Criar
                        minha vitrine grátis</a>
                    <BaseButton as="a" href="/demonstracao" variant="outline" size="lg">
                        Ver demonstração
                    </BaseButton>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-white pt-16 pb-8 border-t border-slate-100">
            <div class="max-w-7xl mx-auto px-6">
                <div class="grid md:grid-cols-3 gap-8 mb-12">
                    <div class="col-span-1 md:col-span-1">
                        <div class="flex items-center gap-2 mb-4">
                            <AppLogoIcon />
                            <span class="font-bold text-slate-800">vitrine.top</span>
                        </div>
                        <p class="text-sm text-slate-500">A solução completa para criar sua presença digital e vender
                            mais pelo WhatsApp.</p>
                    </div>

                    <div>
                        <h4 class="font-bold text-slate-800 mb-4">Produto</h4>
                        <ul class="space-y-2 text-sm text-slate-500">
                            <li><a href="#" class="hover:text-emerald-600">Funcionalidades</a></li>
                            <li><a href="#" class="hover:text-emerald-600">Preços</a></li>
                            <li><a href="#" class="hover:text-emerald-600">Exemplos</a></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="font-bold text-slate-800 mb-4">Suporte</h4>
                        <ul class="space-y-2 text-sm text-slate-500">
                            <li><a href="#" class="hover:text-emerald-600">Central de Ajuda</a></li>
                            <li><a href="#" class="hover:text-emerald-600">Falar no WhatsApp</a></li>
                            <li><a href="#" class="hover:text-emerald-600">Termos de Uso</a></li>
                        </ul>
                    </div>

                </div>

                <div class="border-t border-slate-100 pt-8 text-center text-sm text-slate-400">&copy; {{ new
                    Date().getFullYear() }} Vitrine Top. Todos os direitos reservados.</div>
            </div>
        </footer>
    </div>
</template>
