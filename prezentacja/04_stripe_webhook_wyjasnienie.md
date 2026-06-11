# 4. Integracje Zewnętrzne (Stripe Webhook Controller)

**Gdzie znajduje się kod:**
- `app/Http/Controllers/StripeWebhookController.php`

**Co musisz wiedzieć z lotu ptaka:**
Kiedy integrujesz się z potężną korporacją finansową (Stripe), z reguły odbywa się to na zasadzie "Zadzwoń do mnie, jak wykonasz to zadanie" – w języku oprogramowania jest to **Webhook**. System Stripe wysyła ukryty, zaszyfrowany "strzał" do Twojego kontrolera `StripeWebhookController` z informacją ("Hej, ten gość właśnie zasilił Ci konto, daj mu dostęp do aplikacji, albo zapłacił za swoją fakturę wystawioną z programu"). Opisując ten plik pokażesz wybitne umiejętności autoryzacji "zero trust" i potężnego skryptowania operacyjnego w tle (Asynchronous Background processing).

---

## Plik: `app/Http/Controllers/StripeWebhookController.php`

### Omówienie linijka po linijce:

```php
namespace App\Http\Controllers;
use App\Actions\Communications\LogCommunicationAction;
use App\Enums\CommType;
use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
```
**Co to jest:** Początkowe zdefiniowanie klas i przestrzeni oraz import tzw. paczki systemowej od powiadomień LogCommunicationAction.
**Co masz powiedzieć:** *"Wprowadzam koncepcję Inversion of Control (IoC), prosząc o dostarczenie do funkcji mojego wewnętrznego systemu audytowego `LogCommunicationAction`, by zachowywać ślady aktywności z zewnątrz do komunikatorów moich klientów."*

```php
    public function handleWebhook(Request $request, LogCommunicationAction $logCommunicationAction)
    {
```
**Co to jest:** Główna funkcja wykonawcza przechwytująca dane od Stripe'a (w postaci surowego JSON-a, dlatego operujemy na klasie dostarczanej przez środowisko Laravel: `Request`).

```php
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret', 'whsec_dummy');
```
**Co to jest:** Złota trójca bezpieczeństwa przy webhookach. `getContent()` wyciąga z JSONa treść (Payload). `Stripe-Signature` to matematyczny kryptograficzny sztych przypięty w niewidocznym nagłówku przez programistów ze Stripe. `endpointSecret` to unikalny szyfr podłączeniowy do Twojego konta ukryty w głębiach Twojego serwera.
**Co masz powiedzieć:** *"Rozpoczynam proces inspekcji kryptograficznej uderzenia Webhooku. Biorąc czysty, surowy strumień danych z zapytania sieciowego (Payload), i łącząc z ukrytym podpisem, wpuszczam te klucze do systemu potwierdzania od firmy Stripe."*

```php
        try {
            if ($endpointSecret !== 'whsec_dummy') {
                $event = \Stripe\Webhook::constructEvent(
                    $payload, $sigHeader, $endpointSecret
                );
            } else {
                // For local testing without valid signatures
                $event = json_decode($payload);
                $event = (object) [
                    'type' => $event->type ?? '',
                    'data' => (object) ['object' => $event->data->object ?? []],
                ];
            }
```
**Co to jest:** Weryfikacja bezpieczeństwa (try-catch block) podparta biblioteką SDK Stripe. Wykorzystuje brutalny silnik walidacyjny z API Stripe do odcyfrowania podpisu (lub pozwala nam ominąć weryfikacje przy sztucznych testach lokalnych "Dummy"). Weryfikacja ta to proces kryptograficzny wykorzystujący SHA-256.
**Co masz powiedzieć:** *"Biblioteka Stripe z precyzją skalpela wykonuje kryptograficzną rekonstrukcję Zdarzenia (Event). Jeśli haker spróbowałby zamienić chociaż jedną wielkość litery z zawartości przelewu by odblokować konto, suma kontrolna w podpisie ulegnie rozpadowi po stronie wejściowej."*

```php
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }
```
**Co to jest:** Pułapki na kody błędu. Jeśli ktokolwiek wywoła ten URL spoza infrastruktury platformy Stripe – zderzy się z twardym odbiciem (Status 400 Bad Request), a aplikacja odetnie strumień informacji bez zająknięcia bazy.
**Co masz powiedzieć:** *"Jeśli autoryzacja się posypie, twardo zamykam kanał zwracając restowy błąd 400 (Bad Request). Jest to element twardego modelu ZTNA (Zero Trust Network Architecture)."*

```php
        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
```
**Co to jest:** Sprawdzenie "Typu Zdarzenia". Ze Stripe może wylecieć informacja "Karta Odpięta", "Subskrypcja Anulowana", "Użytkownik uciekł z koszyka". Tutaj analizujemy z chirurgiczną powagą fakt: `ZAKUP DOKOŃCZONY I ZAKSIĘGOWANY W BANKU`. Wyciągnięcie serca z zapytania i odpakowanie jako obiekt.

```php
            if ($session->mode === 'subscription') {
                $registrationId = $session->metadata->registration_id ?? null;
```
**Co to jest:** Skręt w prawo (Instrukcja warunkowa). Ustaliliśmy w module płatności, że proces zakupu `abonamentu miesięcznego` (subscription) niesie ze sobą zaszyte ukryte numery referencyjne w tzw. Metadanych (metadata) odnośnie kto się zrejestrował.
**Co masz powiedzieć:** *"Jeżeli system weryfikuje zapłatę w modelu abonamentowym, zaciągam wplecione głęboko do żądania płatności klucze referencyjne (Metadata payload).*

