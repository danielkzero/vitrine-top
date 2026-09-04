// resources/js/config/routes.ts
export const routes = {
    painel: {
        index: '/painel', // use o nome real da rota base (ajuste se for /painel)
        pages: {
            index: '/painel/pages',
            create: '/painel/pages/create',
            edit: (id: number | string) => `/painel/pages/edit/${id}`,
            show: (id: number | string) => `/painel/pages/${id}`,
            store: '/painel/pages'
        },
        categories: {
            index: '/painel/categories',
            create: '/painel/categories/create',
            edit: (id: number | string) => `/painel/categories/edit/${id}`,
        },
        reviews: {
            index: '/painel/reviews',
        },
        orders: {
            index: '/painel/pedidos',
        },
        customers: {
            index: '/painel/clientes',
        },
        subscriptions: {
            index: '/painel/subscriptions',
        },
        payments: {
            index: '/painel/payments',
        },
        billing: {
            index: '/painel/cobranca',
            required: '/painel/assinatura',
        },
        settings: '/painel/settings',
        banners: {
            index: '/painel/banners'
        }
    },
};
