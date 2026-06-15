# 7. Faza 3 — Deep Dive: Hardening Architektury Enterprise

**Gdzie znajduje się kod (zmieniony w Fazie 3):**
- `app/Filament/Resources/Quotes/Pages/EditQuote.php` — Fix async email
- `app/Providers/AppServiceProvider.php` — Policy registration + Rate limiters
- `app/Models/QuoteItem.php` — Eager loading dla Policy N+1
- `app/Models/InvoiceItem.php` — Eager loading dla Policy N+1
- `database/migrations/2026_06_10_000001_optimize_phase3_indexes.php` — Indeksy Fazy 3
- `routes/web.php` — Named throttle middleware

**Co musisz wiedzieć z lotu ptaka:**
W fazie 3 przeszliśmy przez własny kod jak zewnętrzny audytor bezpieczeństwa klasy enterprise. Każdy z 4 obszarów (asynchroniczność, autoryzacja, baza danych, rate limiting) miał konkretny gap między tym co wyglądało dobrze a tym co działało bezpiecznie pod prawdziwym obciążeniem. Poniżej — linia po linii — każda zmiana z uzasadnieniem inżynierskim.

---

## ZMIANA 1: `EditQuote.php` — Naprawa Synchronicznego Emaila

### Problem (linia 60, przed zmianą):
```php
Mail::to($record->lead->email)->send(new QuoteInquiryMail($record));
```

### Dlaczego to jest Bug Enterprise-Class:

Klasa `QuoteInquiryMail` implementuje interfejs `ShouldQueue`. Jednak interfejs ten jest tylko **deklaracją**, nie automatyzacją. Metoda `->send()` ignoruje `ShouldQueue` i wykonuje email **bezpośrednio w wątku HTTP obsługującym żądanie Filamenta**.

Pełna ścieżka wykonania PRZED:
1. Pracownik klika "Wyślij Ofertę" w panelu
2. Filament wysyła żądanie `POST /dashboard/quotes/{id}`
3. PHP Worker (jeden z puli WAMP) *wstrzymuje się* na połączenie SMTP
4. Jeśli SMTP odpowiada w 3 sekundy → użytkownik czeka 3 sekundy
5. Jeśli SMTP jest niedostępny → `500 Internal Server Error` wprost w UI
6. Jeśli równocześnie 10 pracowników wysyła oferty → 10 wątków blokuje się jednocześnie → aplikacja traci responsywność dla WSZYSTKICH

### Naprawa (jedna zmiana, po):
```php
// Queue the email — QuoteInquiryMail implements ShouldQueue, so we must
// use ->queue() (not ->send()) to avoid blocking the HTTP worker thread.
Mail::to($record->lead->email)->queue(new QuoteInquiryMail($record));
```

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `ShouldQueue` – Pusty interfejs (certyfikat), który nakleja się na klasę mailową.
> *   `send()` – Uruchomienie synchroniczne.
> *   `queue()` – Uruchomienie asynchroniczne (w tle).
>
> **Na chłopski rozum:** 
> Interfejs `ShouldQueue` to po prostu naklejka na liście z napisem: "Proszę wysłać to później w tle". 
> Kiedy używałeś `send()`, stawałeś przed listonoszem i krzyczałeś: "Wyślij to TERAZ przy mnie, nie obchodzą mnie naklejki!". I musiałeś stać przy nim 5 sekund tracąc czas. Zmiana na `queue()` to po prostu wrzucenie tego listu z naklejką do skrzynki i pójście do domu. System z tyłu (Worker) zajmie się resztą, a Twoja przeglądarka odzyskuje wolność w 1 milisekundę.

---

## ZMIANA 2: `AppServiceProvider.php` — Jawna Rejestracja Polityk

### Problem (przed zmianą):

```php
// BRAK jakiejkolwiek rejestracji polityk
public function boot(): void
{
    $this->configureDefaults();
    // ...observers...
}
```

Laravel automatycznie łączył `Invoice` → `InvoicePolicy` przez naming convention. To działało. Ale:

**Scenariusz awarii:**
```
Developer refaktoryzuje: mv app/Models/Invoice.php app/Models/Billing/Invoice.php
→ Namespace zmienia się na App\Models\Billing\Invoice
→ Convention auto-discovery szuka App\Policies\BillingInvoicePolicy (nie istnieje)
→ Policy nie jest rejestrowana
→ authorize('update', $invoice) → ZAWSZE TRUE (brak polityki = dostęp przyznany)
→ Każdy zalogowany użytkownik może modyfikować cudze faktury
→ BRAK BŁĘDU W LOGACH
```

