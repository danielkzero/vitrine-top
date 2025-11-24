<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Order;
use App\Models\Category;
use App\Models\Page;
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
        $productsCount = Product::count();
        $activeProducts = Product::where('is_public', true)->count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $totalRevenue = Order::where('status', 'completed')->sum('total');
        $recentOrders = Order::with('user')->latest()->take(5)->get();

        return Inertia::render('Painel', [
            'user' => $this->user,
            'stats' => [
                'products' => [
                    'active' => $activeProducts,
                    'total' => $productsCount,
                ],
                'orders' => [
                    'pending' => $pendingOrders,
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
