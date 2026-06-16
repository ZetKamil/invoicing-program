<!DOCTYPE html>
<html>
<head>
    <title>Invoice Reminder</title>
</head>
<body style="font-family: sans-serif; line-height: 1.5; color: #333;">
    <h2>Payment Reminder</h2>
    <p>Dear {{ $invoice->customer->first_name ?? 'Customer' }},</p>
    
    <p>This is a friendly reminder that invoice <strong>{{ $invoice->invoice_number }}</strong> for the amount of <strong>€{{ number_format($invoice->total_amount, 2) }}</strong> was due on <strong>{{ $invoice->due_date->format('F j, Y') }}</strong> and is now overdue.</p>
    
    <p>If you have already made this payment, please disregard this email. Otherwise, please arrange for payment as soon as possible.</p>
    
    <p>
        <a href="{{ route('invoice.pay', ['invoice' => $invoice->id]) }}" style="display: inline-block; padding: 10px 20px; background-color: #2563eb; color: #ffffff; text-decoration: none; border-radius: 5px;">
            Pay Invoice Online
        </a>
    </p>

    <p>Thank you for your business!</p>
    
    <p>Best regards,<br>
    {{ $invoice->bedrijf->name }}</p>
</body>
</html>
