# 🐘 Hardcore SQL & Architecture Defense Script (Vakjury Edition)

Ten skrypt został przeprojektowany na poziom "Senior Engineer". Jeśli w komisji jest specjalista SQL z 25-letnim stażem, "wyklikanie" aplikacji go nie zadowoli. Musisz rzucać pojęciami z zakresu **optymalizacji B-Tree, indeksowania, polimorfizmu i ścisłych typów danych**. 

Scenariusz opiera się dokładnie na Flow, o który prosiłeś: Impersonate -> Lead (Klient) -> Faktura -> Płatność (Link) -> Peppol. Wymagania dotyczące e-maili z zadania (3.7) dotyczą **automatycznych przypomnień o zaległościach (Herinneringsmails)**, więc link do samej faktury z panelu w zupełności wystarczy na tym etapie.

---

## 🕒 0:00 - 0:05 | Wstęp i Multi-tenant (Wymóg 3.1)
**Twoja akcja w UI:** Zaloguj się jako SuperAdmin. Kliknij "Impersonate" przy użytkowniku należącym do firmy transportowej (Bedrijf A).
**Co otwierasz w IDE:** `app/Models/Scopes/BedrijfScope.php` oraz migrację z kluczami obcymi.
**Twój tekst:**
> "Dzień dobry. Zaprezentuję system do e-fakturowania dla firm transportowych (Traject B).
> Zacznijmy od fundamentu: architektury Multi-tenant (Wymóg 3.1). Nie zastosowałem podziału na osobne bazy danych per firma, lecz system 'Single Database, Multiple Tenants'. Użyłem Global Scopes wbudowanych w Eloquent. Na najniższym poziomie zapytań SQL, do każdego `SELECT`, `UPDATE` i `DELETE` doklejany jest warunek `AND bedrijf_id = 'xxx'`. 
> 
> Chciałbym zwrócić uwagę na identyfikatory. Zamiast standardowych kluczy głównych (Auto-Increment), które są podatne na ID-guessing, nie użyłem też zwykłego UUIDv4. Użyłem **ULID** (Universally Unique Lexicographically Sortable Identifier). Zwykłe UUID to losowe ciągi znaków, które drastycznie fragmentują indeksy B-Tree w bazach relacyjnych (tzw. Page Splits), co przy dużym ruchu 'zabija' wydajność operacji `INSERT`. ULID jest sortowalny czasowo (jak auto-increment), dzięki czemu indeksy B-Tree układają się sekwencyjnie zachowując maksymalną wydajność i całkowite bezpieczeństwo kryptograficzne."

---

## 🕒 0:05 - 0:10 | Flow: Auto-Provisioning (Data Capture) i Relacje Polimorficzne
**Twoja akcja w UI:** Pokazujesz zakładkę `Aanvragen` (Leads), do której spływają zapytania z formularza. Następnie pokazujesz zakładkę `Klanten` (Klienci), gdzie system w tle automatycznie wygenerował profil dla tego zapytania.
**Co otwierasz w IDE:** `app/Models/Lead.php` (metoda `booted`) oraz `app/Models/Invoice.php` (relacja `customer(): MorphTo`).
**Twój tekst:**
> "Widzimy tutaj zapytanie, które wpłynęło przez formularz publiczny (tzw. Data Capture). Jednak nie muszę go ręcznie przepisywać! Wykorzystałem architekturę sterowaną zdarzeniami (Event-Driven Architecture). Dzięki *Eloquent Model Events*, w tej samej milisekundzie, w której Lead trafia do bazy, uruchamia się **Auto-Provisioning**, który rezerwuje wpis w docelowej tabeli `customers`. 
> 
> Dodatkowo, jeśli chodzi o przypisanie dokumentów – zaimplementowałem wzorzec **Polymorphic Relations** (Relacje Polimorficzne). Tabela faktur nie ma na sztywno przypiętego klucza obcego tylko do jednej tabeli. Zamiast tego używa kolumn `customer_type` oraz `customer_id`. Pozwala mi to bez ingerencji w schemat SQL (bez tworzenia *sparse columns* pełnych wartości NULL) podpiąć Fakturę do firmy (Customer), agencji, czy nawet fizycznego kierowcy (User). To gwarantuje potężną elastyczność i czystość relacyjną na lata."

---

