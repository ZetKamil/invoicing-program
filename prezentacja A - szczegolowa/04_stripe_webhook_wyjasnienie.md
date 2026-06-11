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

> **Edukacja dla Ciebie:**
> *   `extends Controller` – Mówisz programowi: "Moja klasa StripeWebhook przejmuje bazowe moce każdego z kontrolerów, czyli obiektów, których zadaniem jest łapanie ruchu od klienta (np. kliknięć z przeglądarki) i decydowanie co z tym zrobić".

```php
    public function handleWebhook(Request $request, LogCommunicationAction $logCommunicationAction)
    {
```
**Co masz powiedzieć jury:** *"To główna funkcja wykonawcza przechwytująca dane od Stripe'a w postaci surowego ładunku (Payload)."*

> **Edukacja dla Ciebie:**
> *   `Request $request` – Złota reguła Laravela (tzw. Dependency Injection). Zamiast bawić się w pobieranie surowych pakietów z sieci, Laravel daje Ci całe to zapytanie ubrane w ładny obiekt nazywany `$request` (czyli "Żądanie").
> *   Podawanie klasy `LogCommunicationAction` wewnątrz nawiasów tej samej funkcji zmusza Laravela, by natychmiast wrzucił do pamięci gotowe narzędzie do zapisywania logów komunikacyjnych (abyś nie musiał tworzyć go ręcznie). O tym mówiłeś w zdaniu dla jury wyżej jako "IoC".

```php
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret', 'whsec_dummy');
```
**Co masz powiedzieć jury:** *"Rozpoczynam proces inspekcji kryptograficznej uderzenia Webhooku. Biorąc czysty, surowy strumień danych z zapytania sieciowego, łączę go z ukrytym podpisem dostarczanym od firmy Stripe."*

> **Edukacja dla Ciebie:**
> *   `getContent()` – Wyciąga tak zwane "Mięso" z zapytania (zawartość JSON, gdzie są zapisane dane przelewu). Zapisujemy je pod zmienną `$payload`.
> *   `header('Stripe-Signature')` – Patrzy w "Nagłówki" żądania (ukryte informacje sieciowe, o których zwykły użytkownik nie ma pojęcia) i wyciąga z nich twardy klucz kryptograficzny od Stripe, czyli `$sigHeader`.
> *   `config(...)` – Funkcja ładująca z Twojego ściśle strzeżonego pliku konfiguracyjnego na dysku serwera sekretne, prywatne hasło Twojej firmy. 

```php
        try {
            if ($endpointSecret !== 'whsec_dummy') {
                $event = \Stripe\Webhook::constructEvent(
                    $payload, $sigHeader, $endpointSecret
                );
            } else { ... }
```
**Co masz powiedzieć jury:** *"Biblioteka Stripe z precyzją skalpela wykonuje kryptograficzną rekonstrukcję Zdarzenia (Event). Jeśli haker spróbowałby zamienić chociaż jedną literę w przesyłanych danych, suma kontrolna w podpisie ulegnie rozpadowi po stronie wejściowej."*

> **Edukacja dla Ciebie:**
> *   `try { ... }` – Moduł ochronny ("Spróbuj to zrobić..."). Mówi systemowi: Spróbuj odpalić niebezpieczny/narażony na awarię kod, a jeśli wybuchnie, nie wyłączaj mi od razu aplikacji (złapiemy to w tzw. catchu dalej).
> *   `!==` – "Nie jest identyczny". Sprawdzasz: Jeśli klucz nie jest lokalnym testowym hasłem "dummy"...
> *   `\Stripe\Webhook::constructEvent` – Biblioteka Stripe pobiera do rąk Twoje trzy klucze wyciągnięte wyżej i "Krzyżuje je". Jeśli matematyka szyfrowania SHA-256 się zgodzi, powstanie pełnoprawny, autoryzowany obiekt wydarzenia o nazwie `$event`. Jeśli jest zły – wysadzi ten kod w powietrze.

```php
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }
```
**Co masz powiedzieć jury:** *"Jeśli autoryzacja się posypie, twardo zamykam kanał zwracając restowy błąd 400 (Bad Request). Jest to element twardego modelu Zero Trust Network Architecture."*

> **Edukacja dla Ciebie:**
> *   `catch (...)` – ("Złap!"). To siatka bezpieczeństwa postawiona zaraz za słówkiem `try`. Jeśli wyżej wyrzucono awarię, program nie sypie się na białym ekranie 500, ale spadochronuje bezpiecznie tutaj, a my decydujemy, co zrobić.
> *   `return response()->json(..., 400)` – Kulturalnie odbijamy fałszywego gościa uderzającego w API błędem `400 Bad Request`, i odcinamy go, oszczędzając zasoby serwera.

