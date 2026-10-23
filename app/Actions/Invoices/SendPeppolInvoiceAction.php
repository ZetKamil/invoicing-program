<?php

namespace App\Actions\Invoices;

use App\Actions\Communications\LogCommunicationAction;
use App\Enums\CommType;
use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Services\Peppol\PeppolAccessPointService;

class SendPeppolInvoiceAction
{
    public function __construct(
        protected GenerateUblXmlAction $generateUblAction,
        protected PeppolAccessPointService $peppolAccessPointService,
        protected LogCommunicationAction $logCommunicationAction
    ) {}

    /**
     * Generate compliant Peppol BIS 3.0 UBL XML and dispatch it via Peppol Access Point AS4 protocol.
     *
     * @param  Invoice $invoice
     * @return array{success: bool, transmission_id: string, xml_path: string}
     */
    public function execute(Invoice $invoice): array
    {
        // 1. Generate compliant UBL 2.1 XML
        $xmlPath = $this->generateUblAction->execute($invoice);
        $ublXml = \Illuminate\Support\Facades\Storage::get($xmlPath);

        // 2. Dispatch to Peppol Access Point via AS4
        $result = $this->peppolAccessPointService->dispatchInvoice($invoice, $ublXml);

        // 3. Update Invoice metadata
        $updateData = [
            'peppol_status' => $result['status'] ?? 'delivered',
            'peppol_transmission_id' => $result['transmission_id'],
        ];

        if ($invoice->status === InvoiceStatus::DRAFT) {
            $updateData['status'] = InvoiceStatus::SENT;
        }

        $invoice->update($updateData);

        // 4. Log audit and communication trail
        $this->logCommunicationAction->execute(
            $invoice->tenant_id,
            "Peppol BIS 3.0 AS4 Verzending geslaagd",
            $invoice,
            CommType::SYSTEM,
            "Transmissie ID: {$result['transmission_id']} | Netwerk: {$result['network']}"
        );

        return [
            'success' => true,
            'transmission_id' => $result['transmission_id'],
            'xml_path' => $xmlPath,
        ];
    }
}
