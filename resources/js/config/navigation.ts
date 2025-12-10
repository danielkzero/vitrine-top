import { routes } from './routes';
import { Home, FileText, Layers, Star, CreditCard, Settings } from 'lucide-vue-next';
import type { NavItem } from '@/types';

export const mainNavItems: NavItem[] = [
    { title: 'Painel', href: routes.painel.index, icon: Home },
    { title: 'Páginas', href: routes.painel.pages.index, icon: FileText },
    { title: 'Banners', href: routes.painel.banners.index, icon: Layers },
    /*{ title: 'Avaliações', href: routes.painel.reviews.index, icon: Star },
    { title: 'Assinaturas', href: routes.painel.subscriptions.index, icon: CreditCard },
    { title: 'Configurações', href: routes.painel.payments.index, icon: Settings },*/
];

export const footerNavItems: NavItem[] = [
   
];