### Naprawa — Explicit Policy Registration:

```php
protected function registerPolicies(): void
{
    Gate::policy(Lead::class,        LeadPolicy::class);
    Gate::policy(Quote::class,       QuotePolicy::class);
    Gate::policy(QuoteItem::class,   QuoteItemPolicy::class);
    Gate::policy(Invoice::class,     InvoicePolicy::class);
    Gate::policy(InvoiceItem::class, InvoiceItemPolicy::class);
    Gate::policy(Product::class,     ProductPolicy::class);
    Gate::policy(Post::class,        PostPolicy::class);
}
```

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `Gate::policy()` – Twarda deklaracja wiążąca bazę danych z klasą ochronną.
>
> **Na chłopski rozum:** W systemie bezpieczeństwa panuje zasada "Fail-Secure" (Bezpieczeństwo przy awarii). Jak drzwi w banku stracą prąd, to mają się ZAMKNĄĆ na głucho, a nie otworzyć na oścież. Auto-discovery to otwieranie się drzwi przy awarii (zmianie nazwy). Twarda rejestracja `Gate::policy` to zamek elektroniczny — jak system nie znajdzie odpowiedniego strażnika na wydrukowanej liście, to rzuca błędem i nikogo nie wpuszcza.

---

## ZMIANA 3: `QuoteItem.php` i `InvoiceItem.php` — Eliminacja N+1 na Poziomie Polityk

### Problem (QuoteItemPolicy, linia 42):

```php
public function update(User $user, QuoteItem $quoteItem): bool
{
    return $user->bedrijf_id === $quoteItem->quote->bedrijf_id; // LAZY LOAD!
}
```

Kiedy Filament renderuje listę 50 pozycji oferty:
1. Pobiera 50 `QuoteItem` z bazy → 1 zapytanie
2. Dla każdego wywołuje policy `update()` → 50 lazy-load na `$quoteItem->quote` → 50 dodatkowych zapytań
3. Łącznie: **51 zapytań zamiast 1**

### Naprawa — Eager Loading w Modelu:

```php
// QuoteItem.php
protected $with = ['quote'];

// InvoiceItem.php
protected $with = ['invoice'];
```

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `protected $with` – Zmusza model do zabierania ze sobą relacji przy każdym pobraniu z bazy.
>
> **Na chłopski rozum:** Brak `$with` to wezwanie kelnera 50 razy po jedną frytkę, bo zapomniałeś poprosić o cały talerz naraz. Wpisanie `$with = ['quote']` to żądanie całego talerza. Kelner idzie do kuchni (bazy) raz, przynosi Ci 50 frytek i kiedy system autoryzacji sprawdza 50 pozycji, nie musi już ganiać do kuchni 50 razy. Spadek zapytań z 51 do 2.

---

## ZMIANA 4: Migracja `2026_06_10_000001_optimize_phase3_indexes.php`

### Dlaczego SQLite nie tworzy Indeksów dla Foreign Keys:

```php
// Ten kod (Faza 2) WYDAJE SIĘ tworzyć indeks...
$table->foreignUlid('quote_id')->constrained('quotes')->cascadeOnDelete();

// ...ale w SQLite: FK constraint ≠ FK index!
// MySQL/PostgreSQL tworzą indeks automatycznie. SQLite — NIE.
// Wynik: $quote->items() = full table scan na quote_items.
```

### Dodane Indeksy i Ich Uzasadnienie:

```php
// 1. Naprawia relację $quote->items() — O(n) → O(log n)
Schema::table('quote_items', fn ($t) =>
    $t->index('quote_id', 'quote_items_quote_id_index'));

// 2. Naprawia relację $invoice->items() — O(n) → O(log n)
Schema::table('invoice_items', fn ($t) =>
    $t->index('invoice_id', 'invoice_items_invoice_id_index'));

// 3. Optymalizuje nocny cron SendInvoiceReminders
// Zapytanie: WHERE bedrijf_id = ? AND status = 'sent' AND due_date < ?
Schema::table('invoices', fn ($t) =>
    $t->index(['bedrijf_id', 'due_date'], 'invoices_bedrijf_due_date_index'));

// 4. Optymalizuje widok historii komunikacji per faktura/oferta
Schema::table('communications', fn ($t) =>
    $t->index(
        ['bedrijf_id', 'related_type', 'related_id'],
        'communications_bedrijf_related_index'
    ));
```

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `$t->index(...)` – Tworzy strukturę danych B-Tree wokół wskazanej kolumny (lub kilku kolumn).
>
> **Na chłopski rozum:** Jeśli masz tabelę ze 100,000 faktur, nocny robot wysyłający maile z przypomnieniem o zapłacie musiał fizycznie "przejrzeć" wszystkie 100,000 teczek w magazynie, żeby znaleźć te przeterminowane. Stworzenie indeksu `$t->index` to jak ułożenie teczek datami i wsadzenie kolorowych zakładek z napisem "Przeterminowane są w tej przegródce". Robot idzie prosto do nich w 3 milisekundy.

