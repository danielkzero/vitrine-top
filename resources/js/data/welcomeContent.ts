const welcomeContent = {
  hero: {
    prelabel: 'Nova versão 2.0 disponível',
    titleLine1: 'Sua vitrine online',
    titleGradient: 'profissional e vendedora',
    paragraph:
      'Pare de perder vendas pelo WhatsApp. Organize seus produtos em um catálogo digital moderno e receba pedidos organizados.',
    ctaPrimary: 'Começar agora',
    ctaWhatsApp: 'Falar no WhatsApp',
    badges: ['Sem cartão de crédito', 'Setup em 2 min']
  },
  features: [
    {
      icon: 'Smartphone',
      title: 'Mobile First',
      description:
        'Seu catálogo funciona perfeitamente em qualquer celular, tablet ou computador. Leve e rápido.'
    },
    {
      icon: 'Share2',
      title: 'Compartilhamento Fácil',
      description:
        'Envie o link da sua loja pelo WhatsApp, Instagram ou Facebook e receba clientes instantaneamente.'
    },
    {
      icon: 'ShoppingBag',
      title: 'Controle de Estoque',
      description:
        'Gerencie seus produtos, variações de tamanho e cor, e disponibilidade em tempo real.'
    },
    {
      icon: 'MessageCircle',
      title: 'Pedidos no WhatsApp',
      description:
        'O cliente monta o carrinho e o pedido chega pronto no seu WhatsApp. Sem taxas de comissão.'
    },
    {
      icon: 'BarChart3',
      title: 'Painel de Vendas',
      description: 'Acompanhe acessos, produtos mais visitados e volume de vendas.'
    },
    {
      icon: 'Store',
      title: 'Personalização',
      description:
        'Adicione sua logo, banner, cores da marca e informações de contato.'
    }
  ],
  steps: [
    { icon: 'Zap', number: '1', title: 'Cadastre-se', description: 'Crie sua conta gratuita em segundos e configure o perfil da sua loja.' },
    { icon: 'Camera', number: '2', title: 'Adicione Produtos', description: 'Cadastre fotos, preços e descrições dos seus produtos ou serviços.' },
    { icon: 'Share2', number: '3', title: 'Divulgue', description: 'Compartilhe seu link exclusivo nas redes sociais e WhatsApp.' },
    { icon: 'Send', number: '4', title: 'Venda', description: 'Receba pedidos organizados diretamente no seu WhatsApp.' }
  ],
  categories: {
    introTitle: 'Exponha seus produtos e serviços, para vários segmentos',
    introText: 'Deixe sua vitrine online para todos os seus clientes. Alcance novos públicos e organize seus serviços por categoria para facilitar a descoberta.',
    items: [
      { name: 'Imobiliária', icon: 'Home' },
      { name: 'Estética', icon: 'Scissors' },
      { name: 'Hotelaria', icon: 'Bed' },
      { name: 'Academias', icon: 'Dumbbell' },
      { name: 'Contabilidade', icon: 'Calculator' },
      { name: 'Barbearia', icon: 'Scissors' },
      { name: 'Fotógrafo', icon: 'Camera' },
      { name: 'Nutricionista', icon: 'Leaf' },
      { name: 'Dentista', icon: 'Smile' },
      { name: 'Óticas', icon: 'Eye' },
      { name: 'PetShop', icon: 'Heart' },
      { name: 'Floricultura', icon: 'Flower' }
    ]
  },
  plans: {
    monthly: {
      name: 'Mensal',
      price: 'R$ 24,90',
      per: '/mês',
      bullets: ['30 produtos', 'Vendas ilimitadas', 'Suporte por e-mail', 'Sem fidelidade, cancele quando quiser']
    },
    annual: {
      name: 'Anual',
      price: 'R$ 19,90',
      per: '/mês',
      note: 'R$ 238,80 à vista no PIX ou 12x no cartão (consulte taxas)',
      bullets: ['Tudo do plano mensal', 'Suporte prioritário WhatsApp', 'Selos de verificação na loja'],
      badge: 'MAIS POPULAR'
    }
  }
};

export default welcomeContent;
