# Dokument 10: Cykl Życia Dokumentu (Szczegółowa Ścieżka Techniczna)

Ten dokument szczegółowo rozpisuje całą architekturę i przepływ danych w Twoim systemie – krok po kroku. To "mapa drogowa" od pierwszego kliknięcia klienta na stronie internetowej, aż po wygenerowanie prawomocnej faktury gotowej do zaksięgowania.

Dzięki temu dokumentowi dokładnie wiesz, **jakie pliki** biorą w tym udział, **jakie funkcje** są wywoływane i **jak system przesyła ID** między tabelami w bazie danych.

---

## Krok 1: Wypełnienie Formularza i Powstanie Leada

Wszystko zaczyna się na froncie aplikacji, gdy potencjalny klient (np. firma logistyczna) odwiedza Twój landing page i wypełnia formularz wyceny ("Smart Quote").

*   **Użyty plik/klasa:** `app/Livewire/PackageInquiryForm.php` (Komponent Livewire)
*   **Model Bazy Danych:** `App\Models\Lead`
*   **Szczegóły techniczne:**
    Zamiast tworzyć setki kolumn w bazie, formularz zbiera wszystkie specyficzne dane (np. `trailer_type`, `loading_address`, `delivery_date`, `cargo_weight_kg`) i pakuje je do jednej, bardzo elastycznej kolumny `metadata` w formacie JSON.
*   **Efekt końcowy:** Baza danych zapisuje nowego Leada i nadaje mu automatycznie numer, np. `ID = 15`.

---

## Krok 2: Powiadomienie Dyspozytora w Czasie Rzeczywistym

Zaraz po zapisaniu Leada nr 15, system musi powiadomić o tym pracownika siedzącego w panelu administracyjnym. Zamiast zmuszać go do odświeżania strony, system korzysta z architektury Event-Driven (Opartej o zdarzenia).

*   **Użyte pliki/klasy:** 
    *   Event: `LeadSubmittedEvent`
    *   Widget na Froncie Admina: `app/Filament/Widgets/LiveLeadNotificationWidget.php`
*   **Technologia:** Laravel Reverb (WebSockets)
*   **Szczegóły techniczne:**
    System krzyczy do serwera WebSocket: *"Mamy nowego leada na kanale firmy X!"*. Widget `LiveLeadNotificationWidget` posiada w swoim kodzie nasłuchiwacz `#[On('echo-private:dispatcher.{bedrijf_id},lead.submitted')]`. Po usłyszeniu tego sygnału, widget natychmiast renderuje zieloną pulsującą kropkę z informacją o nowym zapytaniu – bezpośrednio w przeglądarce dyspozytora.

---

## Krok 3: Konwersja Leada w Wycenę (CreateQuoteAction)

Dyspozytor widzi powiadomienie, otwiera profil Leada i klika "Wygeneruj Wycenę". W tym momencie system nie każe mu przepisywać danych ręcznie. Odpala się specjalna "fabryka".

*   **Użyta klasa Akcji:** `app/Actions/Quotes/CreateQuoteAction.php`
*   **Wywoływana funkcja:** `public function handle(Lead $lead)`
*   **Szczegóły techniczne (Co dokładnie się dzieje):**
    1.  Akcja tworzy w pamięci nowy obiekt `Quote` (Wycenę).
    2.  Przypina go twardo do naszego Leada: `$quote->lead_id = 15;`
    3.  **Wypakowanie JSON-a:** Akcja wyciąga dane schowane wcześniej w kolumnie `metadata` i przepisuje je na sztywne pola transportowe, np.:
        `$quote->trailer_type = $lead->metadata['trailer_type'] ?? null;`
    4.  **Pricing (Cennik):** Akcja analizuje wybrany przez klienta pakiet i na podstawie nazwy podkłada cenę z biznesplanu (VLAIO):
        Jeżeli pakiet to "Start" -> `850.00`
        Jeżeli "Pro" -> `2450.00`
        Jeżeli "Enterprise" -> `600.00`
    5.  Zapisuje wycenę do bazy i generuje dla niej unikalny numer (np. `Q-20260616-ABCD`).

---

