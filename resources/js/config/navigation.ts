import { routes } from './routes';
import { CreditCard, FileText, Home, Layers, LifeBuoy, MessageCircleMore, ReceiptText, UsersRound } from 'lucide-vue-next';
import type { NavItem } from '@/types';

export const mainNavItems: NavItem[] = [
    { title: 'Painel', href: routes.painel.index, icon: Home },
    { title: 'Pedidos', href: routes.painel.orders.index, icon: ReceiptText },
    { title: 'Clientes', href: routes.painel.customers.index, icon: UsersRound },
    { title: 'Páginas', href: routes.painel.pages.index, icon: FileText },
    { title: 'Banners', href: routes.painel.banners.index, icon: Layers },
    { title: 'Avaliacoes', href: routes.painel.reviews.index, icon: MessageCircleMore },
    { title: 'Assinatura e cobrança', href: routes.painel.billing.index, icon: CreditCard },
    { title: 'Suporte', href: '/painel/suporte', icon: LifeBuoy },
];

export const footerNavItems: NavItem[] = [];