---

## ZMIANA 5: `AppServiceProvider.php` — Named Rate Limiters

### Dlaczego `throttle:60,1` Jest Za Słabe dla Produkcji:

```php
// STARE — anonimowy limiter bez możliwości customizacji
Route::post('/webhook/stripe', [...])->middleware('throttle:60,1');
```

Problemy:
- Stripe nigdy nie wyśle 60 webhooków na minutę z jednego IP — limit jest za wysoki
- Brak nagłówka `Retry-After` w odpowiedzi
- Brak niestandardowej odpowiedzi JSON (zwraca HTML)
- Portal `/pay/{invoice}` nie miał **żadnego** limitu

### Rozwiązanie — Named Rate Limiters:

```php
// AppServiceProvider::configureRateLimiters()
RateLimiter::for('stripe-webhooks', function (Request $request) {
    return Limit::perMinute(30)
        ->by($request->ip())
        ->response(function () {
            return response()->json(
                ['error' => 'Too many webhook requests. Slow down.'],
                429
            );
        });
});

RateLimiter::for('invoice-portal', function (Request $request) {
    return Limit::perMinute(10)->by($request->ip());
});
```

```php
// routes/web.php
Route::post('/webhook/stripe', [...])
    ->middleware('throttle:stripe-webhooks'); // Nowy named limiter

Route::get('/pay/{invoice}', InvoicePayPortal::class)
    ->middleware('throttle:invoice-portal'); // Nowe zabezpieczenie portalu
```

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `RateLimiter::for(...)` – Tworzy i nazywa precyzyjną regułę blokującą.
> *   `Limit::perMinute(...)` – Matematyczny limit ilości odwiedzin.
>
> **Na chłopski rozum:** Zwykły `throttle` to tępy bramkarz z darmowym licznikiem kliknięć w ręce. Wpuści każdego, kto zdąży kliknąć 60 razy. Wysłanie niestandardowej odpowiedzi JSON (`response(...)`) i limitowanie do IP to tak, jakby dać temu bramkarzowi tablet, listę gości z nazwiskami i kazać mu drukować grzeczne bileciki z napisem "Przepraszamy, za duży tłok, wróć za 10 minut". 

---

## Pytania Jury — Faza 3 Deep Dive:

1. **Jury:** *"Skoro mail klasa implementuje ShouldQueue to czyż nie jest automatycznie kolejkowana?"*
   **Twoja odpowiedź:** *"To jest jeden z najpowszechniejszych mitów o Laravelu. ShouldQueue to interfejs-marker — 'zamiar', nie 'akcja'. Dopiero wywołanie `->queue()` aktywuje system kolejkowania. `->send()` jest kompletnie niezależne od tego interfejsu i zawsze wykonuje synchronicznie. Audyt fazy 3 wykrył dokładnie ten bug — elegancja deklaracji przysłaniała krytyczny błąd wykonania."*

2. **Jury:** *"Ile zapytań SQL wykonuje Filament wyświetlając listę 50 pozycji oferty przed i po naprawie?"*
   **Twoja odpowiedź:** *"Przed: 51 zapytań — 1 na listę QuoteItem, plus 50 lazy-loadów relacji `quote` przez Policy. Po dodaniu `protected $with = ['quote']`: 2 zapytania — 1 na QuoteItem z JOIN na Quote. To 96% redukcja obciążenia bazy danych na każdy render tej listy."*

3. **Jury:** *"Dlaczego SQLite nie tworzy indeksów dla kluczy obcych automatycznie?"*
   **Twoja odpowiedź:** *"To fundamentalna różnica między silnikami bazodanowymi. MySQL i PostgreSQL automatycznie tworzą B-Tree index obok każdego klucza obcego. SQLite — zgodnie ze swoją dokumentacją — nie. Definiując foreignUlid()->constrained() dodajemy tylko constraint integralności, nie indeks wyszukiwania. W naszym przypadku oznaczało to, że każde wywołanie $quote->items() wykonywało pełny skan tabeli quote_items. Migracja fazy 3 naprawiła to przez jawne ->index() na kolumnach FK."*
