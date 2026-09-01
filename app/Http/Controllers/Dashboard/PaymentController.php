<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PaymentController extends BaseController
{
    private const METHODS = ['pix', 'credit_card', 'boleto'];

    /**
     * GET /dashboard/payments
     */
    public function index(Request $request)
    {
        $payments = Payment::where('user_id', $this->user->id)
            ->with('subscription:id,user_id,plan,status')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($request->wantsJson()) {
            return $this->json(['payments' => $payments]);
        }

        return Inertia::render('Dashboard/Payments/Index', [
            'payments' => $payments,
        ]);
    }

    /**
     * GET /dashboard/payments/{payment}
     */
    public function show(Payment $payment)
    {
        $this->authorizeOwnership($payment);

        return $this->json(['payment' => $payment->load('subscription')]);
    }

    /**
     * POST /dashboard/payments
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'subscription_id' => [
                'nullable',
                Rule::exists('subscriptions', 'id')->where(fn($query) => $query->where('user_id', $this->user->id)),
            ],
            'transaction_id' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'method' => ['required', Rule::in(self::METHODS)],
            'status' => ['nullable', 'in:pending,paid,failed,refunded'],
            'details' => ['nullable', 'array'],
        ]);

        $data['user_id'] = $this->user->id;
        $data['status'] = $data['status'] ?? 'pending';
        $data['currency'] = strtoupper($data['currency'] ?? 'BRL');

        $payment = Payment::create($data);

        return $this->json([
            'message' => 'Pagamento registrado com sucesso.',
            'payment' => $payment->load('subscription'),
        ], 201);
    }

    /**
     * PUT/PATCH /dashboard/payments/{payment}
     */
    public function update(Request $request, Payment $payment)
    {
        $this->authorizeOwnership($payment);

        $data = $request->validate([
            'status' => ['required', 'in:pending,paid,failed,refunded'],
        ]);

        $payment->update($data);

        return $this->json([
            'message' => 'Status do pagamento atualizado.',
            'payment' => $payment,
        ]);
    }

    /**
     * DELETE /dashboard/payments/{payment}
     */
    public function destroy(Payment $payment)
    {
        $this->authorizeOwnership($payment);

        $payment->delete();

        return $this->json(['message' => 'Pagamento removido com sucesso.']);
    }

    /**
     * GET /checkout
     */
    public function checkout(Request $request)
    {
        return Inertia::render('Dashboard/Payments/Index', [
            'payments' => [],
        ]);
    }

    /**
     * POST /checkout
     */
    public function process(Request $request)
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'subscription_id' => [
                'required',
                Rule::exists('subscriptions', 'id')->where(fn($query) => $query->where('user_id', $request->user()->id)),
            ],
            'method' => ['required', Rule::in(self::METHODS)],
        ]);

        $subscription = Subscription::where('user_id', $request->user()->id)
            ->findOrFail($data['subscription_id']);

        $payment = Payment::create([
            'user_id' => $request->user()->id,
            'subscription_id' => $subscription->id,
            'amount' => $subscription->price,
            'currency' => 'BRL',
            'method' => $data['method'],
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $payment->markAsPaid();

        return redirect()->route('checkout.success');
    }

    /**
     * GET /checkout/sucesso
     */
    public function success()
    {
        return redirect()
            ->route('painel.payments.index')
            ->with('success', 'Checkout processado com sucesso.');
    }

    /**
     * GET /checkout/erro
     */
    public function error()
    {
        return redirect()
            ->route('painel.payments.index')
            ->withErrors(['payment' => 'Falha ao processar checkout.']);
    }

    protected function authorizeOwnership(Payment $payment): void
    {
        if ($payment->user_id !== $this->user->id) {
            abort(403, 'Este pagamento não pertence ao usuário autenticado.');
        }
    }
}
