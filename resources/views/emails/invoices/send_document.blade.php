<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $documentTitle }} - {{ $document->document_number ?? $document->invoice_number }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="table-layout: fixed;">
        <tr>
            <td align="center" style="padding: 40px 16px;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0;">
                    <!-- Header -->
                    <tr>
                        <td style="padding: 32px 32px 24px; border-bottom: 1px solid #f1f5f9;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td>
                                        <h1 style="margin: 0; font-size: 20px; font-weight: 700; color: #0f172a; letter-spacing: -0.02em;">
                                            {{ $tenant->name ?? 'KMO-Flow' }}
                                        </h1>
                                        @if(!empty($tenant->vat_number))
                                            <p style="margin: 4px 0 0; font-size: 13px; color: #64748b;">
                                                BTW / Ondernemingsnr: {{ $tenant->vat_number }}
                                            </p>
                                        @endif
                                    </td>
                                    <td align="right" valign="top">
                                        <span style="display: inline-block; padding: 6px 12px; background-color: #eff6ff; color: #2563eb; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; border-radius: 9999px;">
                                            {{ $documentTitle }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 32px;">
                            <p style="margin: 0 0 16px; font-size: 15px; color: #334155;">
                                Geachte {{ $recipientName }},
                            </p>

                            <p style="margin: 0 0 24px; font-size: 15px; line-height: 1.6; color: #475569;">
                                In bijlage vindt u uw {{ strtolower($documentTitle) }} met nummer <strong>{{ $documentNumber }}</strong>.
                            </p>

                            <!-- Document Summary Box -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 28px;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td style="font-size: 13px; color: #64748b; padding-bottom: 8px;">Documentnummer:</td>
                                                <td align="right" style="font-size: 14px; font-weight: 600; color: #0f172a; padding-bottom: 8px;">{{ $documentNumber }}</td>
                                            </tr>
                                            @if($dueDate)
                                                <tr>
                                                    <td style="font-size: 13px; color: #64748b; padding-bottom: 8px;">Vervaldatum:</td>
                                                    <td align="right" style="font-size: 14px; font-weight: 600; color: #0f172a; padding-bottom: 8px;">{{ $dueDate }}</td>
                                                </tr>
                                            @endif
                                            @if($structuredReference)
                                                <tr>
                                                    <td style="font-size: 13px; color: #64748b; padding-bottom: 8px;">Gestructureerde mededeling (OGM):</td>
                                                    <td align="right" style="font-size: 14px; font-weight: 600; color: #0f172a; font-family: monospace; padding-bottom: 8px;">{{ $structuredReference }}</td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <td style="font-size: 15px; font-weight: 700; color: #0f172a; border-top: 1px solid #e2e8f0; padding-top: 12px;">Totaal te betalen:</td>
                                                <td align="right" style="font-size: 20px; font-weight: 800; color: #16a34a; border-top: 1px solid #e2e8f0; padding-top: 12px;">€ {{ $formattedTotal }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Payment Button CTA -->
                            @if($paymentUrl)
                                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 28px;">
                                    <tr>
                                        <td align="center">
                                            <a href="{{ $paymentUrl }}" target="_blank" style="display: block; width: 100%; box-sizing: border-box; text-align: center; padding: 16px 24px; background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: #ffffff; text-decoration: none; font-size: 16px; font-weight: 700; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(22, 163, 74, 0.3);">
                                                💳 Betaal direct online via Bancontact / Kaart
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            <!-- Manual Wire Transfer Fallback -->
                            @if(!empty($tenant->iban))
                                <div style="font-size: 13px; color: #64748b; line-height: 1.5; background-color: #ffffff; border-left: 3px solid #cbd5e1; padding-left: 12px; margin-bottom: 24px;">
                                    <strong>Liever via klassieke overschrijving?</strong><br>
                                    IBAN: <strong>{{ $tenant->iban }}</strong> @if(!empty($tenant->bic)) | BIC: <strong>{{ $tenant->bic }}</strong> @endif<br>
                                    Mededeling: <strong>{{ $structuredReference ?: $documentNumber }}</strong>
                                </div>
                            @endif

                            <p style="margin: 0; font-size: 14px; line-height: 1.6; color: #475569;">
                                Heeft u nog vragen over dit document? Beantwoord gerust deze e-mail of neem rechtstreeks contact op met onze administratie.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 24px 32px; background-color: #f8fafc; border-top: 1px solid #f1f5f9; text-align: center;">
                            <p style="margin: 0; font-size: 12px; color: #94a3b8;">
                                Verzonden door {{ $tenant->name ?? 'KMO-Flow' }} via het KMO-Flow B2B Invoicing Platform.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
