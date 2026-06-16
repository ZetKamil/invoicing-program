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
**Co masz powiedzieć jury:** *"Korzystam tu ze statycznego Proxy środowiska operacyjnego Laravela - popularnych Fasad (Facades). Dają mi one nieskrępowany autoryzowany dostęp odgórny po szynie komunikacyjnej na żądanie."*

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `Controller` – Klasa bazowa kontrolera, odbierająca żądania HTTP.
> *   `Facades` – "Fasady". Są to statyczne nakładki na potężne systemy wewnątrz frameworka (np. Fasada `Filament` pozwala szybko zarządzać całym interfejsem admina).
>
> **Na chłopski rozum:** Fasada to taki "Pilot do telewizora". Zamiast rozkręcać telewizor, szukać kabli i lutować styki żeby zmienić kanał (skomplikowany kod wewnątrz systemu), masz ładny pilot z guzikiem (Fasada). Naciskasz `Filament::` i dzieje się magia, a telewizor załatwia resztę za Ciebie.

```php
    public function leave(Request $request)
    {
        if (session()->has('impersonated_by')) {
            $superAdminId = session('impersonated_by');
```
**Co masz powiedzieć jury:** *"Inicjując wyjście z trybu awaryjnego (Impersonacji), skanuję w locie bazę podręcznej sesji w poszukiwaniu tzw. Kotwicy Identyfikacyjnej (Anchor ID), za pomocą której mam udowodnić kto de facto się ukrywał pod powłoką konta."*

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `session()->has(...)` – Funkcja sprawdzająca, czy w aktualnej przeglądarce użytkownika istnieje w ciasteczkach (pamięci sesyjnej) konkretny klucz o nazwie `impersonated_by`.
> *   `session('impersonated_by')` – Wyciągnięcie wartości tego klucza (czyli ukrytego ID prawdziwego Administratora).
>
> **Na chłopski rozum:** System przeszukuje kieszenie gościa. Pyta: "Czy masz przy sobie specjalny numerek z szatni (`has`)?". Jeśli tak, bierze ten numerek do ręki (`$superAdminId = ...`), żeby udowodnić, że jesteś właścicielem kurtki administratora, a nie zwykłym klientem.

```php
            // Bypass BedrijfScope to find the Super Admin in the DB
            $superAdmin = User::withoutGlobalScopes()->find($superAdminId);
```
**Co masz powiedzieć jury:** *"Ponieważ jądro modelu User podlega całkowitej kontroli filtra zapór multi-dostępowych `BedrijfScope` - bez możliwości obejścia z zewnątrz, serwer zaciąłby się nie znajdując moich bazowych uprawnień administracyjnych w bazie zamkniętej. Używam techniki Global Scopes Bypass ignorując autoryzację do wyciągnięcia bezwzględnie profilu administratora po ID ULIDzie."*

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `withoutGlobalScopes()` – Tymczasowe wyłączenie nałożonych wcześniej filtrów `Scope` (jak nasz plugin `BedrijfScope`) na czas jednego zapytania.
>
> **Na chłopski rozum:** To jest "Klucz Francuski" (Master Override). Jesteś Super Adminem, ale wszedłeś w skórę klienta, więc system traktuje Cię jak klienta i zamknął przed Tobą drzwi do innych firm. Żeby wyciągnąć z bazy Twój PRAWDZIWY profil administratora, musisz użyć tego łomu (`withoutGlobalScopes`), by ignorować strażników i przeszukać wszystkie pokoje w całej bazie naraz.

```php
            if ($superAdmin) {
                // Manually determine the current guard and its name
                $guardName = Filament::getCurrentOrDefaultPanel()?->getAuthGuard() ?? 'web';
                $sessionGuard = auth()->guard($guardName);
```
**Co masz powiedzieć jury:** *"Bezpieczna detekcja strażnicy chronionej (Authenticaton Guards Detection). Zapytuję serwer o konfigurację kontekstu używając natywnych operatorów PHP 8."*

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `getAuthGuard()` – Zwraca nazwę aktywnego systemu logowania (tzw. Strażnika / Guard).
> *   `?->` i `??` – Operatory PHP 8 zabezpieczające przed błędami Null. "Jeśli Panel istnieje, daj mi Guarda, a jak Guarda nie ma, użyj awaryjnie słowa 'web'".
>
> **Na chłopski rozum:** Duża aplikacja może mieć różne metody wejścia na imprezę. Dopytujesz organizatora (Filament): "Powiedz mi, jaka agencja ochrony dzisiaj tutaj weryfikuje wejściówki?". Odpowiedź przypisujesz do zmiennej.

