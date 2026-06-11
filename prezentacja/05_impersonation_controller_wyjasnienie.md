# 5. Ochrona Pamięci i Hakowanie Sesji (Mechanizm Impersonacji)

**Gdzie znajduje się kod:**
- `app/Http/Controllers/ImpersonationController.php`

**Co musisz wiedzieć z lotu ptaka:**
Jako Super Admin platformy, logujesz się od czasu do czasu do konta swoich klientów (tzw. "Wcielanie się" / Impersonacja), żeby zweryfikować awarię czy błąd klienta od środka. Kiedy chcesz stamtąd bezpiecznie opuścić panel (powrócić na swoje konto administracyjne), framework rzuca pod nogi wszystkie możliwe "kłody technologiczne". Wchodząc głęboko do wnętrza surowych mechanizmów autoryzacyjnych sesji pokonujesz mechanizm multi-dostępu `BedrijfScope` i mordercze blokady kradzieży danych z Laravel 11. To ten z plików "Pokaż jak rozwiązujesz naprawdę skomplikowane zagadki bezpieczeństwa i logiki pamięci operacyjnej".

---

## Plik: `app/Http/Controllers/ImpersonationController.php`

### Omówienie linijka po linijce:

```php
namespace App\Http\Controllers;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\Request;

class ImpersonationController extends Controller
{
```
**Co to jest:** Pusty schemat organizacyjny wewnątrz Laravela podpinający się pod przestrzeń kontrolerów oraz wprowadzający narzędzia operacyjne takie jak zapytania z Front-endu (Request). Fasada Filament jest wprost ściągnięta z jądra platformy TALL stack.
**Co masz powiedzieć:** *"Korzystam tu ze statycznego Proxy środowiska operacyjnego Laravela - popularnych Fasad (Facades). Dają mi one nieskrępowany autoryzowany dostęp odgórny po szynie komunikacyjnej na żądanie."*

```php
    public function leave(Request $request)
    {
        if (session()->has('impersonated_by')) {
            $superAdminId = session('impersonated_by');
```
**Co to jest:** Rozpoczęcie funkcji. System przeszukuje wewnętrzne surowe dane z tablicy przechowującej dane użytkownika (session) szukając kryptograficznego klucza `impersonated_by`. Przypisuje ten element na ułamek sekundy do bezpiecznej zmiennej, przygotowując powrót.
**Co masz powiedzieć:** *"Inicjując wyjście z trybu awaryjnego (Impersonacji), skanuję w locie bazę podręcznej sesji w poszukiwaniu tzw. Kotwicy Identyfikacyjnej (Anchor ID), za pomocą której mam udowodnić kto de facto się ukrywał pod powłoką konta."*

```php
            // Bypass BedrijfScope to find the Super Admin in the DB
            $superAdmin = User::withoutGlobalScopes()->find($superAdminId);
```
**Co to jest:** Super Admin ma zablokowany dostęp do odczytu danych w cudzej firmie. Żeby powrócić na swoje konto i dociągnąć o sobie wszystkie zasady uprawnień bez rzucenia blokady przez framework bezpieczeństwa – wstrzykujesz w Buildera bazodanowego funkcję `withoutGlobalScopes()`.
**Co masz powiedzieć:** *"Ponieważ jądro modelu User podlega całkowitej kontroli filtra zapór multi-dostępowych `BedrijfScope` - bez możliwości obejścia z zewnątrz, serwer zaciąłby się nie znajdując moich bazowych uprawnień administracyjnych w bazie zamkniętej z `AND bedrijf_id = 'cudzefirmyid'`. Używam techniki Global Scopes Bypass ignorując autoryzację do wyciągnięcia bezwzględnie profilu administratora po ID ULIDzie."*

