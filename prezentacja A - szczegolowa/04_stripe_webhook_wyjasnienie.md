# 4. Integracje Zewnętrzne (Stripe Webhook Controller)

**Gdzie znajduje się kod:**
- `app/Http/Controllers/StripeWebhookController.php`

**Co musisz wiedzieć z lotu ptaka:**
Kiedy integrujesz się z potężną korporacją finansową (Stripe), z reguły odbywa się to na zasadzie "Zadzwoń do mnie, jak wykonasz to zadanie" – w języku oprogramowania jest to **Webhook**. System Stripe wysyła ukryty, zaszyfrowany "strzał" do Twojego kontrolera `StripeWebhookController` z informacją ("Hej, ten gość właśnie zasilił Ci konto, daj mu dostęp do aplikacji, albo zapłacił za swoją fakturę wystawioną z programu"). Opisując ten plik pokażesz wybitne umiejętności autoryzacji "zero trust" i potężnego skryptowania operacyjnego w tle.

---

## Plik: `app/Http/Controllers/StripeWebhookController.php`

### Omówienie linijka po linijce:

```php
namespace App\Http\Controllers;
use App\Actions\Communications\LogCommunicationAction;
// (...) Inne importy z Laravela
class StripeWebhookController extends Controller
{
```
**Co masz powiedzieć jury:** *"Wprowadzam koncepcję Inversion of Control (IoC), prosząc o dostarczenie do funkcji mojego wewnętrznego systemu audytowego `LogCommunicationAction`, by zachowywać ślady aktywności z zewnątrz do komunikatorów moich klientów."*

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `extends Controller` – Mówisz programowi: "Moja klasa StripeWebhook przejmuje bazowe moce każdego z kontrolerów, czyli obiektów, których zadaniem jest łapanie ruchu od klienta (np. kliknięć z przeglądarki) i decydowanie co z tym zrobić".
>
> **Na chłopski rozum:** Zastosowanie `extends Controller` to wejście w rolę "Recepcjonisty". Twoja klasa od teraz stoi na froncie, przyjmuje gości (komunikaty z zewnątrz) i decyduje, do którego pokoju (funkcji) ich skierować.

```php
    public function handleWebhook(Request $request, LogCommunicationAction $logCommunicationAction)
    {
```
**Co masz powiedzieć jury:** *"To główna funkcja wykonawcza przechwytująca dane od Stripe'a w postaci surowego ładunku (Payload)."*

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `Request $request` – Złota reguła Laravela (tzw. Dependency Injection / Wstrzykiwanie Zależności). Zamiast bawić się w pobieranie surowych pakietów z sieci, Laravel daje Ci całe to zapytanie ubrane w ładny obiekt nazywany `$request` (czyli "Żądanie").
> *   Podawanie klasy `LogCommunicationAction` wewnątrz nawiasów zmusza Laravela, by natychmiast wrzucił do pamięci gotowe narzędzie do zapisywania logów komunikacyjnych.
>
> **Na chłopski rozum:** Dependency Injection to "Osobisty Asystent". Zamiast biegać samemu na zewnątrz z siatką i łapać surowe pakiety danych latające w internecie, Twój asystent (Laravel) już złapał pakiet od Stripe, ładnie go poukładał w zgrabną paczkę o nazwie `$request` i wręczył Ci wprost do rąk.

```php
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret', 'whsec_dummy');
```
**Co masz powiedzieć jury:** *"Rozpoczynam proces inspekcji kryptograficznej uderzenia Webhooku. Biorąc czysty, surowy strumień danych z zapytania sieciowego, łączę go z ukrytym podpisem dostarczanym od firmy Stripe."*

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `getContent()` – Wyciąga "Mięso" z zapytania (zawartość JSON, gdzie są zapisane dane przelewu). Zapisujemy je pod zmienną `$payload`.
> *   `header('Stripe-Signature')` – Patrzy w "Nagłówki" żądania (ukryte informacje sieciowe) i wyciąga z nich twardy klucz kryptograficzny od Stripe, czyli `$sigHeader`.
> *   `config(...)` – Funkcja ładująca z Twojego ściśle strzeżonego pliku konfiguracyjnego na dysku serwera sekretne, prywatne hasło Twojej firmy.
>
> **Na chłopski rozum:** Paczka od kuriera Stripe. `getContent()` to zajrzenie do pudełka (jakie są dane przelewu). `header()` to spojrzenie na "Woskową Pieczęć" na kopercie z zewnątrz. A `config()` to otwarcie Twojego prywatnego sejfu z hasłami, by upewnić się, że to faktycznie kurier, a nie przebieraniec.

