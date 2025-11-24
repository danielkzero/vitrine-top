// resources/js/lib/utils.ts

import { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

/**
 * Merge inteligente de classes Tailwind.
 * Evita conflitos como "p-2 p-4".
 */
export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

/**
 * Converte href (string ou objeto Inertia) em URL string.
 */
export function toUrl(href: NonNullable<InertiaLinkProps['href']>): string {
    if (typeof href === 'string') return href;
    return href?.url || '';
}

/**
 * Verifica se um link é o ativo pela comparação do URL.
 */
export function urlIsActive(
    urlToCheck: NonNullable<InertiaLinkProps['href']>,
    currentUrl: string,
): boolean {
    return toUrl(urlToCheck) === currentUrl;
}

/**
 * Formata valores para BRL com segurança.
 */
export function formatCurrency(value: number | string): string {
    const parsed = Number(value);
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(isNaN(parsed) ? 0 : parsed);
}

/**
 * Formata números com separador brasileiro.
 */
export function formatNumber(value: number | string): string {
    const parsed = Number(value);
    return new Intl.NumberFormat('pt-BR').format(
        isNaN(parsed) ? 0 : parsed,
    );
}

/**
 * Formata datas de forma amigável.
 */
export function formatDate(
    date: string | Date,
    options?: Intl.DateTimeFormatOptions,
): string {
    const d = new Date(date);
    return new Intl.DateTimeFormat('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        ...options,
    }).format(d);
}

/**
 * Debounce clássico (aguarda parar de chamar).
 */
export function debounce<T extends (...args: any[]) => void>(
    fn: T,
    delay = 300,
) {
    let timer: ReturnType<typeof setTimeout>;
    return (...args: Parameters<T>) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
}

/**
 * Throttle clássico (limita chamadas).
 */
export function throttle<T extends (...args: any[]) => void>(
    fn: T,
    limit = 200,
) {
    let waiting = false;
    return (...args: Parameters<T>) => {
        if (!waiting) {
            fn(...args);
            waiting = true;
            setTimeout(() => (waiting = false), limit);
        }
    };
}

/**
 * Promessa que resolve após X ms.
 */
export function sleep(ms: number) {
    return new Promise((resolve) => setTimeout(resolve, ms));
}

/**
 * Parse seguro para int, evitando NaN.
 */
export function safeParseInt(value: any, fallback = 0): number {
    const parsed = parseInt(value, 10);
    return isNaN(parsed) ? fallback : parsed;
}

/**
 * Parse seguro para float, evitando NaN.
 */
export function safeParseFloat(value: any, fallback = 0): number {
    const parsed = parseFloat(value);
    return isNaN(parsed) ? fallback : parsed;
}

/**
 * Acessa propriedades profundas com segurança.
 * Exemplo: safeGet(obj, "user.profile.name")
 */
export function safeGet(obj: any, path: string, fallback: any = null) {
    return path.split('.').reduce((acc, key) => {
        return acc && acc[key] !== undefined ? acc[key] : fallback;
    }, obj);
}
