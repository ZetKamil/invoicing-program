<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #ffffff; color: #333; margin: 0; padding: 40px; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #2563eb; padding-bottom: 20px; margin-bottom: 30px; }
        .invoice-details h1 { color: #2563eb; margin: 0 0 10px 0; }
        .bill-to { margin-bottom: 40px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { background-color: #f8fafc; border-bottom: 2px solid #e2e8f0; text-align: left; padding: 12px; }
        td { padding: 12px; border-bottom: 1px solid #e2e8f0; }
        .totals { float: right; width: 300px; }
        .total-row { display: flex; justify-content: space-between; padding: 8px 0; }
        .total-row.grand-total { font-size: 18px; font-weight: bold; border-top: 2px solid #2563eb; padding-top: 12px; margin-top: 12px; color: #2563eb; }
        .logistics-section { margin-bottom: 30px; background-color: #f8fafc; padding: 15px; border-radius: 5px; }
        .logistics-section h4 { margin-top: 0; color: #2563eb; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 15px; }
        .logistics-grid { display: flex; flex-wrap: wrap; gap: 20px; }
        .logistics-col { flex: 1; min-width: 200px; font-size: 13px; line-height: 1.5; }
        .notes-section { clear: both; margin-top: 50px; padding: 15px; background-color: #f8fafc; border-left: 4px solid #2563eb; font-size: 14px; white-space: pre-wrap; }
        .notes-section h4 { margin-top: 0; color: #2563eb; }
        .footer { clear: both; text-align: center; margin-top: 50px; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h2>{{ $invoice->bedrijf->name ?? 'Logistics Agency' }}</h2>
            <p>123 Business Road<br>City, Country<br>VAT: BE0123456789</p>
        </div>
        <div class="invoice-details">
            <h1>INVOICE</h1>
            <p><strong>Invoice Number:</strong> {{ $invoice->invoice_number }}</p>
            <p><strong>Date:</strong> {{ $invoice->created_at->format('d M Y') }}</p>
            <p><strong>Due Date:</strong> {{ $invoice->due_date->format('d M Y') }}</p>
            @if($invoice->status->value === 'paid')
                <p style="color: #16a34a; font-weight: bold; margin-top: 10px;">PAID</p>
                @if($invoice->stripe_payment_intent_id)
                    <p style="font-size: 11px; color: #64748b;">Transaction: {{ $invoice->stripe_payment_intent_id }}</p>
                @endif
            @endif
        </div>
    </div>

    <div class="bill-to">
        <h3>Bill To:</h3>
        <p>
            <strong>{{ $invoice->customer->company_name ?? $invoice->customer->name }}</strong><br>
            {{ $invoice->customer->contact_person ?? '' }}<br>
            {{ $invoice->customer->email }}<br>
            {{ $invoice->customer->phone ?? '' }}
        </p>
    </div>

    @if($invoice->loading_address || $invoice->delivery_address || $invoice->trailer_type || $invoice->cmr_number)
    <div class="logistics-section">
        <h4>Transport Details</h4>
        <div class="logistics-grid">
            <div class="logistics-col">
                <strong>Loading:</strong><br>
                {{ $invoice->loading_address ?: '-' }}<br>
                Date: {{ $invoice->loading_date ? $invoice->loading_date->format('d M Y') : '-' }}
            </div>
            <div class="logistics-col">
                <strong>Delivery:</strong><br>
                {{ $invoice->delivery_address ?: '-' }}<br>
                Date: {{ $invoice->delivery_date ? $invoice->delivery_date->format('d M Y') : '-' }}
            </div>
            <div class="logistics-col">
                <strong>Trailer / CMR:</strong><br>
                Type: {{ $invoice->trailer_type ?: '-' }}<br>
                CMR: {{ $invoice->cmr_number ?: '-' }}<br>
                Incoterms: {{ $invoice->incoterms ?: '-' }}
            </div>
            <div class="logistics-col">
                <strong>Cargo:</strong><br>
                Weight: {{ $invoice->cargo_weight_kg ? $invoice->cargo_weight_kg . ' kg' : '-' }}<br>
                Pallets: {{ $invoice->pallet_count ?: '-' }}<br>
                Truck Pl.: {{ $invoice->truck_license_plate ?: '-' }}<br>
                Trailer Pl.: {{ $invoice->trailer_license_plate ?: '-' }}
            </div>
        </div>
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Tax Rate</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td>{{ $item->quantity }}</td>
                <td>&euro;{{ number_format($item->unit_price, 2) }}</td>
                <td>{{ number_format($item->tax_rate, 2) }}%</td>
                <td>&euro;{{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="total-row">
            <span>Subtotal:</span>
            <span>&euro;{{ number_format($invoice->subtotal, 2) }}</span>
        </div>
        <div class="total-row">
            <span>Tax Total:</span>
            <span>&euro;{{ number_format($invoice->tax_total, 2) }}</span>
        </div>
        <div class="total-row grand-total">
            <span>Total Amount:</span>
            <span>&euro;{{ number_format($invoice->total_amount, 2) }}</span>
        </div>
    </div>

    @if($invoice->notes)
    <div class="notes-section">
        <h4>Opmerkingen / Voorwaarden</h4>
        {{ $invoice->notes }}
    </div>
    @endif

    <div class="footer">
        Thank you for your business. Please make payment by {{ $invoice->due_date->format('d M Y') }}.
    </div>
</body>
</html>