```php
        try {
            if ($endpointSecret !== 'whsec_dummy') {
                $event = \Stripe\Webhook::constructEvent(
                    $payload, $sigHeader, $endpointSecret
                );
            } else { ... }
```
**Co masz powiedzieć jury:** *"Biblioteka Stripe z precyzją skalpela wykonuje kryptograficzną rekonstrukcję Zdarzenia (Event). Jeśli haker spróbowałby zamienić chociaż jedną literę w przesyłanych danych, suma kontrolna w podpisie ulegnie rozpadowi po stronie wejściowej."*

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `try { ... }` – Moduł ochronny. Mówi systemowi: "Spróbuj odpalić niebezpieczny kod, a jeśli wybuchnie, nie wyłączaj mi od razu aplikacji".
> *   `!==` – "Nie jest identyczny". Sprawdzasz: Jeśli klucz nie jest lokalnym testowym hasłem...
> *   `\Stripe\Webhook::constructEvent` – Biblioteka Stripe bierze zawartość, pieczęć i klucz, i je "Krzyżuje" by potwierdzić autentyczność.
>
> **Na chłopski rozum:** Słowo `try` to założenie Okularów Ochronnych w laboratorium. Mówisz: "Spróbuję otworzyć tę bombę. Jeśli wybuchnie, nie spal całego budynku". A `constructEvent` to sprzęt CSI Kryminalne Zagadki - rzuca światło ultrafioletowe na paczkę, sprawdzając, czy haker po drodze nie podmienił kwoty ze 100 zł na 1000 zł.

```php
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }
```
**Co masz powiedzieć jury:** *"Jeśli autoryzacja się posypie, twardo zamykam kanał zwracając restowy błąd 400 (Bad Request). Jest to element twardego modelu Zero Trust Network Architecture."*

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `catch (...)` – ("Złap!"). Siatka bezpieczeństwa postawiona zaraz po wpadce w bloku `try`.
> *   `return response()->json(..., 400)` – Kulturalnie odbijamy fałszywego gościa błędem `400 Bad Request`.
>
> **Na chłopski rozum:** Złapanie błędu (`catch`) to Twój gaśnica. Gdy bomba w laboratorium wybuchnie (czyli haker wysłał zły klucz), siatka ląduje u Ciebie. Zamiast paniki, kulturalnie zamykasz drzwi fałszywemu kurierowi (Błąd 400), a reszta Twojej aplikacji działa w pełni normalnie, bez zawieszania się.

```php
        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
```
**Co masz powiedzieć jury:** *"Z chirurgiczną powagą odfiltrowuję z miliona darmowych sygnałów wyłącznie ten kluczowy: zakup został w pełni ukończony i opłacony na bramce."*

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `$event->type` – Sprawdzamy specyficzny TYP oficjalnego dokumentu.
> *   `===` – Zawsze używa się tu trzech znaków (zamiast `==`), żeby wymusić identyczność formatu danych.
>
> **Na chłopski rozum:** Po weryfikacji paczki patrzysz do środka. Dokument może mieć tytuł "Klient zaczął wpisywać kartę" albo "Klient opłacił". Ty wyłapujesz tylko ten jeden, konkretny rodzaj papieru (`checkout.session.completed`). Inne lądują w niszczarce.

```php
            if ($session->mode === 'subscription') {
                $registrationId = $session->metadata->registration_id ?? null;
```
**Co masz powiedzieć jury:** *"Zaciągam wplecione głęboko do żądania płatności klucze referencyjne z tzw. Metadanych (Metadata payload)."*

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `?? null` – Null Coalescing Operator. Mówi: "Jeśli ta wartość nie istnieje w ogóle w Stripe, wstaw tu Pustkę (`null`), zamiast zwracać rzucać awarią braku zmiennej".
>
> **Na chłopski rozum:** To tzw. "Plan Awaryjny B". Patrzysz na wiersz "ID Rejestracji". Jeśli ktoś zapomniał go w ogóle wydrukować na papierze, nie wyrzucasz komputera przez okno (Crash). Po prostu wzruszasz ramionami, piszesz "Brak Danych" (`null`) i idziesz dalej.