```php
            if ($superAdmin) {
                // Manually determine the current guard and its name
                $guardName = Filament::getCurrentOrDefaultPanel()?->getAuthGuard() ?? 'web';
                $sessionGuard = auth()->guard($guardName);
```
**Co to jest:** W tak potężnym rozwiązaniu, logowanie nie dzieje się z jednej ścieżki (tzw. Guards w Laravelu). Kod z niezwykłą dozą bezpieczeństwa automatycznie dopytuje się frameworka (poprzez Fasadę `Filament`), na jakim systemie ochrony jesteśmy zapięci (czy standardowym 'web' czy np. na oddzielnym do API). Wyciągamy ochroniarza na przód, uzyskując pełen dostęp do strażnicy.
**Co masz powiedzieć:** *"Bezpieczna detekcja strażnicy chronionej (Authenticaton Guards Detection). Zapytuję serwer o konfigurację kontekstu używając natywnych operatorów PHP 8."*

```php
                // Clear the client's auth data from the session
                session()->forget($sessionGuard->getName());
                session()->forget($sessionGuard->getRecallerName());
```
**Co to jest:** Ręczne wyszorowanie ciastek ze starym klientem z operacyjnej pamięci komputera. `getRecallerName` to nazwa niewidocznego ciastka "Zapamiętaj mnie" z logowania w systemie. System brutalnie wyrzuca go z przeglądarki.
**Co masz powiedzieć:** *"Za pomocą twardych modyfikacji surowych tablic ramowych – wymuszam fizyczne wypięcie kluczy sesyjnych (Wiping the Authenticator Payload) związanych z fałszywą skorupą."*

```php
                // Clear the package's impersonation flags
                session()->forget([
                    'impersonated_by',
                    'impersonator_guard',
                    'impersonator_guard_using',
                    'remember_web',
                ]);
```
**Co to jest:** Sprzątanie. Usuwanie z serwera po stronie aplikacji wszystkich flag pamięciowych od dostawcy paczek (Vendora). Serwer wyzerowany przed logowaniem "powrotowym".
**Co masz powiedzieć:** *"Usuwam również tzw. Payload Contextualny od twórców zewnętrznych paczek, czyszcząc ślady pod cykl GC (Garbage Collection), przygotowując pamięć procesora na twardy powrót instancji klas."*

```php
                // Manually inject the Super Admin's ID into the session 
                // This correctly avoids triggering a password_hash check failure in AuthenticateSession!
                session()->put($sessionGuard->getName(), $superAdmin->getAuthIdentifier());
```
**Co to jest:** Klucz i wybitnie chytre zagranie w inżynierii bezpieczeństwa. Zamiast logować użytkownika metodą wyższego rzędu `login()`, piszesz RĘCZNIE unikalny z powrotem wpis do bazy wpisując na wprost z powrotem klucz admina do pliku bez sprawdzania przez frameworka hasła, maila, pinu itp.
**Co masz powiedzieć:** *"Napisanie tego w tradycyjny sposób (`auth()->login($admin)`) stanowi wektor zagrożenia. Kiedy Laravel wczytuje taką komendę na otwartej i przypisanej pamięci do innej osoby w przeglądarce, zderza się to z blokadą sprzętową od uciskania wektorów ataków Session Hijacking (Kradzież ciastka sesji), niszcząc sesję z automatu po błędem kryptograficznym o braku spójności starego hasła z nowym użytkownikiem. Moja implementacja tzw. "Surowego Hakowania Danych" bezszelestnie, proceduralnie pomija proces logowania podmieniając klucz za plecami procesora walidacji uwierzytelnień."*

