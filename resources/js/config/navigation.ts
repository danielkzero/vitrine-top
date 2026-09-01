import { routes } from './routes';
import { CreditCard, FileText, Home, Layers, MessageCircleMore } from 'lucide-vue-next';
import type { NavItem } from '@/types';

export const mainNavItems: NavItem[] = [
    { title: 'Painel', href: routes.painel.index, icon: Home },
    { title: 'Páginas', href: routes.painel.pages.index, icon: FileText },
    { title: 'Banners', href: routes.painel.banners.index, icon: Layers },
    { title: 'Avaliacoes', href: routes.painel.reviews.index, icon: MessageCircleMore },
    { title: 'Assinatura e cobrança', href: routes.painel.billing.index, icon: CreditCard },
];

export const footerNavItems: NavItem[] = [];
