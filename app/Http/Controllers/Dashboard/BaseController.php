<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BaseController extends Controller
{
    protected $user;

    public function __construct()
    {
        // Middleware de autenticação por request
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $productsCount = Product::where(['user_id' => $this->user->id])->count();
        $activeProducts = Product::where(['is_public' => true, 'user_id' => $this->user->id])->count();
        $totalProductImage = ProductImage::whereHas('product', function ($q) {
            $q->where('user_id', $this->user->id);
        })->count();
        $totalRevenue = Order::where(['status' => 'completed', 'user_id' => $this->user->id])->sum('total');
        $recentOrders = Order::with('user')->latest()->take(5)->get();

        return Inertia::render('Painel', [
            'user' => $this->user,
            'stats' => [
                'products' => [
                    'total' => $productsCount,
                    'imagesCount' => $totalProductImage
                ],
                'revenue' => [
                    'total' => $totalRevenue,
                    'count' => Order::count(),
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

    protected function error(string $message = 'Erro', int $status = 400, array $meta = [])
    {
        return response()->json(array_merge(['message' => $message], $meta), $status);
    }
}
