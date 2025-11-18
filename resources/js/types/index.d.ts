import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;

    business_name: string;
    slug: string;
    description: string;
    logo_url: string;
    banner_url: string;
    theme_color: string;
    phone: string;
    whatsapp: string;
    instagram: string;
    facebook: string;
    phone_primary: string;
}

export interface Product {
    id: number;
    user_id: number;
    category_id: number;
    name: string;
    slug: string;
    description: string;
    price: number;
    discount_price: number | null;
    active: boolean;
    featured: boolean;
    created_at: string;
    updated_at: string;
    images?: ProductImage[];
    category?: {
        id: number;
        name: string;
    };
}

export type BreadcrumbItemType = BreadcrumbItem;