```php
        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
```
**Co masz powiedzieć jury:** *"Z chirurgiczną powagą odfiltrowuję z miliona darmowych sygnałów wyłącznie ten kluczowy: zakup został w pełni ukończony i opłacony na bramce."*

> **Edukacja dla Ciebie:**
> *   `$event->type` – Ponieważ przeszliśmy zbrodniczą walidację wyżej, to już jest nasz pełen ufności obiekt Zdarzenia. Możemy do niego wejść za pomocą strzałki `->` i sprawdzić np. jego TYP (czy to jest opłacona faktura? Czy wycofana karta?).
> *   `===` – Zawsze używa się tu trzech znaków, a nie dwóch (`==`), żeby zachować tzw. rygorystyczne typowanie danych.

```php
            if ($session->mode === 'subscription') {
                $registrationId = $session->metadata->registration_id ?? null;
```
**Co masz powiedzieć jury:** *"Zaciągam wplecione głęboko do żądania płatności klucze referencyjne z tzw. Metadanych (Metadata payload)."*

> **Edukacja dla Ciebie:**
> *   `?? null` – Operator z PHP. Mówi po prostu: "Zanurkuj głęboko do informacji płatności ($session->metadata) i weź to ID rejestracji. A jeśli z jakiegoś powodu Stripe go do nas nie wysłał w ogóle... to nie zgłaszaj błędu pustej zmiennej, tylko po prostu ustaw wartość tej zmiennej awaryjnie na `null` (Pustkę)".

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

> **Edukacja dla Ciebie:**
> *   `Cache::get(...)` – Sięgasz do tzw. Pamięci Podręcznej komputera. Gdy użytkownik kilka minut temu wypisywał formularz "Kupuję Abonament", system zamroził jego formularz w pamięci pod nazwą ID rejestracji. Teraz, gdy opłata przeszła ze Stripe, z powrotem go odmrażamy.
> *   `create([ ... ])` – Wywołujemy w systemie akcję "Utwórz Nowego". Tworzymy fizycznie nowego klienta w bazie i nadajemy mu uprawnienia.
> *   `forget(...)` – Bardzo ważne. Czyścimy pamięć podręczną ze śmieci. Użytkownik opłacony, nie przechowujemy już tego zamrożonego formularza dłużej.

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

> **Edukacja dla Ciebie:**
> *   `} else {` – Skręt w drugą stronę. Jeśli Stripe nam wysłał potwierdzenie zapłaty, ale to NIE JEST abonament naszej firmy SaaSowej, to co to jest? To jest po prostu Zwykła Faktura (Kowalski opłacił fakturę transportową).
> *   `withoutGlobalScopes()` – Zobacz, nie zapomnieliśmy o tym! Stripe puka "z zewnątrz", nikt nie jest formalnie "Zalogowany w systemie jako użytkownik", więc żeby system mógł podpiąć opłatę do faktury, musimy powiedzieć mu "Ściągnij na chwilę barierę ochronną, zaufaj, Stripe dostarczył hajs, oznacz fakturę jako PAID (Zapłacona)".

```php
        return response()->json(['status' => 'success'], 200);
    }
}
```
**Co masz powiedzieć jury:** *"Zwieńczeniem całego systemu jest czysty kod sukcesu odsyłany automatom od Stripe."*

> **Edukacja dla Ciebie:**
> *   `return response()->json(...)` – Serwer kulturalnie odpowiada paczką formatu JSON na zewnątrz: "Dzięki Stripe, wykonałem zadanie. Zwracam Ci status `200 Success`".

---

## Pytania, na które musisz być gotów:

1. **Jury:** *"Jeżeli system w pełni automatycznie tworzy Firmę (Provisioning) u Ciebie na zapleczu po zweryfikowaniu Webhooka, co powstrzyma napastnika, który zlokalizuje i weźmie wyciąg metadanych, i wyśle sfingowane zapytanie (Spam Request Attack) do portu Twojego serwera, żeby otworzyć darmowe, setki, miliony firm w CRM z prawami Admina podając się za serwer Stripe?"*
   **Twoja odpowiedź:** *"Niestety jego szanse to okrągłe zero. Żadne zapytanie wpadające na `/webhook/stripe` nie przekroczy muru blokady, zanim kryptograficzny certyfikat podpisowy SHA-256 przypięty do requesta, z podpisem w ukrytym sekretnym kluczu Stripe (w moim kodzie wywołany jako `whsec_dummy` lub zaciągnięty z env), nie wygeneruje potwierdzenia 1:1 zawartości z Payloadem JSON'a. Zwykła literówka "o" zamieniona na "a" zniszczy podpisywalność Payloadu blokując ruch ze statusem błędnym 400 Bad Request, a samo żądanie jest ignorowane."*
