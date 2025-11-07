import { routes } from './routes';
import { Home, FileText, Layers, Star, CreditCard, Settings } from 'lucide-vue-next';
import type { NavItem } from '@/types';

export const mainNavItems: NavItem[] = [
    { title: 'Início', href: routes.painel.index, icon: Home },
    { title: 'Páginas', href: routes.painel.pages.index, icon: FileText },
    { title: 'Categorias', href: routes.painel.categories.index, icon: Layers },
    { title: 'Avaliações', href: routes.painel.reviews.index, icon: Star },
    { title: 'Assinaturas', href: routes.painel.subscriptions.index, icon: CreditCard },
    { title: 'Configurações', href: routes.painel.payments.index, icon: Settings },
];

export const footerNavItems: NavItem[] = [
    { title: 'Repositório', href: 'https://github.com/laravel/vue-starter-kit', icon: Layers },
    { title: 'Documentação', href: 'https://laravel.com/docs/starter-kits#vue', icon: FileText }
];
