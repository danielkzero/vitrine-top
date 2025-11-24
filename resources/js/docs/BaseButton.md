# Documentação do BaseButton

## Visão Geral
`BaseButton` é o componente unificado de botões utilizado em toda a aplicação. Ele fornece estilização consistente, renderização dinâmica (button, link, anchor), suporte a ícones e múltiplas variantes visuais.

---

## Recursos

### Variantes Visuais
- **primary**: Gradiente verde com forte destaque
- **secondary**: Fundo branco com borda sutil
- **dark**: Tom escuro sólido
- **outline**: Transparente com borda cinza
- **ghost**: Transparente com leve destaque ao passar o mouse
- **danger**: Vermelho para ações destrutivas

### Tamanhos
- `sm` — Pequeno
- `md` — Médio (padrão)
- `lg` — Grande

### Comportamento
- `block`: Expande o botão para largura total
- `loading`: Exibe spinner e desativa interação
- `disabled`: Desativa interação e reduz opacidade
- `leadingIcon` / `trailingIcon`: Aceitam componentes Vue para ícones

### Renderização Dinâmica (propriedade `as`)
O componente pode renderizar como diferentes elementos HTML ou Inertia:

| Valor       | Renderiza como | Observações                                     |
|-------------|----------------|--------------------------------------------------|
| `button`    | `<button>`     | Comportamento padrão                             |
| `Link`      | `<Link>`       | Suporta props de navegação do Inertia            |
| `a`         | `<a>`          | Para links externos ou âncoras normais           |

Props suportadas por `<Link>` do Inertia:
- `href`
- `method`
- `preserveScroll`
- `preserveState`
- `replace`

---

## Props

```ts
interface Props {
  variant?: 'primary' | 'secondary' | 'dark' | 'outline' | 'ghost' | 'danger';
  size?: 'sm' | 'md' | 'lg';
  block?: boolean;
  disabled?: boolean;
  loading?: boolean;

  as?: 'button' | 'Link' | 'a';

  href?: string;
  method?: string;
  preserveScroll?: boolean;
  preserveState?: boolean;
  replace?: boolean;

  type?: 'button' | 'submit' | 'reset';
  leadingIcon?: Component | null;
  trailingIcon?: Component | null;
}
```

---

## Exemplos de Uso

### Botão Básico
```vue
<BaseButton variant="primary">Salvar</BaseButton>
```

### Com Ícones
```vue
<BaseButton 
  variant="dark"
  leadingIcon="Home"
>
  Dashboard
</BaseButton>
```

### Botão de Largura Total
```vue
<BaseButton block>Enviar</BaseButton>
```

### Estado de Carregamento
```vue
<BaseButton loading>Carregando…</BaseButton>
```

### Link do Inertia
```vue
<BaseButton
  as="Link"
  :href="route('painel')"
  leadingIcon="ArrowRight"
>
  Ir para o painel
</BaseButton>
```

### Link Externo
```vue
<BaseButton as="a" href="https://google.com" target="_blank">
  Abrir Google
</BaseButton>
```

### Ação Perigosa
```vue
<BaseButton variant="danger" trailingIcon="Trash">
  Excluir item
</BaseButton>
```

---

## Notas
- Quando `loading = true`, o botão automaticamente desativa eventos de clique.
- Ícones devem ser componentes Vue (geralmente obtidos via `getIcon()` do `iconMap`).
- Variantes são totalmente customizáveis e podem ser estendidas.

---

## Boas Práticas
- Use `variant="danger"` **somente** para ações irreversíveis.
- Use `as="Link"` sempre que navegar entre páginas via Inertia.
- Para ações assíncronas: combine `loading` com `await` na função disparada.

---

Documentação mantida por **Daniel**.