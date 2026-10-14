<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Services\Payments\PaymentGateway;
use App\Services\Payments\PaymentService;
use DomainException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * POST /webhooks/razorpay — server-to-server confirmation from Razorpay.
 * The raw body must match the X-Razorpay-Signature HMAC (webhook secret)
 * before anything in it is trusted. Replays are ignored via the event id.
 */
class RazorpayWebhookController extends Controller
{
    public function __invoke(Request $request, PaymentGateway $gateway, PaymentService $payments): JsonResponse
    {
        $body = $request->getContent();

        if (! $gateway->verifyWebhook($body, (string) $request->header('X-Razorpay-Signature'))) {
            Log::warning('Rejected Razorpay webhook with invalid signature', ['ip' => $request->ip()]);

            return response()->json(['error' => 'invalid signature'], 400);
        }

        $event = (string) $request->json('event');
        $entity = (array) $request->json('payload.payment.entity', []);
        $orderId = $entity['order_id'] ?? $request->json('payload.order.entity.id');
        $payment = $orderId ? Payment::where('provider_order_id', $orderId)->first() : null;

        try {
            PaymentEvent::create([
                'payment_id' => $payment?->id,
                'provider' => 'razorpay',
                'event' => $event,
                'provider_event_id' => $request->header('X-Razorpay-Event-Id') ?: null,
                'signature_valid' => true,
                'payload' => ['payment_id' => $entity['id'] ?? null, 'order_id' => $orderId, 'amount' => $entity['amount'] ?? null, 'status' => $entity['status'] ?? null],
            ]);
        } catch (UniqueConstraintViolationException) {
            return response()->json(['status' => 'duplicate ignored']);
        }

        if (! $payment) {
            return response()->json(['status' => 'unknown order ignored']);
        }

        try {
            match ($event) {
                'payment.captured', 'order.paid' => $payments->confirmOnline($payment, (string) $entity['id'], 'webhook', isset($entity['amount']) ? (int) $entity['amount'] : null),
                'payment.failed' => $payments->failOnline($payment, (string) ($entity['error_description'] ?? 'Payment failed at gateway')),
                default => null,
            };
        } catch (DomainException $e) {
            Log::warning('Razorpay webhook not applied', ['payment' => $payment->ulid, 'event' => $event, 'reason' => $e->getMessage()]);
        }

        return response()->json(['status' => 'ok']);
    }
}