```php
                // Clear the client's auth data from the session
                session()->forget($sessionGuard->getName());
                session()->forget($sessionGuard->getRecallerName());
```
**Co masz powiedzieć jury:** *"Za pomocą twardych modyfikacji surowych tablic ramowych – wymuszam fizyczne wypięcie kluczy sesyjnych (Wiping the Authenticator Payload) związanych z fałszywą skorupą."*

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `forget()` – Zmusza system do natychmiastowego zniszczenia wybranych danych z pamięci ciasteczkowej.
> *   `getRecallerName()` – To ukryta nazwa ciasteczka generowanego gdy zaznaczysz pole "Zapamiętaj mnie" przy logowaniu.
>
> **Na chłopski rozum:** Szorujesz odciski palców. Zdejmujesz przebranie klienta, które przed chwilą nosiłeś. Każesz systemowi bezlitośnie zapomnieć, że w ogóle nosiłeś to przebranie, a przy okazji wyrywasz z komputera wszelkie ciasteczka "Zapamiętaj mnie" powiązane z tym klientem.

```php
                // Clear the package's impersonation flags
                session()->forget([
                    'impersonated_by',
                    'impersonator_guard',
                    'impersonator_guard_using',
                    'remember_web',
                ]);
```
**Co masz powiedzieć jury:** *"Usuwam również tzw. Payload Contextualny od twórców zewnętrznych paczek, czyszcząc ślady pod cykl GC (Garbage Collection), przygotowując pamięć procesora na twardy powrót instancji klas."*

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   Usuwamy całą tablicę flag, które zewnętrzna wtyczka zostawiła w naszym systemie podczas Wcielania się.
>
> **Na chłopski rozum:** Wycierasz kurze w mieszkaniu przed oddaniem kluczy. Wywalasz wszystkie żółte karteczki (flagi), które informowały komputer: "Uwaga, ten facet to tak naprawdę przebieraniec". Chcesz, żeby komputer zaczął z totalnie czystą kartą.

```php
                // Manually inject the Super Admin's ID into the session 
                // This correctly avoids triggering a password_hash check failure in AuthenticateSession!
                session()->put($sessionGuard->getName(), $superAdmin->getAuthIdentifier());
```
**Co masz powiedzieć jury:** *"Napisanie tego w tradycyjny sposób stanowi wektor zagrożenia. Kiedy Laravel zderza tradycyjne logowanie z nałożoną sesją innej osoby, niszczy sesję błędem kryptograficznym o braku spójności starego hasła z nowym użytkownikiem. Moja implementacja tzw. "Surowego Hakowania Danych" bezszelestnie, proceduralnie pomija proces logowania podmieniając klucz za plecami procesora walidacji uwierzytelnień."*

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `session()->put(...)` – Forsuje wpisanie konkretnych wartości BEZPOŚREDNIO do rdzenia pliku sesji (omijając weryfikacje haseł).
> *   `getAuthIdentifier()` – To po prostu ID użytkownika (np. numer `1`).
>
> **Na chłopski rozum:** To jest Twój ostateczny napad hakerski. Zamiast wejść oficjalnie przez frontowe drzwi (formularz logowania i hasło), co wywołałoby alarm ochroniarzy, Ty włamujesz się w nocy prosto do budki strażniczej i dopisujesz na tablicy sucho flamastrem: "Zalogowany jest teraz szef". Ochroniarze rano patrzą na tablicę i myślą, że wszystko jest ok.

