<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends BaseController
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search'));

        $customers = Customer::query()
            ->where('user_id', $this->user->id)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('whatsapp', 'like', "%{$search}%");
                });
            })
            ->with(['defaultAddress', 'addresses'])
            ->withCount('orders')
            ->withSum('orders as orders_total', 'total')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Dashboard/Customers/Index', [
            'customers' => $customers,
            'filters' => ['search' => $search],
        ]);
    }
}
