<template>
    <component :is="componentTag" v-bind="componentProps" :disabled="isButton && (disabled || loading)"
        :class="computedClasses" @click="onClick">
        <!-- Loading Spinner -->
        <svg v-if="loading" class="animate-spin h-5 w-5 text-current mr-2" xmlns="http://www.w3.org/2000/svg"
            fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
        </svg>

        <!-- Leading Icon -->
        <component v-if="leadingIcon && !loading" :is="iconMap[leadingIcon]" class="w-5 h-5 mr-2" />

        <slot />

        <!-- Trailing Icon -->
        <component v-if="trailingIcon && !loading" :is="iconMap[trailingIcon]" class="w-5 h-5 ml-2" />
    </component>
</template>

<script setup lang="ts">
import { computed, type Component } from 'vue';
import { Link } from '@inertiajs/vue3';
import { cn } from '@/lib/utils';
import iconMap from '@/lib/iconMap';

interface Props {
    variant?: 'primary' | 'secondary' | 'dark' | 'outline';
    size?: 'sm' | 'md' | 'lg';
    block?: boolean;
    disabled?: boolean;
    loading?: boolean;
    target?: '_self' | '_blank';

    as?: 'button' | 'Link' | 'a';

    href?: { type: [String, Object], default: null },       // para Link ou <a>
    method?: string;     // para Link
    preserveScroll?: boolean;
    preserveState?: boolean;
    replace?: boolean;

    type?: 'button' | 'submit' | 'reset';
    leadingIcon?: Component | null;
    trailingIcon?: Component | null;
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'primary',
    size: 'md',
    block: false,
    disabled: false,
    loading: false,
    type: 'button',
    as: 'button',
    leadingIcon: null,
    trailingIcon: null,
    target: '_self',
});

const emit = defineEmits<{
    (e: 'click', event: MouseEvent): void;
}>();

const isButton = computed(() => props.as === 'button');

const onClick = (event: MouseEvent) => {
    if (props.as === 'button' && !props.disabled && !props.loading) {
        emit('click', event);
    }
};

const componentTag = computed(() => {
    if (props.as === 'Link') return Link;
    if (props.as === 'a') return 'a';
    return 'button';
});

const componentProps = computed(() => {
    if (props.as === 'Link') {
        return {
            href: props.href,
            method: props.method,
            preserveScroll: props.preserveScroll,
            preserveState: props.preserveState,
            replace: props.replace,
        };
    }

    if (props.as === 'a') {
        return {
            href: props.href,
            target: props.target,
        };
    }

    return {
        type: props.type,
    };
});

const sizeClasses = {
    sm: 'px-3 py-1.5 text-sm',
    md: 'px-4 py-2 text-sm',
    lg: 'px-5 py-3 text-base',
};

const variantClasses = {
    primary:
        'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all cursor-pointer',
    secondary:
        'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 cursor-pointer',
    dark: 'bg-slate-900 text-white hover:bg-slate-800 cursor-pointer',
    outline:
        'bg-transparent border border-slate-400 text-slate-400 hover:border-emerald-500 hover:text-emerald-600 cursor-pointer',
    pill:
        'px-5 py-2.5 rounded-full bg-slate-900 text-white text-sm font-medium ' +
        'hover:bg-slate-800 shadow-lg hover:shadow-xl flex items-center gap-2 cursor-pointer',
    ghost:
        'text-sm font-medium text-slate-700 hover:text-slate-900 cursor-pointer',
};

const computedClasses = computed(() => {
    return cn(
        'rounded-lg font-medium inline-flex items-center justify-center transition-all duration-200',
        sizeClasses[props.size],
        variantClasses[props.variant],
        props.block && 'w-full',
        (props.disabled || props.loading) && 'opacity-50 cursor-not-allowed'
    );
});
</script>
