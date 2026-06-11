<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #0f172a; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); border-top: 4px solid #2563eb; }
        .header { text-align: center; margin-bottom: 30px; }
        .total { font-size: 24px; font-weight: bold; color: #2563eb; margin: 20px 0; }
        .footer { margin-top: 30px; font-size: 12px; color: #64748b; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 20px; }
        .btn { display: inline-block; padding: 10px 20px; background-color: #2563eb; color: white; text-decoration: none; border-radius: 6px; font-weight: 500; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>{{ $quote->bedrijf->name }}</h2>
        </div>
        
        <p>Dear {{ $quote->lead->contact_person }},</p>
        
        <p>Thank you for your interest in our services. Please find below the summary of your requested quote (<strong>{{ $quote->quote_number }}</strong>).</p>
        
        <div class="total">
            Total Amount: â‚¬{{ number_format($quote->total_amount, 2) }}
        </div>
        
        <p>This quote is valid until {{ $quote->valid_until->format('d M Y') }}.</p>
        
        <p><em>(PDF attachment of the detailed quote will be appended here once the generator is implemented)</em></p>
        
        <p style="text-align: center; margin-top: 30px;">
            <a href="#" class="btn">View Full Quote</a>
        </p>
        
        <div class="footer">
            &copy; {{ date('Y') }} {{ $quote->bedrijf->name }}. All rights reserved.<br>
            This is an automated message, please do not reply directly.
        </div>
    </div>
</body>
</html>
