<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PageController extends Controller
{
    /**
     * Lista todas as páginas do usuário autenticado.
     */
    public function index()
    {
        $user = auth()->user();

        Page::ensureDefaultPages($user->id);

        $pages = Page::where('user_id', auth()->id())
            ->ordered()
            ->paginate(10)
            ->through(fn($page) => [
                'id' => $page->id,
                'key' => $page->key,
                'title' => $page->title,
                'content' => $page->content,
                'icon' => $page->icon,
                'is_active' => $page->is_active,
                'order' => $page->order,
                'public_url' => $page->public_url,
                'type' => $page->type,
            ]);

        $avaliacoes = Review::where('user_id', auth()->id())->get();

        $produtos = Product::where('user_id', auth()->id())->get();

        $categorias = Category::where('user_id', auth()->id())->get();

        return Inertia::render('Dashboard/Pages/Index', [
            'pages' => $pages,
            'avaliacoes' => $avaliacoes,
            'produtos' => $produtos,
            'categorias' => $categorias
        ]);
    }

    /**
     * Exibe o formulário de criação.
     */
    public function create()
    {
        return Inertia::render('Dashboard/Pages/Create');
    }

    /**
     * Armazena uma nova página.
     */
    public function store(Request $request)
    {
        // Limite máximo de páginas por usuário
        $maxPages = 6;
        if (Page::where('user_id', auth()->id())->count() >= $maxPages) {
            return redirect()
                ->back()
                ->withErrors(['max' => "Você atingiu o limite máximo de $maxPages páginas."]);
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'icon' => 'nullable|string|max:100',
            'content' => 'nullable|string',
            'cover_image' => 'nullable|string|max:255',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'order' => 'nullable|integer',
            'type' => 'required|in:products,reviews,links,simple',
        ]);

        $data['user_id'] = auth()->id();

        Page::create($data);

        return redirect()
            ->route('dashboard.pages.index')
            ->with('success', 'Página criada com sucesso!');
    }

    /**
     * Exibe o formulário de edição.
     */
    public function edit(string $key)
    {
        // Busca a página pelo campo "key"
        $page = Page::where('key', $key)->firstOrFail();

        // Define dados extras conforme o tipo da página

        $avaliacoes = Review::where('user_id', auth()->id())->get();

        $produtos = Product::where('user_id', auth()->id())->get();

        $categorias = Category::where('user_id', auth()->id())->get();

        return inertia('Dashboard/Pages/Edit', [
            'page' => $page,
            'avaliacoes' => $avaliacoes,
            'produtos' => $produtos,
            'categorias' => $categorias,
        ]);
    }

    /**
     * Atualiza uma página existente.
     */
    public function update(Request $request, Page $page)
    {
        // $this->authorizeOwnership($page);
        $validatedPage = $request->validate([
            'page.title' => 'required|string|max:255',
            'page.icon' => 'nullable|string|max:100',
            'page.content' => 'nullable',
            'page.cover_image' => 'nullable|string|max:255',
            'page.seo_title' => 'nullable|string|max:255',
            'page.seo_description' => 'nullable|string|max:255',
            'page.is_active' => 'boolean',
            'page.order' => 'nullable|integer',
            'page.type' => 'required|string',
        ]);

        // Atualiza a página
        $page->update($validatedPage['page']);

        // ==============================
        // 🔥 CRUD de CATEGORIAS
        // ==============================
        $categorias = $request->input('categorias', []);

        foreach ($categorias as $cat) {
            // Categoria nova
            if (!isset($cat['id'])) {
                Category::create([
                    'user_id' => auth()->id(),
                    'name' => $cat['name']
                ]);
                continue;
            }

            // Atualizar categoria existente
            Category::where('user_id', auth()->id())
                ->where('id', $cat['id'])
                ->update(['name' => $cat['name']]);
        }

        // ==============================
        // 🔥 CRUD de PRODUTOS
        // ==============================
        $produtos = $request->input('produtos', []);

        foreach ($produtos as $p) {
            // ==============================
            // 🔥 NOVO PRODUTO
            // ==============================
            if (!isset($p['id'])) {
                $produto = Product::create([
                    'user_id' => auth()->id(),
                    'name' => $p['name'],
                    'price' => $p['price'],
                    'discount_price' => $p['discount_price'] ?? null,
                    'stock' => $p['stock'] ?? 0,
                    'description' => $p['description'] ?? null,
                    'featured' => $p['featured'] ?? false,
                    'is_public' => $p['is_public'] ?? true,
                    'category_id' => $p['category_id'],
                ]);

                // Salvar ID recém-criado para usar nas imagens
                $p['id'] = $produto->id;
            } else {
                // ==============================
                // 🔥 ATUALIZAR PRODUTO EXISTENTE
                // ==============================
                Product::where('user_id', auth()->id())
                    ->where('id', $p['id'])
                    ->update([
                        'name' => $p['name'],
                        'price' => $p['price'],
                        'discount_price' => $p['discount_price'] ?? null,
                        'stock' => $p['stock'] ?? 0,
                        'description' => $p['description'] ?? null,
                        'featured' => $p['featured'] ?? false,
                        'is_public' => $p['is_public'] ?? true,
                        'category_id' => $p['category_id'],
                    ]);
            }

            // ==============================
            // 🔥 CRUD DAS IMAGENS DO PRODUTO
            // ==============================
            if (isset($p['images']) && is_array($p['images'])) {
                foreach ($p['images'] as $img) {
                    // NOVA IMAGEM
                    if (!isset($img['id'])) {
                        ProductImage::create([
                            'product_id' => $p['id'],
                            'image_path' => $img['image_path'] ?? null,
                            'image_base64' => $img['image_base64'] ?? null,
                            'is_cover' => $img['is_cover'] ?? false,
                        ]);
                        continue;
                    }

                    // ATUALIZAR IMAGEM EXISTENTE
                    ProductImage::where('id', $img['id'])
                        ->where('product_id', $p['id'])
                        ->update([
                            'image_path' => $img['image_path'] ?? null,
                            'image_base64' => $img['image_base64'] ?? null,
                            'is_cover' => $img['is_cover'] ?? false,
                        ]);
                }
            }
        }
        return back()->with('success', 'Página e produtos atualizados com sucesso!');
    }

    /**
     * Remove uma página.
     */
    public function destroy(Page $page)
    {
        $this->authorizeAccess($page);

        $page->delete();

        return back()->with('success', 'Página removida com sucesso!');
    }

    /**
     * Exibe uma página pública (fora do dashboard).
     */
    public function show(string $key)
    {
        $page = Page::where('key', $key)
            ->where('is_active', true)
            ->firstOrFail();

        return Inertia::render('Public/Page', [
            'page' => [
                'title' => $page->title,
                'content' => $page->content,
                'cover_image' => $page->cover_image,
                'seo_title' => $page->seo_title,
                'seo_description' => $page->seo_description,
            ],
        ]);
    }

    /**
     * Protege o acesso de outros usuários.
     */
    private function authorizeAccess(Page $page)
    {
        abort_if($page->user_id !== auth()->id(), 403);
    }
}
