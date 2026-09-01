<h1>Pedido recebido</h1>
<p>Seu pedido {{ $order->order_number }} foi criado com sucesso.</p>
<p>Total: R$ {{ number_format((float) $order->total, 2, ',', '.') }}</p>
