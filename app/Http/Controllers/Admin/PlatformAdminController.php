<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\ProductImage;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PlatformAdminController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $status = trim((string) $request->string('status'));

        $clientsQuery = User::query()
            ->where('is_admin', false)
            ->with(['subscription.planModel'])
            ->withCount(['products', 'pages', 'banners', 'categories', 'customers'])
            ->latest('id');

        if ($search !== '') {
            $clientsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('business_name', 'like', "%{$search}%");
            });
        }

        if ($status !== '') {
            $clientsQuery->whereHas('subscription', fn ($query) => $query->where('status', $status));
        }

        $clients = $clientsQuery->paginate(15)->withQueryString();
        $imageCounts = ProductImage::query()
            ->join('products', 'products.id', '=', 'product_images.product_id')
            ->whereIn('products.user_id', $clients->getCollection()->pluck('id'))
            ->groupBy('products.user_id')
            ->selectRaw('products.user_id, count(*) as total')
            ->pluck('total', 'products.user_id');

        $clients->through(function (User $user) use ($imageCounts) {
            $subscription = $user->subscription;
            $plan = $subscription?->planModel;

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'business_name' => $user->business_name,
                'slug' => $user->slug,
                'is_active' => $user->is_active,
                'created_at' => $user->created_at,
                'usage' => [
                    'products' => $user->products_count,
                    'product_images' => (int) ($imageCounts[$user->id] ?? 0),
                    'pages' => $user->pages_count,
                    'banners' => $user->banners_count,
                    'categories' => $user->categories_count,
                    'customers' => $user->customers_count,
                ],
                'subscription' => $subscription ? [
                    'id' => $subscription->id,
                    'plan_id' => $subscription->plan_id,
                    'plan_name' => $subscription->custom_plan_name ?: $plan?->name ?: $subscription->plan,
                    'custom_plan_name' => $subscription->custom_plan_name,
                    'price' => (float) $subscription->price,
                    'billing_period' => $subscription->billing_period->value,
                    'status' => $subscription->status->value,
                    'trial_ends_at' => $subscription->trial_ends_at?->toDateString(),
                    'next_billing_at' => $subscription->next_billing_at?->toDateString(),
                    'days_left' => $subscription->days_left,
                    'status_changed_at' => $subscription->status_changed_at?->toISOString(),
                    'days_in_status' => (int) ($subscription->status_changed_at?->diffInDays(now()) ?? 0),
                    'limits' => [
                        'products' => $subscription->custom_products_limit ?? $plan?->products_limit,
                        'product_images' => $subscription->custom_plan_name
                            ? $subscription->custom_product_images_limit
                            : $plan?->product_images_limit,
                        'gallery_images' => $subscription->custom_gallery_images_limit ?? $plan?->gallery_images_limit,
                        'banners' => $subscription->custom_banners_limit ?? $plan?->banners_limit,
                    ],
                ] : null,
            ];
        });

        $payments = Payment::query()
            ->with('user:id,name,email,business_name')
            ->latest()
            ->limit(30)
            ->get()
            ->map(fn (Payment $payment) => [
                'id' => $payment->id,
                'customer' => $payment->user?->business_name ?: $payment->user?->name,
                'email' => $payment->user?->email,
                'amount' => (float) $payment->amount,
                'method' => $payment->method,
                'status' => $payment->status,
                'paid_at' => $payment->paid_at?->toISOString(),
                'created_at' => $payment->created_at?->toISOString(),
            ]);

        return Inertia::render('Admin/Index', [
            'stats' => [
                'clients' => User::where('is_admin', false)->count(),
                'active' => Subscription::active()->count(),
                'trial' => Subscription::trial()->whereDate('trial_ends_at', '>=', today())->count(),
                'expiring_trials' => Subscription::trial()->whereBetween('trial_ends_at', [today(), today()->addDays(7)])->count(),
                'paid_revenue' => (float) Payment::paid()->sum('amount'),
                'paid_payments' => Payment::paid()->count(),
                'pending_payments' => Payment::pending()->count(),
                'expired' => Subscription::expired()->count(),
                'cancelled' => Subscription::where('status', SubscriptionStatus::CANCELLED->value)->count(),
            ],
            'clients' => $clients,
            'payments' => $payments,
            'plans' => Plan::where('is_active', true)->orderBy('monthly_price')->get()->map(fn (Plan $plan) => [
                'id' => $plan->id,
                'name' => $plan->name,
                'price' => (float) $plan->monthly_price,
                'products_limit' => $plan->products_limit,
                'product_images_limit' => $plan->product_images_limit,
                'gallery_images_limit' => $plan->gallery_images_limit,
                'banners_limit' => $plan->banners_limit,
            ]),
            'filters' => compact('search', 'status'),
        ]);
    }

    public function updateSubscription(Request $request, User $user)
    {
        abort_if($user->is_admin, 422, 'Não é possível alterar o plano de um administrador.');

        $data = $request->validate([
            'plan_id' => ['required', Rule::exists('plans', 'id')->where('is_active', true)],
            'custom_plan_name' => ['nullable', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'billing_period' => ['required', Rule::in(['monthly', 'annual'])],
            'status' => ['required', Rule::enum(SubscriptionStatus::class)],
            'trial_ends_at' => ['nullable', 'date'],
            'next_billing_at' => ['nullable', 'date'],
            'custom_products_limit' => ['nullable', 'integer', 'min:0'],
            'custom_product_images_limit' => ['nullable', 'integer', 'min:0'],
            'custom_gallery_images_limit' => ['nullable', 'integer', 'min:0'],
            'custom_banners_limit' => ['nullable', 'integer', 'min:0'],
        ]);

        $plan = Plan::findOrFail($data['plan_id']);
        $subscription = $user->subscription()->firstOrNew();
        $subscription->fill($data + [
            'plan' => $plan->code->value,
            'trial_starts_at' => $data['status'] === SubscriptionStatus::TRIAL->value
                ? ($subscription->trial_starts_at ?: now())
                : $subscription->trial_starts_at,
        ])->save();

        return back()->with('success', 'Plano do cliente atualizado com sucesso.');
    }
}
