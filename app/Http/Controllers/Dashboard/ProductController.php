<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Dashboard\BaseController;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProductController extends BaseController
{
    /**
     * GET /dashboard/products
     */
    public function index(Request $request)
    {
        $products = Product::with('images')->where('user_id', $this->user->id)
            ->with('category:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        // Se quiser usar Inertia (exemplo futuramente)
        if ($request->wantsJson()) {
            return $this->json(['products' => $products]);
        }

        return Inertia::render('Dashboard/Products/Index', [
            'products' => $products
        ]);
    }

    /**
     * POST /dashboard/products
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'code' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'is_public' => ['sometimes', 'boolean'],
            'allow_whatsapp' => ['sometimes', 'boolean'],
            'cover_image' => ['nullable', 'string', 'max:255'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:255'],
        ]);

        $data['user_id'] = $this->user->id;

        $product = Product::create($data);

        return $this->json([
            'message' => 'Produto criado com sucesso.',
            'product' => $product,
        ], 201);
    }

    /**
     * GET /dashboard/products/{product}
     */
    public function show(Product $product)
    {
        $this->authorizeOwnership($product);

        return $this->json(['product' => $product->load('category')]);
    }

    /**
     * PUT/PATCH /dashboard/products/{product}
     */
    public function update(Request $request, Product $product)
    {
        $this->authorizeOwnership($product);

        $data = $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'code' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'is_public' => ['sometimes', 'boolean'],
            'allow_whatsapp' => ['sometimes', 'boolean'],
            'cover_image' => ['nullable', 'string', 'max:255'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:255'],
        ]);

        $product->update($data);

        return $this->json([
            'message' => 'Produto atualizado com sucesso.',
            'product' => $product,
        ]);
    }

    /**
     * DELETE /dashboard/products/{product}
     */
    public function destroy(Product $product)
    {
        $this->authorizeOwnership($product);

        $product->delete();

        return $this->json(['message' => 'Produto removido com sucesso.']);
    }

    /**
     * Helper para verificar se o recurso pertence ao usuário logado.
     */
    protected function authorizeOwnership(Product $product)
    {
        if ($product->user_id !== $this->user->id) {
            abort(403, 'Este produto não pertence ao usuário autenticado.');
        }
    }
}
