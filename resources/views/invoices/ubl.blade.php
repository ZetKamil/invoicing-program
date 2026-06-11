<?xml version="1.0" encoding="UTF-8"?>
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"
         xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
         xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2">
    
    <cbc:CustomizationID>urn:cen.eu:en16931:2017#compliant#urn:fdc:peppol.eu:2017:poacc:billing:3.0</cbc:CustomizationID>
    <cbc:ProfileID>urn:fdc:peppol.eu:2017:poacc:billing:01:1.0</cbc:ProfileID>
    <cbc:ID>{{ $invoice->invoice_number }}</cbc:ID>
    <cbc:IssueDate>{{ $invoice->created_at->format('Y-m-d') }}</cbc:IssueDate>
    {{-- DueDate is MANDATORY in Peppol BIS Billing 3.0 for payment terms specification --}}
    <cbc:DueDate>{{ $invoice->due_date->format('Y-m-d') }}</cbc:DueDate>
    <cbc:InvoiceTypeCode>380</cbc:InvoiceTypeCode>
    {{-- BuyerReference: mandatory in DE/NL profiles when provided by the buyer --}}
    @if($invoice->buyer_reference)
    <cbc:BuyerReference>{{ $invoice->buyer_reference }}</cbc:BuyerReference>
    @endif
    <cbc:DocumentCurrencyCode>EUR</cbc:DocumentCurrencyCode>

    <cac:AccountingSupplierParty>
        <cac:Party>
            <cac:PartyName>
                <cbc:Name>{{ $invoice->tenant->name }}</cbc:Name>
            </cac:PartyName>
            <cac:PostalAddress>
                <cbc:Country>
                    <cbc:IdentificationCode>BE</cbc:IdentificationCode>
                </cbc:Country>
            </cac:PostalAddress>
            <cac:PartyTaxScheme>
                <cbc:CompanyID>{{ $invoice->tenant->vat_number ?? 'BE0000000000' }}</cbc:CompanyID>
                <cac:TaxScheme>
                    <cbc:ID>VAT</cbc:ID>
                </cac:TaxScheme>
            </cac:PartyTaxScheme>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName>{{ $invoice->tenant->name }}</cbc:RegistrationName>
            </cac:PartyLegalEntity>
        </cac:Party>
    </cac:AccountingSupplierParty>

    <cac:AccountingCustomerParty>
        <cac:Party>
            <cac:PartyName>
                <cbc:Name>{{ $invoice->customer->company_name ?? $invoice->customer->first_name . ' ' . $invoice->customer->last_name }}</cbc:Name>
            </cac:PartyName>
            <cac:PostalAddress>
                <cbc:Country>
                    <cbc:IdentificationCode>BE</cbc:IdentificationCode>
                </cbc:Country>
            </cac:PostalAddress>
            <cac:PartyTaxScheme>
                <cbc:CompanyID>{{ $invoice->customer->vat_number ?? 'UNKNOWN' }}</cbc:CompanyID>
                <cac:TaxScheme>
                    <cbc:ID>VAT</cbc:ID>
                </cac:TaxScheme>
            </cac:PartyTaxScheme>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName>{{ $invoice->customer->company_name ?? $invoice->customer->first_name . ' ' . $invoice->customer->last_name }}</cbc:RegistrationName>
            </cac:PartyLegalEntity>
        </cac:Party>
    </cac:AccountingCustomerParty>

    <cac:TaxTotal>
        <cbc:TaxAmount currencyID="EUR">{{ number_format($invoice->tax_total, 2, '.', '') }}</cbc:TaxAmount>
        <cac:TaxSubtotal>
            <cbc:TaxableAmount currencyID="EUR">{{ number_format($invoice->subtotal, 2, '.', '') }}</cbc:TaxableAmount>
            <cbc:TaxAmount currencyID="EUR">{{ number_format($invoice->tax_total, 2, '.', '') }}</cbc:TaxAmount>
            <cac:TaxCategory>
                <cbc:ID>S</cbc:ID>
                <cbc:Percent>21.00</cbc:Percent>
                <cac:TaxScheme>
                    <cbc:ID>VAT</cbc:ID>
                </cac:TaxScheme>
            </cac:TaxCategory>
        </cac:TaxSubtotal>
    </cac:TaxTotal>

    <cac:LegalMonetaryTotal>
        <cbc:LineExtensionAmount currencyID="EUR">{{ number_format($invoice->subtotal, 2, '.', '') }}</cbc:LineExtensionAmount>
        <cbc:TaxExclusiveAmount currencyID="EUR">{{ number_format($invoice->subtotal, 2, '.', '') }}</cbc:TaxExclusiveAmount>
        <cbc:TaxInclusiveAmount currencyID="EUR">{{ number_format($invoice->total_amount, 2, '.', '') }}</cbc:TaxInclusiveAmount>
        <cbc:PayableAmount currencyID="EUR">{{ number_format($invoice->total_amount, 2, '.', '') }}</cbc:PayableAmount>
    </cac:LegalMonetaryTotal>

    @foreach($invoice->items as $index => $item)
    <cac:InvoiceLine>
        <cbc:ID>{{ $index + 1 }}</cbc:ID>
        <cbc:InvoicedQuantity unitCode="EA">{{ $item->quantity }}</cbc:InvoicedQuantity>
        <cbc:LineExtensionAmount currencyID="EUR">{{ number_format($item->total, 2, '.', '') }}</cbc:LineExtensionAmount>
        <cac:Item>
            <cbc:Name>{{ $item->description }}</cbc:Name>
            <cac:ClassifiedTaxCategory>
                <cbc:ID>S</cbc:ID>
                <cbc:Percent>21.00</cbc:Percent>
                <cac:TaxScheme>
                    <cbc:ID>VAT</cbc:ID>
                </cac:TaxScheme>
            </cac:ClassifiedTaxCategory>
        </cac:Item>
        <cac:Price>
            <cbc:PriceAmount currencyID="EUR">{{ number_format($item->unit_price, 2, '.', '') }}</cbc:PriceAmount>
        </cac:Price>
    </cac:InvoiceLine>
    @endforeach

</Invoice>
