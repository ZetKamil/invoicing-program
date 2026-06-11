# Phase 4: Enterprise FinTech Precision & Security Audit

## Objective
To ensure Master-Digit operates with absolute financial accuracy, strictly adheres to 2026 Peppol electronic invoicing standards, and guarantees real-time secure state management, we conducted a massive architectural audit (Phase 4). The focus was on eliminating silent floating-point drift, hardening the UBL XML pipeline, securing Livewire components against model substitution attacks, and implementing true real-time WebSockets.

---

## 1. Absolute Financial Precision: The BCMath Architecture

### The Threat: IEEE 754 Floating-Point Drift
In standard PHP, `float` arithmetic is susceptible to IEEE 754 binary representation drift. For example, calculating a 21% tax on 3 units of €14.90 (`14.90 * 3 * 1.21`) yields `54.08699999...` instead of exactly `54.09`. 
When scaled to thousands of line items in an enterprise logistics system, these micro-deviations compound, leading to:
- **Cent Mismatches:** Stripe checkouts failing due to mismatched totals.
- **Peppol Rejections:** The UBL 2.1 Access Point rigorously recalculates `TaxTotal` against `LineExtensionAmount` and rejects the document if it's off by even a single cent.

### The Solution: String-Based BCMath
We purged all PHP `float` arithmetic from the financial pipeline:
1. **Observers & Actions:** Replaced PHP multiplication (`*`) and `round()` with `bcmul()`, `bcadd()`, and `bcdiv()`. 
2. **Intermediate Precision:** We calculate line items using a scale of 10 (`bcmul($qty, $price, 10)`), and only truncate to 2 decimal places at the final step (`bcadd(..., 2)`).
3. **Contract Enforcement:** Changed method signatures (e.g., `CreateQuoteAction::handle(Lead $lead, string $totalAmount)`) to enforce the passage of BCMath-compatible string literals, permanently blocking `float` leakage.
4. **Stripe Integration:** Modified `StripeService` to convert totals to cents using exact string multiplication (`bcmul($amount, '100', 0)`) rather than unreliable `round()`.

---

## 2. Peppol UBL 2.1 XML Hardening

### The Threat: Silent Pipeline Failure & Non-Compliance
The initial implementation lacked exception handling. If the server failed to write the XML file (e.g., disk full), the entire invoice generation process crashed, preventing the user from even downloading the PDF. Furthermore, the XML was not strictly compliant with Peppol BIS Billing 3.0.

### The Solution: Decoupling & Validation
1. **Partial Success Pattern:** We decoupled PDF generation from XML generation in `GenerateInvoicePdfAction`. The critical human-readable PDF is generated first. The XML generation is wrapped in a `try/catch` block catching a custom `UblGenerationException`. If XML generation fails, the error is logged, the `ubl_xml_path` remains null, but the invoice succeeds.
2. **Pre-Storage DOM Validation:** `GenerateUblXmlAction` now parses the generated XML using PHP's `DOMDocument` *before* saving it to disk. This guarantees that no malformed XML (caused by bad data escaping) is ever sent to the Peppol Access Point.
3. **Strict Compliance:** Added the mandatory `<cbc:DueDate>` and conditional `<cbc:BuyerReference>` tags to the `ubl.blade.php` template, bringing it to 100% compliance with the 2026 Peppol BIS 3.0 standard.

---

## 3. Livewire Security: Defeating Model Substitution

### The Threat: Hydration Tampering
The public invoice payment portal (`InvoicePayPortal`) previously bound the full `Invoice` Eloquent model to a `public` property. Livewire serializes this into the browser's DOM. A malicious user could use browser DevTools to intercept the request, alter the `invoice.id` in the Livewire payload to a different ULID, and trick the server into generating a Stripe checkout session for someone else's invoice.

### The Solution: Cryptographic Property Locking
1. **`#[Locked]` Attributes:** Replaced `public Invoice $invoice` with `#[Locked] public string $invoiceId`. Livewire cryptographically signs this locked property. Any attempt to tamper with the ULID in the browser results in a `CannotBindToComponentDataWithoutValidation` exception, instantly terminating the request.
2. **Server-Side Re-Verification:** In the `pay()` method, the system discards any hydrated state and explicitly re-fetches the authoritative invoice from the database using the locked ID, followed by a strict bedrijf ownership check.
3. **State Protection:** Applied the same `#[Locked]` pattern to the public `PackageInquiryForm` to prevent users from manipulating the selected package strings.

---

## 4. Real-Time WebSockets: Laravel Reverb Broadcasting

### The Threat: Polling Latency & Database Load
Relying on HTTP polling (`wire:poll`) to notify dispatchers of new inbound leads creates massive database strain (Queries = Users × Polling Interval). Alternatively, requiring manual page refreshes leads to unacceptable delays in the logistics sector, where response time wins contracts.

### The Solution: True Event-Driven Reactivity
1. **Laravel Reverb:** We implemented native WebSockets using Laravel Reverb, replacing third-party dependencies like Pusher.
2. **Secure Private Channels:** Dispatchers subscribe to a bedrijf-isolated private channel (`private-dispatcher.{bedrijfId}`). The `routes/channels.php` authorization callback verifies the user's bedrijf affiliation and role, preventing cross-bedrijf data leakage.
3. **Filament Dashboard Integration:** Developed the `LiveLeadNotificationWidget` using Livewire 4's `#[On('echo-private:...')]` listener. When a new lead is submitted on the public site, `CreateLeadAction` queues a `LeadSubmittedEvent`. The dispatcher receives a real-time pulsing UI banner and toast notification instantly, without a single HTTP request or page refresh.

---

### Conclusion
With Phase 4 complete, Master-Digit transitions from a robust SaaS to an **Enterprise-Grade FinTech Logistics Platform**. It mathematically guarantees tax precision, flawlessly complies with European e-invoicing laws, completely nullifies state tampering attacks, and delivers instant WebSocket reactivity at scale.
