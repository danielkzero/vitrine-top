// resources/js/config/routes.ts
export const routes = {
    painel: {
        index: '/painel', // use o nome real da rota base (ajuste se for /painel)
        pages: {
            index: '/painel/pages',
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
        subscriptions: {
            index: '/painel/subscriptions',
        },
        payments: {
            index: '/painel/payments',
        },
        settings: '/painel/settings',
    },
};
