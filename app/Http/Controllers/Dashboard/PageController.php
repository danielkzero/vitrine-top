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
        $this->authorizeAccess($page);

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

        $page->update($data);

        return redirect()->route('dashboard.pages.index')->with('success', 'Página atualizada com sucesso!');
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
