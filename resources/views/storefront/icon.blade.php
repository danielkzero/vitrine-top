@php($iconName = strtolower((string) ($name ?? 'circle')))
@if(in_array($iconName, ['book', 'catalogo', 'home']))
<svg {!! $attributes ?? '' !!} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V4H6.5A2.5 2.5 0 0 0 4 6.5v13Z"/><path d="M8 7h8M8 11h6"/></svg>
@elseif(in_array($iconName, ['badgeinfo', 'info']))
<svg {!! $attributes ?? '' !!} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/></svg>
@elseif(in_array($iconName, ['link', 'globe']))
<svg {!! $attributes ?? '' !!} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M10 13a5 5 0 0 0 7.1 0l2-2a5 5 0 0 0-7.1-7.1l-1.1 1.1"/><path d="M14 11a5 5 0 0 0-7.1 0l-2 2A5 5 0 0 0 12 20.1l1.1-1.1"/></svg>
@elseif($iconName === 'star')
<svg {!! $attributes ?? '' !!} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 3 2.7 5.5 6 .9-4.4 4.2 1 6-5.3-2.8-5.3 2.8 1-6-4.4-4.2 6-.9L12 3Z"/></svg>
@elseif(in_array($iconName, ['filetext', 'shieldcheck']))
<svg {!! $attributes ?? '' !!} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 3h9l4 4v14H6z"/><path d="M14 3v5h5M9 13h6M9 17h6"/></svg>
@elseif(in_array($iconName, ['shoppingcart', 'cart']))
<svg {!! $attributes ?? '' !!} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/><path d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 2-1.6L21 8H6"/></svg>
@elseif($iconName === 'search')
<svg {!! $attributes ?? '' !!} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
@elseif(in_array($iconName, ['layoutgrid', 'grid']))
<svg {!! $attributes ?? '' !!} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="4" width="6" height="6" rx="1"/><rect x="14" y="4" width="6" height="6" rx="1"/><rect x="4" y="14" width="6" height="6" rx="1"/><rect x="14" y="14" width="6" height="6" rx="1"/></svg>
@elseif($iconName === 'heart')
<svg {!! $attributes ?? '' !!} viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.8"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z"/></svg>
@elseif(in_array($iconName, ['arrowleft', 'back']))
<svg {!! $attributes ?? '' !!} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m15 18-6-6 6-6"/></svg>
@elseif($iconName === 'userstar')
<svg {!! $attributes ?? '' !!} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3"/><path d="M3 20c0-4 2.5-6 6-6 1.3 0 2.4.3 3.3.8M17 13l1 2 2.2.3-1.6 1.6.4 2.1-2-1-2 1 .4-2.1-1.6-1.6L16 15l1-2Z"/></svg>
@else
<svg {!! $attributes ?? '' !!} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="8"/></svg>
@endif