## 🕒 0:10 - 0:15 | Flow: Wystawienie Faktury Transportowej i Data Integrity (Wymóg 3.2)
**Twoja akcja w UI:** Tworzysz Fakturę dla tego nowo utworzonego Leada. Wpisujesz np. stawkę transportową i zniżkę. Potem zmieniasz jej status, po czym pola blokują się do edycji (lub udajesz próbę edycji z komunikatem błędu).
**Co otwierasz w IDE:** Pliki migracji np. faktur z widocznym rzutowaniem `decimal(12,2)` lub `InvoicePolicy.php`.
**Twój tekst:**
> "Przechodzę do wystawienia faktury transportowej. Zwróćcie proszę uwagę na to, jak obsługuję wartości finansowe. Błędem krytycznym początkujących jest używanie typu `FLOAT` w bazie. `FLOAT` to typ przybliżony, oparty o standard IEEE 754, który prowadzi do absurdalnych błędów matematycznych przy sumowaniu (np. 0.1 + 0.2 = 0.30000000004). W mojej bazie SQL używam wyłącznie twardego, stałoprzecinkowego typu `DECIMAL(12,2)`. 
> 
> Co więcej, dbam o *Data Integrity* (Wymóg 3.2). Mechanizm *Factuur Locking* powiązany z klasą Policy sprawia, że wystawiona faktura odrzuca z poziomu serwera wszelkie operacje `UPDATE` i `DELETE`. To niezbędne w systemach audytowanych (np. przy rewizji skarbowej)."

---

## 🕒 0:15 - 0:20 | Płatność i Integracja Asynchroniczna (Wymóg 3.3, 3.4, 3.5, 3.7)
**Twoja akcja w UI:** Klikasz akcję skopiowania linku do płatności faktury dla klienta i wklejasz go w nowe okno. Pokazujesz piękny portal Stripe `/pay`. (Wyjaśniasz, że wysyłka automatycznych e-maili uruchamia się za sprawą crona w przypadku *przypomnień / Reminders*).
**Co otwierasz w IDE:** `app/Http/Controllers/StripeWebhookController.php` oraz wspominasz o `app/Console/Commands/SendInvoiceReminders.php`.
**Twój tekst:**
> "Oto link, który wysyłamy klientowi. Dlaczego pokazuję go na ekranie, a nie czekam na e-mail? Wynika to z moich założeń architektonicznych z zadania (Wymóg 3.7 i 3.5 dotyczy automatycznych przypomnień windykacyjnych w tle - 'Herinneringsmails'). Te przypomnienia przetwarzane są asynchronicznie przez demona systemowego, odpytującego bazę o zaległości i delegującego wysyłkę e-maili do **Redis/Database Queues**, aby uniknąć przeciążenia procesu PHP.
> 
> Klient wchodzi w link, klika płacę i weryfikuje się przez Stripe (Wymóg 3.3). Co dzieje się w systemie? Stripe uderza w mój Webhook (Wymóg 3.4). Nie modyfikuję rekordów od razu synchronicznie. Loguję to zdarzenie, weryfikuję Hash podpisu, a zapytanie aktualizujące zrzucam znów do Kolejki (Jobs). Dzięki temu nigdy nie zablokuję odpowiedzi do Stripe'a."

---

## 🕒 0:20 - 0:25 | Finał: Generowanie Peppol UBL i Wnioski (Wymóg 3.8)
**Twoja akcja w UI:** Wracasz do panelu Bedrijf. Faktura ma już status "Paid" (Opłacona). Klikasz guzik "Download Peppol UBL" (aby wygenerować standard XML na nowy rok podatkowy). Otwierasz go w IDE/Przeglądarce.
**Co otwierasz w IDE:** `app/Actions/Invoices/GenerateUblXmlAction.php`
**Twój tekst:**
> "Widzimy, że faktura zmieniła status. Na koniec upewniam się, że firma jest gotowa na przepisy o e-fakturowaniu w Belgii (2026 r.). W kodzie zaimplementowałem wzorzec enkapsulacji (Action Class). Wyciągnąłem gigantyczną logikę tworzenia Peppol BIS Billing 3.0 z kontrolerów, upewniając się, że spełnia on standardy architektoniczne (Wymóg 3.8). XML waliduje się natywnym obiektem `DOMDocument` przed zapisaniem na dysku, więc nie zanieczyszczam serwera uszkodzonymi plikami.
>
> Podsumowując, użyłem bazy z poprawnym indeksowaniem (ULID), typów ułamkowych zapobiegających wyciekom pamięci w PHP (Decimal), w pełni izolowanego Multi-tenancy oraz asynchronicznych kolejek.
> Dziękuję za uwagę i zapraszam do zadawania pytań, zwłaszcza z zakresu struktury bazy danych."

---

### Jak ugrać tym skryptem +50 punktów u nauczyciela bazo-danowca?
- Będzie chciał dopytywać. Jeśli zapyta o "Dlaczego ulepszyłeś ID na ULID?", wyjaśnij jeszcze raz powoli, że UUID zabija cache serwera bazodanowego i powoduje Page Splits w strukturze B-Tree (drzewo binarne), a ULID działa jak auto-increment, ale zachowuje rozproszenie znaków.
- Jeśli zapyta "Co jeśli mam 5 milionów faktur w jednym miesiącu?", odpowiedz: "Indeksuje odpowiednio złączenia polimorficzne oraz korzystam z Eager Loadingu w Eloquent, co minimalizuje problem n+1 queries".