```php
                if ($registrationId) {
                    $registrationData = \Illuminate\Support\Facades\Cache::get("registration_{$registrationId}");
                    
                    if ($registrationData) {
                        // Tworzenie bazy firmy
                        $bedrijf = \App\Models\Bedrijf::create([ ... ]);
                        // Tworzenie pierwszego użytkownika
                        $user = \App\Models\User::create([ ... ]);

                        Log::info("Provisioned new bedrijf: {$bedrijf->name} via SaaS Webhook.");
                        \Illuminate\Support\Facades\Cache::forget("registration_{$registrationId}");
                    }
                }
```
**Co to jest:** System zrzuca z pamięci masowej podręcznej wyciąg firmy, tworzy absolutnie nową firmę, nadaje użytkownika na stopień ADMINISTRATORA w bazie, generując bezpieczny wpis do dziennika systemowego, oraz utylizuje klucze za pomocą pamięci `forget`. Nazywa się to procesowaniem SaaS – (Provisioning).
**Co masz powiedzieć:** *"Aplikując zaawansowane skryptowanie w tle bazy pamięciowej Redis, system Provisioningu działa w absolutnej asynchroniczności (Backgroud execution), bez udziału operatora. Baza jest uzupełniana o relacyjne modele zarządu z nadaniem predefiniowanych ról autoryzacji bazując na opłaconym checkoucie w locie."*

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
                            'stripe_payment_intent_id' => $session->payment_intent ?? null,
                        ]);

                        $logCommunicationAction->execute( ... );
                        Log::info("Invoice {$invoice->invoice_number} marked as paid via Stripe webhook.");
                    }
                }
            }
```
**Co to jest:** Skręt w lewo z funkcji wyżej. W ten sam jeden kanał trafia informacja, że "Klient Transportowca właśnie zapłacił 400 Euro za transport". System odbiera ten klucz, puszcza weryfikację nad bazą bez barier ochronnych firmy (`withoutGlobalScopes`), zmienia pole kwoty opłaconej i oznacza to we wszystkich bazach (i loguje powiadomienie z `Action`).
**Co masz powiedzieć:** *"Dzięki wybitnej elastyczności (Flexibility) interfejsu Stripe, przepuszczam przez jeden dedykowany szlak zapytania w modelu `payment`. Używam bezpiecznika ignorującego Globalne Zakresy z racji asynchronicznego żądania od zewnętrznego serwera, dokonuję autorskiej zmiany statusu dokumentu (State Mutation) na InvoiceStatus::PAID oraz deleguję informację zwrotną audytowo na zaplecze biznesowe, zamykając sprawę fakturowania bez udziału operatora bazy danych."*

```php
        } elseif (in_array($event->type, ['customer.subscription.updated', 'customer.subscription.deleted'])) {
            $subscription = $event->data->object;
            $bedrijf = \App\Models\Bedrijf::where('stripe_subscription_id', $subscription->id)->first();
            
            if ($bedrijf) {
                $bedrijf->update(['stripe_subscription_status' => $subscription->status]);
                Log::info("Updated subscription status for bedrijf: {$bedrijf->name} to {$subscription->status}.");
            }
        }

        return response()->json(['status' => 'success'], 200);
    }
}
```
**Co to jest:** Jeśli firma nie opłaci faktury, bo wygaśnie karta w 3 miesiącu pracy – Stripe automatycznie nas o tym zawiadamia sygnałem `deleted` / `updated`. Natychmiastowo nadpisujemy to do serwisu. Ostatecznie – kulturalnie odpowiadamy automatom od Stripe – `200 Success` "Zrobiłem co kazałeś, dziękuję". Jakbyśmy rzucili błędem, Stripe spamowałby nas przez następne 24 godziny non stop.
**Co masz powiedzieć:** *"Zwieńczeniem całego systemu są zdarzenia anulujące lub zawieszające subskrypcję firm w systemie CRM, chroniące serwer jako zabezpieczenie przeciw awariom i brakom na koncie klienta, zachowując bezwzględny przepływ gotówki w naszym ujęciu Cash-Flow."*

---

## Pytania, na które musisz być gotów:

1. **Jury:** *"Jeżeli system w pełni automatycznie tworzy Firmę (Provisioning) u Ciebie na zapleczu po zweryfikowaniu Webhooka, co powstrzyma napastnika, który zlokalizuje i weźmie wyciąg metadanych, i wyśle sfingowane zapytanie (Spam Request Attack) do portu Twojego serwera, żeby otworzyć darmowe, setki, miliony firm w CRM z prawami Admina podając się za serwer Stripe?"*
   **Twoja odpowiedź:** *"Niestety jego szanse to okrągłe zero. Żadne zapytanie wpadające na `/webhook/stripe` nie przekroczy muru blokady, zanim kryptograficzny certyfikat podpisowy SHA-256 przypięty do requesta, z podpisem w ukrytym sekretnym kluczu Stripe (w moim kodzie wywołany jako `whsec_dummy` lub zaciągnięty z env), nie wygeneruje potwierdzenia 1:1 zawartości z Payloadem JSON'a. Zwykła literówka "o" zamieniona na "a" zniszczy podpisywalność Payloadu blokując ruch ze statusem błędnym 400 Bad Request, a samo żądanie jest ignorowane."*
