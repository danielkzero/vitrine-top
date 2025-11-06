<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Dashboard\BaseController;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends BaseController
{
    /**
     * GET /dashboard/orders
     */
    public function index(Request $request)
    {
        $orders = Order::where('user_id', $this->user->id)
            ->with('product:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        // Retorno Inertia ou JSON, conforme o tipo de requisição
        if ($request->wantsJson()) {
            return $this->json(['orders' => $orders]);
        }

        return Inertia::render('Dashboard/Orders/Index', [
            'orders' => $orders,
        ]);
    }

    /**
     * POST /dashboard/orders
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['nullable', 'exists:products,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
        ]);

        $data['user_id'] = $this->user->id;

        $order = Order::create($data);

        return $this->json([
            'message' => 'Pedido criado com sucesso.',
            'order' => $order->load('product'),
        ], 201);
    }

    /**
     * GET /dashboard/orders/{order}
     */
    public function show(Order $order)
    {
        $this->authorizeOwnership($order);

        return $this->json(['order' => $order->load('product')]);
    }

    /**
     * PUT/PATCH /dashboard/orders/{order}
     */
    public function update(Request $request, Order $order)
    {
        $this->authorizeOwnership($order);

        $data = $request->validate([
            'product_id' => ['nullable', 'exists:products,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
        ]);

        $order->update($data);

        return $this->json([
            'message' => 'Pedido atualizado com sucesso.',
            'order' => $order->load('product'),
        ]);
    }

    /**
     * DELETE /dashboard/orders/{order}
     */
    public function destroy(Order $order)
    {
        $this->authorizeOwnership($order);

        $order->delete();

        return $this->json(['message' => 'Pedido removido com sucesso.']);
    }

    /**
     * Helper para verificar se o recurso pertence ao usuário logado.
     */
    protected function authorizeOwnership(Order $order)
    {
        if ($order->user_id !== $this->user->id) {
            abort(403, 'Este pedido não pertence ao usuário autenticado.');
        }
    }
}
