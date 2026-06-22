# 🛡️ Skrypt Obrony - Wersja Bardzo Szczegółowa (Kod + UI)

Ten skrypt poprowadzi Cię krok po kroku. Dokładnie wiesz, w co kliknąć na ekranie, jaki plik otworzyć w IDE (np. VS Code), którą linijkę zaznaczyć myszką i co DOKŁADNIE powiedzieć, żeby zabrzmieć jak profesjonalista.

Przepływ to: **Klant -> Offerte -> Factuur**.

---

## 🕒 KROK 1: Tworzenie Klienta (Walidacja i Bezpieczeństwo)

**Akcja na ekranie (UI):** 
Logujesz się do panelu, wchodzisz w `Klanten` (Klienci) i klikasz `Nieuwe klant` (Nowy Klient). Próbujesz zapisać pusty formularz, żeby pokazać czerwone błędy walidacji.

**Kod do otwarcia w IDE:** 
Otwórz plik `app/Filament/Resources/Customers/Schemas/CustomerForm.php`

**Zaznacz myszką w kodzie:** 
Linijka 17: `->required()` oraz Linijka 31: `->email()`

**Co mówisz (Słowo w słowo):**
> "Jak widzicie, formularz odrzuca puste lub błędne dane. Żeby to osiągnąć, w pliku `CustomerForm.php` użyłem wbudowanych metod wideo. Zaznaczając `->required()` wymuszam obecność nazwy firmy. Metoda `->email()` to gotowy walidator Laravela, który pod spodem używa wyrażeń regularnych (Regex), by upewnić się, że struktura maila jest poprawna. Chroni to moją bazę danych przed śmieciowymi wpisami."

---

## 🕒 KROK 2: Ochrona Danych (Multi-Tenant & Global Scopes)

**Akcja na ekranie (UI):** 
Wpisujesz poprawne dane klienta i klikasz Zapisz. Wraca Cię do listy klientów, gdzie widać Twojego nowego klienta.

**Kod do otwarcia w IDE:** 
Otwórz plik `app/Models/Scopes/BedrijfScope.php`

**Zaznacz myszką w kodzie:** 
Linijka 16 (lub okoliczna): `$builder->where($model->getTable() . '.bedrijf_id', $user->bedrijf_id);`

**Co mówisz (Słowo w słowo):**
> "Klient został zapisany. Ale skąd system wie, że to mój klient, a nie innej firmy transportowej na tym serwerze? Zastosowałem wzorzec **Global Scope**. Ten plik, który teraz pokazuję, automatycznie wstrzykuje klauzulę SQL `WHERE bedrijf_id = X` do **każdego** zapytania w systemie. Nie muszę o tym pamiętać w kontrolerach. To gwarantuje absolutną hermetyczność danych – wyciek faktur między firmami jest niemożliwy."

---

## 🕒 KROK 3: Tworzenie Wyceny (Relacje Bazy Danych)

**Akcja na ekranie (UI):** 
Wchodzisz w zakładkę `Offertes` (Wyceny) i klikasz "Nieuwe offerte". Z listy rozwijanej wybierasz klienta, którego przed chwilą stworzyłeś.

**Kod do otwarcia w IDE:** 
Otwórz plik `app/Models/Quote.php`

**Zaznacz myszką w kodzie:** 
Linijka 40-43: `public function customer(): BelongsTo` oraz Linijka 58-61: `public function items(): HasMany`

**Co mówisz (Słowo w słowo):**
> "Teraz tworzę wycenę. Zamiast pisać surowe zapytania SQL z `JOIN`, używam systemu ORM o nazwie Eloquent. W modelu `Quote.php` zdefiniowałem relacje. `BelongsTo` oznacza, że ta wycena 'Należy Do' konkretnego klienta. Z kolei `HasMany` oznacza, że ta sama wycena może mieć wiele pozycji (np. różne ładunki czy usługi dodatkowe). Laravel sam tłumaczy to pod spodem na zapytania SQL, co sprawia, że kod jest krótki i obiektowy."

---

## 🕒 KROK 4: Generowanie Faktury (Transakcje i BCMath)

**Akcja na ekranie (UI):** 
Zapisujesz wycenę, wchodzisz w jej podgląd (lub edycję) i klikasz przycisk akcji "Generuj Fakturę" (Convert to Invoice). Zostajesz przeniesiony na gotową fakturę.

**Kod do otwarcia w IDE:** 
Otwórz plik `app/Actions/Invoices/CreateInvoiceFromQuoteAction.php`

**Zaznacz myszką w kodzie:** 
Poszukaj w pliku słowa `DB::transaction` oraz funkcji matematycznych typu `bcmul` lub `bcadd`.