```php
                // Also clear the password_hash from the session so the middleware 
                // re-caches the Super Admin's hash instead of complaining about a mismatch.
                session()->forget('password_hash_' . $guardName);

                // Force the auth system to reload the user object from the session on the next request
                auth()->forgetGuards();
```
**Co masz powiedzieć jury:** *"Żeby utrzymać zaporę bezpieczeństwa Session Hijacking (`AuthenticateSession`) w stabilnym trybie zaufania w Laravel 11, stosuję prewencyjną amnezję i wyczyszczenie ukrytego parametru skrótowego `password_hash` z bazy operacyjnej. Dodatkowy `forgetGuards()` upewnia się, że na koniec obróbki serwer nie wyciągnie z cache'u przestarzałych struktur."*

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `forget('password_hash...')` – Usuwamy stary odcisk palca hasła klienta.
> *   `auth()->forgetGuards()` – Resetuje instancję systemu autoryzacji w aktualnie trwającym przeładowaniu strony.
>
> **Na chłopski rozum:** System w Laravel 11 jest tak mądry, że pamiętałby w głowie, jakie hasło miał KLIENT z poprzedniej sekundy. Gdyby zobaczył na tablicy dopisane "zalogowany szef" (z kodu wyżej), a w pamięci miałby "hasło klienta", włączyłby alarm, krzycząc: "Złodziej kradnie ciasteczko!". Komendami wyżej aplikujesz systemowi pigułkę na Amnestję. Zmuszasz go, żeby wyrzucił z głowy stare odciski palców i wyzerował kamery.

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
**Co masz powiedzieć jury:** *"Jeśli odnajdywanie powrotnego operatora zawiedzie, wpis wyciągnie błąd na stałe do dzienników w postaci rygorystycznego polecenia Log::Error – co nie blokuje wylogowania samej powłoki na front-endzie. Następuje ostateczne wyrzucenie flagi sesji przez `pull()`, co finalizuje proces Redirecta w czystej klasie z wyzbyciem się pozostałości w pamięci RAM."*

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `Log::error(...)` – Niewidzialny dla użytkownika zapis do pliku dziennika w awaryjnych sytuacjach na zapleczu.
> *   `redirect(...)` – Wyślij przeglądarkę do konkretnego adresu URL.
> *   `pull(...)` – Wyciąga dane z pamięci po raz OSTATNI, od razu je kasując za sobą (metoda Destructive Read).
>
> **Na chłopski rozum:** Spadasz ze sceny jak bohater kina akcji. Komenda `redirect` to wrzucenie się na spadochronie z powrotem do bezpiecznego Głównego Kokpitu (Dashboardu) Super Admina. Z kolei `pull` to zniszczenie za sobą odznaki i biletu windziarza tuż po użyciu – raz pociągasz za rączkę spadochronu i od razu wyrzucasz ją do rzeki, żeby nikt inny nie mógł jej użyć. 

---

## Pytania, na które musisz być gotów:

1. **Jury:** *"To co tu demonstrujesz wygląda wręcz ryzykownie i niekonwencjonalnie. Skoro umiesz ominąć sprawdzenie pętli hasła i ręcznie logujesz Super Admina podmieniając sesję, czy nie wyeksponowałeś luk dla ataków Cross-Site Request Forgery (CSRF) próbujących zalogować innego szkodliwego użytkownika bez autoryzacji w ten sposób z przeglądarki wprost?"*
   **Twoja odpowiedź:** *"Nie, absolutnie nie. Cała procedura zamyka się jako operacja od wewnątrz wywołana z uprzednio nałożonego zabezpieczenia (wyjęta na obopólną zgodę przy rozpoczęciu bycia Wcielonym). Ponieważ jest ona osadzona na bezpiecznym protokole sprawdzającym nałożone, silne `Middlewares` Laravel'a 11 oraz filtruje w autoryzowanym żądaniu (chronionym tokenem `@csrf` z front-endu wejściowego), nikt z zewnątrz za pomocą fałszywych linków na zapytaniach np. GET i POST nie jest w stanie uruchomić sekwencji, gdyż odcina go warstwa sieciowej straży pożarnej wchodzącej tuż przed uderzeniem na kontrolery."*
