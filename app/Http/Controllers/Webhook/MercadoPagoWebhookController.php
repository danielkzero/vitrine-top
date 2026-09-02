<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PlatformSetting;
use App\Services\Payments\MercadoPagoGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MercadoPago\Exceptions\InvalidWebhookSignatureException;
use MercadoPago\Webhook\WebhookSignatureValidator;
use Throwable;

class MercadoPagoWebhookController extends Controller
{
    public function __invoke(Request $request, MercadoPagoGateway $gateway)
    {
        $webhookSecret = PlatformSetting::valueFor('mercado_pago_webhook_secret', config('services.mercado_pago.webhook_secret'));
        if ($webhookSecret) {
            try {
                WebhookSignatureValidator::validate(
                    $request->header('x-signature'),
                    $request->header('x-request-id'),
                    (string) ($request->query('data.id') ?: $request->input('data.id')),
                    $webhookSecret,
                    300,
                );
            } catch (InvalidWebhookSignatureException) {
                return response()->json(['received' => false], 401);
            }
        }

        $remoteId = (int) ($request->input('data.id') ?: $request->input('id'));
        if ($remoteId <= 0) {
            return response()->json(['received' => true]);
        }

        try {
            $remotePayment = $gateway->getPayment($remoteId);
            $payment = Payment::where('transaction_id', (string) $remoteId)->first();
            if (! $payment) {
                return response()->json(['received' => true]);
            }

            $status = match ($remotePayment->status) {
                'approved' => 'paid',
                'refunded', 'charged_back' => 'refunded',
                'rejected', 'cancelled' => 'failed',
                default => 'pending',
            };

            $payment->update([
                'status' => $status,
                'paid_at' => $status === 'paid' ? ($payment->paid_at ?: now()) : null,
                'refunded_at' => $status === 'refunded' ? now() : null,
                'details' => array_merge($payment->details ?? [], ['remote_status' => $remotePayment->status, 'remote_status_detail' => $remotePayment->status_detail]),
            ]);

            if ($status === 'paid') {
                $payment->markAsPaid();
            }
        } catch (Throwable $exception) {
            Log::error('Falha ao processar webhook do Mercado Pago.', ['payment_id' => $remoteId, 'exception' => $exception->getMessage()]);

            return response()->json(['received' => false], 500);
        }

        return response()->json(['received' => true]);
    }
}