```php
                // Also clear the password_hash from the session so the middleware 
                // re-caches the Super Admin's hash instead of complaining about a mismatch.
                session()->forget('password_hash_' . $guardName);

                // Force the auth system to reload the user object from the session on the next request
                auth()->forgetGuards();
```
**Co to jest:** Genialne zwieńczenie haku z akapitu wyżej. Aby nie rozjuszyć programu sprawdzającego kradzieże danych (czyli `AuthenticateSession Middleware`), który by szukał w ciastkach starego hasha w starej sesji klienta – wyrywamy i niszczymy komendą `forget` odcisk `password_hash` z serwera całkowicie w ułamku sekundy dla tego klienta.
Następnie komenda `forgetGuards()` dosłownie usuwa załadowaną z pamięci wizualizację starego klienta, zanim przeglądarka zdąży załadować grafikę, upewniając się, że następne zapytanie o model `$user` wymusi czytanie ze świeżo uzupełnionych nowym kluczem danych.
**Co masz powiedzieć:** *"Żeby utrzymać zaporę bezpieczeństwa Session Hijacking (`AuthenticateSession`) w stabilnym trybie zaufania w Laravel 11, stosuję prewencyjną amnezję i wyczyszczenie ukrytego parametru skrótowego `password_hash_web` z bazy operacyjnej. Brak obcego Hasła zmusza wewnętrzny middleware frameworku na wywołanie świeżego zapytania z bazy dla wstrzykniętego sekundę temu administratora celem wygenerowania sobie nowego odcisku obronnego, ignorując jakąkolwiek próbę wyłączenia instancji. Dodatkowy `forgetGuards()` upewnia się, że na koniec obróbki serwer nie wyciągnie po starych cache'owych kluczach pamięci przestarzałych struktur."*

```php
            } else {
                \Illuminate\Support\Facades\Log::error("Impersonation Exit Failed: Super Admin not found for ID: {$superAdminId}");
            }

            return redirect(session()->pull('impersonate.back_to', '/dashboard'));
        }

        return redirect('/dashboard');
    }
}
```
**Co to jest:** Na sam koniec skrypt wędruje przez zapasowe śluzy "else". Jeśli admin uciął kabel z prądem w chwili wylogowania lub usunął z bazy samego siebie sekundę wcześniej – wyrzucamy twardy błąd do awaryjnego logu w pamięci, a ostatecznie przeglądarka po sprzątnięciu z serwera przerzuca "Zadowolonego Admina" spowrotem na swój `/dashboard` bezpiecznym przejściem Redirecta z usunięciem wbudowanego tagu za pomocą opcji "wyciągnij i wyrzuć" zwaną z angielskiego funkcją `pull()`.
**Co masz powiedzieć:** *"Jeśli odnajdywanie powrotnego operatora na jakimś abstrakcyjnym etapie zawiedzie ze względu na usunięty z bazy rekord od strony zaplecza w czasie pracy, wpis wyciągnie błąd na stałe do dzienników zrzutu bazy w postaci rygorystycznego w logach polecenia Log::Error – co nie blokuje wylogowania samej powłoki na front-endzie. Następuje ostateczne wyrzucenie flagi sesji przez `pull()`, co finalizuje proces Redirecta w czystej klasie z wyzbyciem się pozostałości w pamięci RAM."*

---

## Pytania, na które musisz być gotów:

1. **Jury:** *"To co tu demonstrujesz wygląda wręcz ryzykownie i niekonwencjonalnie. Skoro umiesz ominąć sprawdzenie pętli hasła i ręcznie logujesz Super Admina podmieniając sesję, czy nie wyeksponowałeś luk dla ataków Cross-Site Request Forgery (CSRF) próbujących zalogować innego szkodliwego użytkownika bez autoryzacji w ten sposób z przeglądarki wprost?"*
   **Twoja odpowiedź:** *"Nie, absolutnie nie. Cała procedura zamyka się jako operacja od wewnątrz wywołana z uprzednio nałożonego zabezpieczenia (wyjęta na obopólną zgodę przy rozpoczęciu bycia Wcielonym). Ponieważ jest ona osadzona na bezpiecznym protokole sprawdzającym nałożone, silne `Middlewares` Laravel'a 11 oraz filtruje w autoryzowanym żądaniu (chronionym tokenem `@csrf` z front-endu wejściowego), nikt z zewnątrz za pomocą fałszywych linków na zapytaniach np. GET i POST nie jest w stanie uruchomić sekwencji, gdyż odcina go warstwa sieciowej straży pożarnej wchodzącej tuż przed uderzeniem na kontrolery."*
