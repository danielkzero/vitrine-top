<h1>Novo pedido na loja</h1>
<p>Pedido {{ $order->order_number }} criado.</p>
<p>Cliente: {{ $order->customer?->name }}</p>
<p>Total: R$ {{ number_format((float) $order->total, 2, ',', '.') }}</p>
