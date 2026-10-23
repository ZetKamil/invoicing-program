<?php

namespace App\Services\Peppol;

use App\Models\Invoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class PeppolAccessPointService
{
    /**
     * Dispatch a compliant Peppol BIS 3.0 UBL XML invoice to the European Peppol network via AS4.
     *
     * In Belgium (mandatory from Jan 1, 2026), B2B invoices cannot merely be emailed as PDF/XML;
     * they must be dispatched through an authorized Peppol Access Point (AP) via AS4 protocol.
     *
     * Supports:
     * - 'mock': Local / Testing driver (simulates AS4 transmission without incurring external API costs)
     * - 'storecove' / 'digiteal' / 'billit': External developer Access Point API (~0.10€ - 0.25€ / doc)
     *
     * @param  Invoice $invoice
     * @param  string  $ublXml
     * @return array{success: bool, transmission_id: string, status: string, network: string, delivered_at: string}
     */
    public function dispatchInvoice(Invoice $invoice, string $ublXml): array
    {
        $driver = config('services.peppol.driver', 'mock');
        $endpointId = $invoice->customer->vat_number ?? $invoice->customer->company_name ?? '0000000000';
        $cleanedEndpointId = preg_replace('/[^A-Za-z0-9]/', '', $endpointId);

        if ($driver === 'mock' || app()->environment('testing', 'local')) {
            $transmissionId = 'AS4-BE-' . Str::upper((string) Str::ulid()) . '@peppol.eu';

            Log::info("Peppol AS4 Dispatch (Mock AP): Invoice {$invoice->invoice_number} dispatched to {$cleanedEndpointId}.", [
                'transmission_id' => $transmissionId,
                'bytes' => strlen($ublXml),
            ]);

            return [
                'success' => true,
                'transmission_id' => $transmissionId,
                'status' => 'delivered',
                'network' => 'Peppol BIS Billing 3.0 (AS4 Protocol)',
                'delivered_at' => now()->toIso8601String(),
            ];
        }

        // Production driver via external Access Point Developer API (e.g. Digiteal / Storecove / Billit)
        $apiKey = config('services.peppol.api_key');
        $apiEndpoint = config('services.peppol.endpoint');

        if (empty($apiKey) || empty($apiEndpoint)) {
            throw new RuntimeException('Peppol Access Point API sleutel of endpoint ontbreekt in de configuratie.');
        }

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->post($apiEndpoint . '/documents/submit', [
                'document_type' => 'ubl_invoice_2_1',
                'recipient_identifier' => '0208:' . $cleanedEndpointId, // 0208 = Belgian Enterprise / VAT scheme
                'document_content_base64' => base64_encode($ublXml),
                'invoice_number' => $invoice->invoice_number,
            ]);

        if (! $response->successful()) {
            Log::error('Peppol Access Point AS4 Dispatch Failure: ' . $response->body(), [
                'invoice_id' => $invoice->id,
            ]);

            throw new RuntimeException('Peppol Access Point fout: ' . ($response->json('message') ?? $response->body()));
        }

        $transmissionId = $response->json('transmission_id') ?? ('AS4-' . (string) Str::ulid());

        return [
            'success' => true,
            'transmission_id' => $transmissionId,
            'status' => 'delivered',
            'network' => 'Peppol BIS Billing 3.0 (AS4 Protocol)',
            'delivered_at' => now()->toIso8601String(),
        ];
    }
}
