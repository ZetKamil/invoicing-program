# 🧠 KROK 4: Generowanie Faktury (Transakcje, BCMath i Peppol UBL)

**Plik:** `app/Actions/Invoices/CreateInvoiceFromQuoteAction.php`

## O czym jest ten krok?
Pokazujesz zaawansowane wzorce inżynierii oprogramowania w systemach finansowych. Jury sprawdzi, czy wiesz, jak radzić sobie z operacjami pieniężnymi i przerwaniem połączenia w połowie tworzenia faktury.

## Co musisz wytłumaczyć z kodu?

### 1. Podwójne bezpieczeństwo dzięki `DB::transaction`
Kopiowanie wyceny na fakturę to wiele kroków w bazie danych (utworzenie nagłówka, skopiowanie linii, naliczenie podatków). 
- **Tłumaczenie dla Jury:** "Jeśli po przekopiowaniu 2 z 10 linii ładunku nagle padnie nam baza danych, nie chcemy mieć w systemie na wpół zrobionej faktury na błędną kwotę. `DB::transaction` sprawia, że cały proces traktowany jest jako 'Zrób wszystko albo wycofaj wszystko'. W razie awarii, baza automatycznie wykonuje operację ROLLBACK i kasuje ucięty szkic."

### 2. Standard księgowy BCMath (FinTech)
Gdy obliczasz 21% VAT, musisz zachować skrajną precyzję.
- **Tłumaczenie dla Jury:** "Zabroniłem mojemu kodowi używać standardowego typu matematycznego zmiennoprzecinkowego (tzw. Floating Point). Przez to, jak procesory zapisują ułamki w systemie dwójkowym, operacja `0.1 + 0.2` w PHP czy JavaScript często daje wynik `0.30000000000000004`. Gdy operujemy na wielkich kwotach fakturowych, księgowym nie zgadzałyby się centy pod koniec roku. Funkcje `bcmul` czy `bcadd` (z rozszerzenia BCMath) wykonują obliczenia na zwykłych stringach. Precyzja jest wtedy absolutna w 100%."

### 3. As w rękawie: Zgodność Peppol UBL (Prawo z 2026 r.)
- **Tłumaczenie dla Jury:** "Ponieważ Belgia w 2026 roku wymusza system przesyłu e-faktur dla biznesu B2B, zaimplementowałem pełną zgodność ze standardem **Peppol BIS Billing 3.0**. Guzik w moim panelu generuje unijną e-fakturę z użyciem języka UBL 2.1 (zapisanej jako XML). Inny program księgowy odczyta tę fakturę bez błędów w 0,1 sekundy."