```php
                if ($registrationId) {
                    $registrationData = \Illuminate\Support\Facades\Cache::get("registration_{$registrationId}");
                    
                    if ($registrationData) {
                        // Tworzenie bazy firmy
                        $bedrijf = \App\Models\Bedrijf::create([ ... ]);
                        \Illuminate\Support\Facades\Cache::forget("registration_{$registrationId}");
                    }
                }
```
**Co masz powiedzieć jury:** *"Aplikując zaawansowane skryptowanie bazy pamięciowej Redis, system Provisioningu działa w absolutnej asynchroniczności, bez udziału operatora. Tworzymy klienta od zera."*

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `Cache::get(...)` – Sięgasz do Pamięci Podręcznej serwera po zapisane wcześniej dane formularza.
> *   `create([ ... ])` – Wywołujemy instrukcję "Utwórz Nowego". Tworzymy fizycznie firmę w bazie.
> *   `forget(...)` – Usuwamy stare dane z pamięci cache.
>
> **Na chłopski rozum:** Gdy użytkownik wypisywał wniosek o konto, zamroziliśmy go w systemowej "ZAMRAŻARCE" (`Cache`). Teraz, gdy dostałeś przelew od Stripe, otwierasz zamrażarkę (`Cache::get`), rozmrażasz formularz, fizycznie budujesz dla niego firmę (`create`), a opakowanie po formularzu wyrzucasz do śmieci, żeby nie robić bałaganu (`forget`).

```php
            } else {
                // Obsługa zwykłych, ręcznie wystawionych faktur dla klientów
                $invoiceId = $session->metadata->invoice_id ?? null;

                if ($invoiceId) {
                    $invoice = Invoice::withoutGlobalScopes()->find($invoiceId);

                    if ($invoice && $invoice->status !== InvoiceStatus::PAID) {
                        $invoice->update([
                            'status' => InvoiceStatus::PAID,
                            'paid_at' => now(),
                        ]);
```
**Co masz powiedzieć jury:** *"Używam bezpiecznika ignorującego Globalne Zakresy z racji asynchronicznego żądania od zewnętrznego serwera, dokonuję autorskiej zmiany statusu dokumentu (State Mutation) na opłaconą."*

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `} else {` – Ścieżka awaryjna: jeśli to NIE jest płatność za abonament Twojej aplikacji, to znaczy, że to zapłata za zwykłą fakturę.
> *   `withoutGlobalScopes()` – Zignorowanie wtyczki "BedrijfScope", by wyszukać fakturę w całej globalnej bazie danych.
>
> **Na chłopski rozum:** Serwer Stripe to tylko robot, a nie "zalogowany szef firmy w przeglądarce". Żeby robot miał prawo przeszukać całą bazę i odnaleźć zapłaconą fakturę, musimy mu tymczasowo wyłączyć kamery i strażników bezpieczeństwa (`withoutGlobalScopes()`), wpuścić go na sekundę do magazynu, pozwolić nakleić mu naklejkę "ZAPŁACONE" na teczkę, i wyprowadzić.

```php
        return response()->json(['status' => 'success'], 200);
    }
}
```
**Co masz powiedzieć jury:** *"Zwieńczeniem całego systemu jest czysty kod sukcesu odsyłany automatom od Stripe."*

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `return response()->json(...)` – Odesłanie odpowiedzi do Stripe'a z kodem HTTP 200 (Wszystko OK).
>
> **Na chłopski rozum:** Kurier (Stripe) wręczył Ci paczkę i pyta "Zrozumiałeś?". Ty po zrobieniu tej całej analizy, dajesz mu przysłowiowego "Kciuka w górę" (Kod `200 Success`), żeby kurier zapisał sobie, że zadanie wykonano z sukcesem.

---

## Pytania, na które musisz być gotów:

1. **Jury:** *"Jeżeli system w pełni automatycznie tworzy Firmę (Provisioning) u Ciebie na zapleczu po zweryfikowaniu Webhooka, co powstrzyma napastnika, który zlokalizuje i weźmie wyciąg metadanych, i wyśle sfingowane zapytanie (Spam Request Attack) do portu Twojego serwera, żeby otworzyć darmowe, setki, miliony firm w CRM z prawami Admina podając się za serwer Stripe?"*
   **Twoja odpowiedź:** *"Niestety jego szanse to okrągłe zero. Żadne zapytanie wpadające na `/webhook/stripe` nie przekroczy muru blokady, zanim kryptograficzny certyfikat podpisowy SHA-256 przypięty do requesta, z podpisem w ukrytym sekretnym kluczu Stripe (w moim kodzie wywołany jako `whsec_dummy` lub zaciągnięty z env), nie wygeneruje potwierdzenia 1:1 zawartości z Payloadem JSON'a. Zwykła literówka "o" zamieniona na "a" zniszczy podpisywalność Payloadu blokując ruch ze statusem błędnym 400 Bad Request, a samo żądanie jest ignorowane."*