**Co mówisz (Słowo w słowo):**
> "Jednym kliknięciem zamieniłem wycenę w gotową fakturę. Spójrzcie do pliku `CreateInvoiceFromQuoteAction.php`. Po pierwsze, cały proces przepisywania danych z wyceny jest owinięty w `DB::transaction`. Jeśli po drodze wystąpi błąd serwera, baza danych wycofa zmianę i nie zapisze uciętej, pustej faktury. 
> Po drugie, popatrzcie na obliczanie podatku. Używam funkcji `bcmul` i `bcadd`. W PHP standardowe liczby zmiennoprzecinkowe (typu float) mogą generować błędy ułamkowe (tzw. floating point drift). BCMath wykonuje operacje na stringach (tekstach), dając 100% precyzji co do centa, co jest wymogiem przy fakturowaniu."

---

## 🕒 KROK 5: Płatność Online (Stripe i Webhooki)

**Akcja na ekranie (UI):** 
Pokazujesz publiczny portal faktury (z perspektywy klienta) i klikasz "Zapłać przez Stripe". Przechodzisz przez testową płatność. Wracasz do panelu i pokazujesz, że faktura automatycznie zmieniła status na "Zapłacona".

**Kod do otwarcia w IDE:** 
Otwórz plik `app/Http/Controllers/StripeWebhookController.php`

**Zaznacz myszką w kodzie:** 
Znajdź sekcję w instrukcji switch: `case 'checkout.session.completed':` oraz linijkę ze zmianą statusu np. `$invoice->update(['status' => InvoiceStatus::PAID]);`

**Co mówisz (Słowo w słowo):**
> "Klient opłacił fakturę online używając karty. Ale skąd nasz system wie, że zapłacił, skoro klient był na stronie Stripe'a? Zaimplementowałem architekturę **Webhooków**. Kiedy płatność przejdzie u operatora, serwery Stripe wysyłają do mojej aplikacji ukryte zapytanie POST (w tle). 
> W tym pliku `StripeWebhookController` odbieram ten sygnał, odczytuję specjalne zdarzenie `checkout.session.completed`, wyciągam ID faktury z zaszyfrowanej paczki (metadata) i aktualizuję status faktury na 'Zapłacona'. Dzieje się to w pełni asynchronicznie i automatycznie, bez klikania przez dyspozytora."

---

## 🛡️ KROK 6: Ogień Pytań (Odpowiedzi na pytania Nauczyciela)

Nauczyciel zapyta o konkrety. Wykuj te odpowiedzi:

### Pytanie 1: "Pokaż mi ten przycisk 'Generuj Fakturę'. Jak on działa na frontendzie? Przeładowuje stronę?"
**Twoja odpowiedź:**
> "Używam biblioteki **Livewire**. Kiedy kliknąłem przycisk, strona nie została przeładowana. Zamiast tego JavaScript w tle (Fetch API) wysłał asynchroniczne zapytanie do serwera. PHP przetworzyło moją klasę `CreateInvoiceFromQuoteAction` i zwróciło z powrotem tylko mały wycinek kodu HTML, który zastąpił przycisk, pokazując zielony komunikat sukcesu. To daje odczucie szybkiej aplikacji SPA (Single Page Application)."

### Pytanie 2: "A jak to zabezpieczyłeś przed podszywaniem się (Cross-Site Request Forgery - CSRF)?"
**Twoja odpowiedź:**
> "Framework Laravel zabezpiecza to natywnie. Do każdego żądania wysyłanego z przeglądarki dołącza unikalny, szyfrowany ciąg znaków zwany **Tokenem CSRF**. Token ten jest generowany przy starcie sesji i ukryty w kodzie HTML lub w nagłówkach HTTP (X-CSRF-TOKEN). Serwer akceptuje zapytanie np. o wygenerowanie faktury tylko wtedy, gdy przesłany token z przeglądarki idealnie pasuje do tokena przechowanego w bezpiecznej sesji na serwerze."

### Pytanie 3: "W pasku URL widzę np. /invoices/2. Co by było, gdybym w przeglądarce zmienił dwójkę na ID faktury innej firmy?"
**Twoja odpowiedź:**
> "Atak typu IDOR (Insecure Direct Object Reference) się nie uda. Na początku prezentacji pokazywałem Państwu plik `BedrijfScope.php`. Zanim Laravel w ogóle wyciągnie fakturę numer 3, dokleja do zapytania `WHERE bedrijf_id = <moje id>`. Baza danych odpowie po prostu `404 Not Found`, ponieważ pod moją firmą nie istnieje faktura z takim ID. Dodatkowo nad modelem czuwa plik `InvoicePolicy.php`, autoryzujący samą próbę wyświetlenia rekordu."

### Pytanie 4: "Przechowujesz sesję. Jak uchronisz użytkownika przed atakiem typu XSS i przejęciem ciasteczka z sesją (Session Hijacking)?"
**Twoja odpowiedź:**
> "W pliku konfiguracyjnym sesji mam włączoną flagę **HTTP-Only** dla ciasteczek (`session.cookie_httponly = true`). To oznacza, że żadne skrypty JavaScript (np. jeśli ktoś wstrzyknąłby złośliwy kod XSS w opis faktury) nie mogą odczytać tego ciasteczka za pomocą `document.cookie`. Sesja jest wymieniana tylko przez przeglądarkę pod maską. Oczywiście ruch idzie po szyfrowanym HTTPS, więc po drodze nikt go nie podsłucha."
