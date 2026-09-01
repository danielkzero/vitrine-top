<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Order;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\PlanLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BaseController extends Controller
{
    protected $user;
    protected PlanLimitService $basePlanLimitService;

    public function __construct(?PlanLimitService $planLimitService = null)
    {
        $this->basePlanLimitService = $planLimitService ?? app(PlanLimitService::class);

        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $productsCount = Product::where('user_id', $this->user->id)->count();
        $activeProducts = Product::where('is_public', true)->where('user_id', $this->user->id)->count();
        $totalProductImage = ProductImage::whereHas('product', function ($query) {
            $query->where('user_id', $this->user->id);
        })->count();

        $galleryImagesCount = Page::where('user_id', $this->user->id)
            ->get(['content'])
            ->sum(function (Page $page) {
                $content = $page->content;

                if (is_string($content)) {
                    $decoded = json_decode($content, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $content = $decoded;
                    }
                }

                return is_array($content) ? count($content) : 0;
            });

        $bannersCount = Banner::where('user_id', $this->user->id)->count();
        $totalRevenue = Order::where('user_id', $this->user->id)->sum('total');

        $recentOrders = Order::with('customer:id,name')
            ->where('user_id', $this->user->id)
            ->latest()
            ->take(5)
            ->get();

        $currentPlan = $this->basePlanLimitService->getPlanForUser($this->user);

        return Inertia::render('Painel', [
            'user' => $this->user,
            'stats' => [
                'products' => [
                    'total' => $productsCount,
                    'active' => $activeProducts,
                    'imagesCount' => $totalProductImage,
                ],
                'revenue' => [
                    'total' => $totalRevenue,
                    'count' => Order::where('user_id', $this->user->id)->count(),
                ],
                'resources' => [
                    'products' => [
                        'used' => $productsCount,
                        'limit' => $currentPlan->products_limit,
                    ],
                    'product_images' => [
                        'used' => $totalProductImage,
                        'limit' => $currentPlan->product_images_limit,
                    ],
                    'gallery_images' => [
                        'used' => $galleryImagesCount,
                        'limit' => $currentPlan->gallery_images_limit,
                    ],
                    'banners' => [
                        'used' => $bannersCount,
                        'limit' => $currentPlan->banners_limit,
                    ],
                ],
            ],
            'recentOrders' => $recentOrders,
        ]);
    }

    protected function respond(Request $request, string $view, array $data = [])
    {
        if ($request->wantsJson()) {
            return response()->json($data);
        }

        return Inertia::render($view, $data);
    }

    protected function json($data = [], int $status = 200)
    {
        return response()->json($data, $status);
    }

    protected function jsonError(string $message = 'Erro', int $status = 400, array $meta = [])
    {
        return response()->json(array_merge(['message' => $message], $meta), $status);
    }
}
