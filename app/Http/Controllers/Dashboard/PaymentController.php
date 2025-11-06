<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Dashboard\BaseController;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentController extends BaseController
{
    /**
     * GET /dashboard/payments
     */
    public function index(Request $request)
    {
        $payments = Payment::where('user_id', $this->user->id)
            ->with('subscription:id,plan,status')
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
            'subscription_id' => ['nullable', 'exists:subscriptions,id'],
            'transaction_id' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'method' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:pending,paid,failed,refunded'],
            'details' => ['nullable', 'array'],
        ]);

        $data['user_id'] = $this->user->id;

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

    protected function authorizeOwnership(Payment $payment)
    {
        if ($payment->user_id !== $this->user->id) {
            abort(403, 'Este pagamento não pertence ao usuário autenticado.');
        }
    }
}
