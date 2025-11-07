<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
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
        return Inertia::render('Dashboard', [
            'user' => $this->user,
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