## Krok 4: Konwersja Wyceny w Fakturę (CreateInvoiceFromQuoteAction)

Gdy klient akceptuje wycenę nr `Q-20260616-ABCD`, dyspozytor klika "Generuj Fakturę". To jest najbardziej kluczowy moment w systemie finansowym.

*   **Użyta klasa Akcji:** `app/Actions/Invoices/CreateInvoiceFromQuoteAction.php`
*   **Wywoływana funkcja:** `public function execute(Quote $quote): Invoice`
*   **Szczegóły techniczne (Rozwiązanie zagadki ID):**
    1.  **Obliczenia Finansowe (BCMath):** System wylicza podatek i kwoty brutto. Zamiast używać zwykłych typów `float` (co generuje błędy dziesiętne np. 0.1+0.2=0.30004), używa wbudowanych w serwer funkcji `bcmul` i `bcadd`. Obliczenia są 100% dokładne rzędu dziesiętnych części centa.
    2.  **Relacja Polimorficzna:** System przypina fakturę do Leada używając `morphTo`:
        `'customer_type' => get_class($quote->lead)` (Zapisuje: "App\Models\Lead")
        `'customer_id'   => $quote->lead_id` (Zapisuje: 15)
    3.  **Generowanie ID Faktury:** System wywołuje komendę `Invoice::create([...])`. Właśnie w tym ułamku sekundy Baza Danych nadaje tej fakturze **Klucz Główny (ID)**, np. `ID = 100`.
    4.  **Przekazywanie ID do Pozycji (InvoiceItem):**
        Akcja od razu uruchamia pętlę po pozycjach z wyceny (`foreach ($quote->items as $item)`). 
        Dla każdej pozycji system wywołuje:
        ```php
        InvoiceItem::create([
            'invoice_id'  => $invoice->id,  // <-- TUTAJ system sam wpisuje wygenerowaną wcześniej 100-tkę!
            'description' => $item->description,
            'unit_price'  => $item->unit_price,
        ]);
        ```
    5. **Transakcja DB (`DB::transaction`):** Cały powyższy proces (tworzenie Faktury i jej Pozycji) zamknięty jest w tzw. transakcję. Jeśli prąd na serwerze zgasłby w trakcie pętli zapisującej pozycje, system wykona "Rollback" i anuluje tworzenie samej faktury. Nigdy nie powstanie "pusta" faktura na 0 EUR.

---

## Krok 5: Finał – Generowanie Plików (PDF i XML UBL)

Faktura jest już bezpiecznie zapisana w bazie z poprawnymi relacjami i sumami. System w tle dysponuje specjalnymi generatorami do stworzenia dokumentów wychodzących.

*   **Użyte klasy Akcji:** 
    *   `app/Actions/Invoices/GenerateInvoicePdfAction.php` (Generuje czytelnego PDF-a z logotypami)
    *   `app/Actions/Invoices/GenerateUblXmlAction.php` (Generuje plik XML w standardzie UBL/Peppol)
*   **Szczegóły techniczne:**
    Platforma to system nowoczesny (Gotowy na e-invoicing). Zamiast tylko rysować PDF-a, generuje równolegle plik XML. Dzięki temu fakturę można wysłać bezpośrednio do rządowej sieci Peppol, co jest standardem Unii Europejskiej od 2026 roku.

---

> **Co powiedzieć Jury:** 
> *"Architektura systemu to idealny przykład zastosowania zasady Single Responsibility. Modele bazy danych, takie jak Faktura czy Lead, są niemal 'puste' w swojej logice – one służą tylko do definiowania struktury i relacji. Natomiast cały ruch, od momentu wysłania e-maila po generowanie faktur PDF i XML, obsługują wyizolowane klasy typu Actions. Śledzenie procesu i debugowanie jest przez to banalne. Kiedy zamieniamy Wycenę w Fakturę, używamy transakcji bazodanowej by ochronić spójność oraz mechanizmów BCMath by zabezpieczyć obliczenia podatkowe na wypadek błędów zmiennoprzecinkowych. System nie pyta dyspozytora o ID, on sam orkiestruje te dane używając inteligentnych wiązań Eloquent ORM."*
