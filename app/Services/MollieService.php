<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Tenant;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class MollieService
{
    protected string $baseUrl = 'https://api.mollie.com/v2';

    /**
     * Create a Mollie Bancontact/iDEAL payment session for an invoice.
     * Webhook URL is dynamically bound during payment creation — zero webhook configuration needed by the merchant!
     *
     * @param  Invoice     $invoice
     * @param  string|null $customApiKey
     * @return array{checkout_url: string, payment_id: string}
     */
    public function createPaymentForInvoice(Invoice $invoice, ?string $customApiKey = null): array
    {
        $apiKey = $customApiKey ?: $this->resolveApiKeyForTenant($invoice->tenant);

        if (empty($apiKey)) {
            throw new RuntimeException('Geen Mollie API sleutel geconfigureerd voor deze onderneming.');
        }

        // Format decimal currency according to Mollie specification (2 decimals, string e.g. "120.50")
        $value = number_format((float) $invoice->total_amount, 2, '.', '');
        $tenantName = $invoice->tenant->name ?? 'KMO-Flow Partner';

        $payload = [
            'amount' => [
                'currency' => 'EUR',
                'value' => $value,
            ],
            'description' => "Factuur {$invoice->invoice_number} - {$tenantName}",
            'redirectUrl' => route('pay.portal', ['invoice' => $invoice->id]) . '?status=paid',
            'webhookUrl' => route('mollie.webhook'),
            'metadata' => [
                'invoice_id' => $invoice->id,
                'tenant_id' => $invoice->tenant_id,
            ],
            'method' => ['bancontact', 'ideal', 'creditcard'],
        ];

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->post("{$this->baseUrl}/payments", $payload);

        if (! $response->successful()) {
            Log::error('Mollie Payment Creation Error: ' . $response->body(), [
                'invoice_id' => $invoice->id,
                'status' => $response->status(),
            ]);

            throw new RuntimeException('Mollie betaling kon niet worden geïnitieerd: ' . ($response->json('detail') ?? $response->body()));
        }

        $checkoutUrl = $response->json('_links.checkout.href');
        $paymentId = $response->json('id');

        return [
            'checkout_url' => $checkoutUrl,
            'payment_id' => $paymentId,
        ];
    }

    /**
     * Retrieve payment details from Mollie by Payment ID.
     *
     * @param  string      $paymentId
     * @param  string|null $apiKey
     * @return array<string, mixed>
     */
    public function getPayment(string $paymentId, ?string $apiKey = null): array
    {
        $key = $apiKey ?: config('services.mollie.key');

        $response = Http::withToken($key)
            ->acceptJson()
            ->get("{$this->baseUrl}/payments/{$paymentId}");

        if (! $response->successful()) {
            throw new RuntimeException("Mollie payment fetch failed: {$response->body()}");
        }

        return $response->json();
    }

    /**
     * Resolve the appropriate Mollie API key for a tenant (decrypting if stored encrypted).
     */
    public function resolveApiKeyForTenant(?Tenant $tenant): ?string
    {
        if ($tenant && !empty($tenant->settings['payment_gateways']['mollie']['api_key'])) {
            $raw = $tenant->settings['payment_gateways']['mollie']['api_key'];
            try {
                return Crypt::decryptString($raw);
            } catch (\Throwable $e) {
                return $raw;
            }
        }

        return config('services.mollie.key') ?: null;
    }

    /**
     * Check if Mollie is configured for a specific tenant or globally.
     */
    public function isConfiguredForTenant(?Tenant $tenant): bool
    {
        return !empty($this->resolveApiKeyForTenant($tenant));
    }
}
