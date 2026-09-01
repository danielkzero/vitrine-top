<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use App\Services\PlanLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class PageController extends Controller
{
    public function __construct(private readonly PlanLimitService $planLimitService) {}

    /**
     * Lista todas as paginas do usuario autenticado.
     */
    public function index()
    {
        $user = auth()->user();

        Page::ensureDefaultPages($user->id);

        $pages = Page::where('user_id', auth()->id())
            ->withCount('visits')
            ->ordered()
            ->paginate(10)
            ->through(fn ($page) => [
                'id' => $page->id,
                'key' => $page->key,
                'title' => $page->title,
                'content' => $page->content,
                'icon' => $page->icon,
                'is_active' => $page->is_active,
                'order' => $page->order,
                'public_url' => $page->public_url,
                'type' => $page->type,
                'total_visits' => $page->total_visits,
                'unique_visits' => $page->visits_count,
            ]);

        $avaliacoes = Review::where('user_id', auth()->id())->get();

        $produtos = Product::with('images')
            ->where('user_id', auth()->id())
            ->get()
            ->map(function ($produto) {
                $produto->imagensParaExcluir = [];

                return $produto;
            });

        $categorias = Category::where('user_id', auth()->id())->get();

        return Inertia::render('Dashboard/Pages/Index', [
            'pages' => $pages,
            'avaliacoes' => $avaliacoes,
            'produtos' => $produtos,
            'categorias' => $categorias,
        ]);
    }

    /**
     * Exibe o formulario de criacao.
     */
    public function create()
    {
        return Inertia::render('Dashboard/Pages/Create');
    }

    /**
     * Armazena uma nova pagina.
     */
    public function store(Request $request)
    {
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
            'catalog_mode' => 'nullable|in:store,affiliate,presell,showcase,hybrid',
        ]);

        $data['user_id'] = auth()->id();

        Page::create($data);

        return redirect()
            ->route('painel.pages.index')
            ->with('success', 'Página criada com sucesso!');
    }

    /**
     * Exibe o formulario de edicao.
     */
    public function edit(string $key)
    {
        $page = Page::where('key', $key)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $avaliacoes = [];
        if ($page->type === 'reviews') {
            $avaliacoes = Review::where('user_id', auth()->id())->get();
        }

        $produtos = [];
        $categorias = [];
        if ($page->type === 'products') {
            $produtos = Product::with('images')->where('user_id', auth()->id())->get();
            $categorias = Category::where('user_id', auth()->id())->get();
        }

        return inertia('Dashboard/Pages/Edit', [
            'page' => $page,
            'avaliacoes' => $avaliacoes,
            'produtos' => $produtos,
            'categorias' => $categorias,
        ]);
    }

    /**
     * Atualiza uma pagina existente.
     */
    public function update(Request $request, string $key)
    {
        $page = Page::where('key', $key)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $categorias = $request->categorias ? json_decode($request->categorias, true) : [];
        $produtos = $request->produtos ? json_decode($request->produtos, true) : [];
        $pageData = json_decode($request->page, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($pageData)) {
            return back()->withErrors(['page' => 'Formato invalido dos dados enviados.']);
        }

        validator(['products' => $produtos], [
            'products' => 'array',
            'products.*.conversion_type' => 'nullable|in:cart,external,whatsapp,lead',
            'products.*.external_url' => 'nullable|url|max:2048',
            'products.*.cta_label' => 'nullable|string|max:80',
        ])->validate();

        $newProducts = collect($produtos)
            ->filter(fn ($product) => empty($product['id']) && ! empty($product['name']) && isset($product['price']))
            ->count();
        $existingProductsCount = Product::where('user_id', auth()->id())->count();

        try {
            $this->planLimitService->ensureCanAddProducts(auth()->user(), $existingProductsCount, $newProducts);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        foreach ($produtos as $product) {
            if (! isset($product['images']) || ! is_array($product['images'])) {
                continue;
            }

            try {
                $this->planLimitService->ensureProductImagesWithinLimit(auth()->user(), count($product['images']));
            } catch (ValidationException $e) {
                return back()->withErrors($e->errors());
            }
        }

        if (($pageData['type'] ?? null) === 'gallery') {
            $galleryImagesCount = is_array($pageData['content'] ?? null) ? count($pageData['content']) : 0;

            try {
                $this->planLimitService->ensureGalleryWithinLimit(auth()->user(), $galleryImagesCount);
            } catch (ValidationException $e) {
                return back()->withErrors($e->errors());
            }
        }

        $validatedPage = validator(['page' => $pageData], [
            'page.title' => 'required|string|max:255',
            'page.icon' => 'nullable|string|max:100',
            'page.is_active' => 'boolean',
            'page.order' => 'nullable|integer',
            'page.type' => 'required|in:products,reviews,links,simple',
            'page.catalog_mode' => 'nullable|in:store,affiliate,presell,showcase,hybrid',
            'page.content' => 'nullable|string',
            'page.cover_image' => 'nullable|string|max:255',
            'page.seo_title' => 'nullable|string|max:255',
            'page.seo_description' => 'nullable|string|max:255',
        ])->validate();

        $categoriasPersistidas = DB::transaction(function () use ($validatedPage, $page, $categorias, $produtos, $request) {
            $page->update($validatedPage['page']);

            $categoriasPersistidas = [];
            foreach ($categorias as $cat) {
                if (empty($cat['name'])) {
                    continue;
                }

                if (! isset($cat['id'])) {
                    $categoria = Category::create([
                        'user_id' => auth()->id(),
                        'name' => $cat['name'],
                    ]);
                    $categoriasPersistidas[] = $categoria;

                    continue;
                }

                Category::where('user_id', auth()->id())
                    ->where('id', $cat['id'])
                    ->update(['name' => $cat['name']]);

                $categoriaAtualizada = Category::where('user_id', auth()->id())
                    ->where('id', $cat['id'])
                    ->first();

                if ($categoriaAtualizada) {
                    $categoriasPersistidas[] = $categoriaAtualizada;
                }
            }

            $uploadedImages = $request->file('produtos_images', []);
            $fileIndex = 0;
            $plan = $this->planLimitService->getPlanForUser(auth()->user());
            $productImagesLimit = $plan->product_images_limit;

            foreach ($produtos as $p) {
                if (empty($p['name']) || ! isset($p['price'])) {
                    continue;
                }

                $categoryId = $p['category_id'] ?? null;
                if ($categoryId) {
                    $categoryId = Category::where('user_id', auth()->id())
                        ->where('id', $categoryId)
                        ->value('id');
                }

                $productPayload = [
                    'user_id' => auth()->id(),
                    'name' => $p['name'],
                    'price' => $p['price'],
                    'discount_price' => $p['discount_price'] ?? null,
                    'stock' => $p['stock'] ?? 0,
                    'description' => $p['description'] ?? null,
                    'featured' => $p['featured'] ?? false,
                    'is_public' => $p['is_public'] ?? true,
                    'conversion_type' => $p['conversion_type'] ?? 'cart',
                    'external_url' => $p['external_url'] ?? null,
                    'cta_label' => $p['cta_label'] ?? null,
                    'category_id' => $categoryId,
                ];

                if (! isset($p['id'])) {
                    $produto = Product::create($productPayload);
                    $productId = $produto->id;
                } else {
                    Product::where('user_id', auth()->id())
                        ->where('id', $p['id'])
                        ->update($productPayload);
                    $productId = $p['id'];
                }

                ProductImage::whereHas('product', function ($query) {
                    $query->where('user_id', auth()->id());
                })->where('product_id', $productId)->delete();

                if (! isset($p['images']) || ! is_array($p['images'])) {
                    continue;
                }

                $images = $p['images'];
                if ($productImagesLimit !== null) {
                    $images = array_slice($images, 0, $productImagesLimit);
                }

                foreach ($images as $img) {
                    $imagePath = $img['image_path'] ?? null;

                    if (isset($uploadedImages[$fileIndex])) {
                        $imagePath = '/storage/'.$uploadedImages[$fileIndex]->store('products', 'public_direct');
                        $fileIndex++;
                    }

                    if (! $imagePath) {
                        continue;
                    }

                    ProductImage::create([
                        'product_id' => $productId,
                        'image_path' => $imagePath,
                        'is_cover' => $img['is_cover'] ?? false,
                    ]);
                }
            }

            return $categoriasPersistidas;
        });

        return redirect()
            ->back()
            ->with([
                'success' => 'Página atualizada com sucesso!',
                'categorias' => $categoriasPersistidas,
            ]);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'pages' => 'required|array',
            'pages.*.id' => [
                'required',
                'integer',
                Rule::exists('pages', 'id')->where(fn ($query) => $query->where('user_id', auth()->id())),
            ],
            'pages.*.order' => 'required|integer',
        ]);

        foreach ($request->pages as $p) {
            Page::where('id', $p['id'])
                ->where('user_id', auth()->id())
                ->update(['order' => $p['order']]);
        }

        return back()->with('success', 'Ordem atualizada');
    }

    /**
     * Remove uma pagina.
     */
    public function destroy(Page $page)
    {
        $this->authorizeAccess($page);

        $page->delete();

        return back()->with('success', 'Página removida com sucesso!');
    }

    /**
     * Exibe uma pagina publica (fora do dashboard).
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
     * Protege o acesso de outros usuarios.
     */
    private function authorizeAccess(Page $page)
    {
        $user = auth()->user();
        abort_if($page->user_id !== $user->id, 403);
    }
}
