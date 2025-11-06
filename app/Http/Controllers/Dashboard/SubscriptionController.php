<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Dashboard\BaseController;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscriptionController extends BaseController
{
    /**
     * GET /dashboard/subscriptions
     */
    public function index(Request $request)
    {
        $subscriptions = Subscription::where('user_id', $this->user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($request->wantsJson()) {
            return $this->json(['subscriptions' => $subscriptions]);
        }

        return Inertia::render('Dashboard/Subscriptions/Index', [
            'subscriptions' => $subscriptions,
        ]);
    }

    /**
     * GET /dashboard/subscriptions/{subscription}
     */
    public function show(Subscription $subscription)
    {
        $this->authorizeOwnership($subscription);

        return $this->json(['subscription' => $subscription]);
    }

    /**
     * POST /dashboard/subscriptions
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'plan' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'billing_period' => ['required', 'in:monthly,annual'],
            'payment_method' => ['nullable', 'string', 'max:50'],
        ]);

        $data['user_id'] = $this->user->id;
        $data['status'] = 'trial';
        $data['trial_ends_at'] = now()->addDays(7);

        $subscription = Subscription::create($data);

        return $this->json([
            'message' => 'Assinatura criada com sucesso.',
            'subscription' => $subscription,
        ], 201);
    }

    /**
     * PUT/PATCH /dashboard/subscriptions/{subscription}
     */
    public function update(Request $request, Subscription $subscription)
    {
        $this->authorizeOwnership($subscription);

        $data = $request->validate([
            'plan' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'billing_period' => ['required', 'in:monthly,annual'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:trial,active,expired,cancelled'],
        ]);

        $subscription->update($data);

        return $this->json([
            'message' => 'Assinatura atualizada com sucesso.',
            'subscription' => $subscription,
        ]);
    }

    /**
     * DELETE /dashboard/subscriptions/{subscription}
     */
    public function destroy(Subscription $subscription)
    {
        $this->authorizeOwnership($subscription);

        $subscription->delete();

        return $this->json(['message' => 'Assinatura removida com sucesso.']);
    }

    /**
     * POST /dashboard/subscriptions/{subscription}/cancel
     */
    public function cancel(Subscription $subscription)
    {
        $this->authorizeOwnership($subscription);

        $subscription->cancel();

        return $this->json(['message' => 'Assinatura cancelada.']);
    }

    protected function authorizeOwnership(Subscription $subscription)
    {
        if ($subscription->user_id !== $this->user->id) {
            abort(403, 'Esta assinatura não pertence ao usuário autenticado.');
        }
    }
}
