<?php

namespace App\Http\Controllers;

use App\Actions\Communications\LogCommunicationAction;
use App\Enums\CommType;
use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Services\MollieService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class MollieWebhookController extends Controller
{
    /**
     * Handle incoming Mollie payment webhook.
     * Mollie passes only the `id` of the payment, requiring server-side retrieval.
     */
    public function handleWebhook(Request $request, MollieService $mollieService, LogCommunicationAction $logCommunicationAction): Response
    {
        $paymentId = $request->input('id');

        if (empty($paymentId)) {
            Log::warning('Mollie Webhook received without payment ID.');
            return response('Missing payment ID', 400);
        }

        try {
            $payment = $mollieService->getPayment($paymentId);
            $invoiceId = $payment['metadata']['invoice_id'] ?? null;

            if (! $invoiceId) {
                Log::info("Mollie Webhook for payment {$paymentId} contains no invoice_id metadata.");
                return response('OK', 200);
            }

            $invoice = Invoice::withoutGlobalScopes()->find($invoiceId);

            if (! $invoice) {
                Log::error("Mollie Webhook: Invoice {$invoiceId} not found.");
                return response('Invoice not found', 404);
            }

            $status = $payment['status'] ?? 'open';
            $method = $payment['method'] ?? 'bancontact';

            if ($status === 'paid' && $invoice->status !== InvoiceStatus::PAID) {
                $invoice->update([
                    'status' => InvoiceStatus::PAID,
                    'paid_at' => now(),
                    'payment_method' => $method,
                    'payment_reference' => $paymentId,
                ]);

                $logCommunicationAction->execute(
                    $invoice->tenant_id,
                    "Factuur voldaan via Mollie ({$method})",
                    $invoice,
                    CommType::SYSTEM,
                    "Mollie Transactie: {$paymentId} | Bedrag: €{$payment['amount']['value']}"
                );

                Log::info("Invoice {$invoice->invoice_number} marked as PAID via Mollie ({$method}).");
            }

            return response('OK', 200);
        } catch (\Throwable $e) {
            Log::error('Mollie Webhook Exception: ' . $e->getMessage(), [
                'payment_id' => $paymentId,
            ]);

            return response('Internal Server Error', 500);
        }
    }
}
