<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
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
        
        <p>Beste {{ $quote->customer->contact_person ?? $quote->customer->company_name }},</p>
        
        <p>Bedankt voor uw interesse in onze diensten. Hieronder vindt u een samenvatting van uw offerte (<strong>{{ $quote->quote_number }}</strong>).</p>
        
        <div class="total">
            Totaalbedrag: &euro;{{ number_format($quote->total_amount, 2, ',', '.') }}
        </div>
        
        <p>Deze offerte is geldig tot {{ $quote->valid_until->format('d-m-Y') }}.</p>
        
        <p><strong>Indien u akkoord gaat met deze offerte, gelieve op deze e-mail te antwoorden met uw bevestiging.</strong></p>
        
        <p><em>(De PDF-bijlage met de gedetailleerde offerte volgt nog)</em></p>
        

        
        <div class="footer">
            &copy; {{ date('Y') }} {{ $quote->bedrijf->name }}. Alle rechten voorbehouden.<br>
            Dit is een automatisch gegenereerd bericht, gelieve hier niet direct op te antwoorden.
        </div>
    </div>
</body>
</html>
