# 1. Izolacja Danych (Multi-Tenancy i Global Scopes)

**Gdzie znajduje się kod:**
- `app/Traits/HasBedrijf.php`
- `app/Models/Scopes/BedrijfScope.php`

**Dlaczego rozbiliśmy to na dwa pliki? (Zabłyśnij przed jury):**
Zrobiliśmy to z powodu zasady **SOLID (Single Responsibility)**. 
- `BedrijfScope` jest specjalistą od filtrowania zapytań SQL w bazie. 
- Trait `HasBedrijf` jest organizatorem – zarządza całym modelem i wie *kiedy* podpiąć ten filtr. 
Rozbicie tego na dwa pliki sprawia, że kod jest czysty, łatwy do testowania i każda część robi tylko jedną, konkretną rzecz.

---

## Plik 1: `app/Traits/HasBedrijf.php`

Ten plik to tzw. "Trait" (Cecha). Możesz ją dokleić do dowolnego modelu Eloquent (np. Faktury, Lead), aby automatycznie zyskał pełną obsługę Multi-Tenancy bez konieczności powielania kodu (zasada DRY - Don't Repeat Yourself).

### Omówienie linijka po linijce:

```php
namespace App\Traits;
use App\Models\Scopes\BedrijfScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
```
**Co masz powiedzieć jury:** *"Na początku importuję niezbędne fasady, w szczególności fasadę Auth z przestrzeni Illuminate, aby mieć bezpieczny, statyczny dostęp do aktualnej sesji HTTP użytkownika z dowolnego miejsca w Traicie."*

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `namespace` – "Przestrzeń nazw". To wirtualny adres tego pliku w projekcie, żeby inne pliki wiedziały, gdzie go szukać. Przypomina folder na komputerze.
> *   `use` – Komenda importu. Mówi plikowi: "Będziemy tu korzystać z narzędzia Auth, idź i ściągnij je do pamięci z systemu Laravel, zanim zaczniemy pracę".
> 
> **Na chłopski rozum:** Jeśli w kodzie poniżej użyjesz pełnej ścieżki (zaczynającej się od `\`, np. `\Illuminate\...\Auth`), to ten krótki `use` na górze zszarzeje, bo edytor zauważy, że z niego nie korzystasz w skróconej formie.

```php
trait HasBedrijf
{
```
**Co masz powiedzieć jury:** *"Definiuję architekturę Traitu, gotowego do wstrzyknięcia do modeli dziedzinowych."*

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `trait` – Specjalne słowo w PHP oznaczające blok kodu (cechę), który można "wkleić" do innych klas. To nie jest samodzielna funkcja ani kompletny obiekt, tylko wtyczka.
> *   `HasBedrijf` – Nazwa, którą wymyśliliśmy dla naszej wtyczki (po polsku: "Posiada Najemcę/Firmę").
> *   `{` – Otwarcie klamry. Oznacza początek zawartości tego Traita (wszystko wewnątrz klamer to jego "ciało").
>
> **Na chłopski rozum:** Wyobraź sobie Traita jako Wtyczkę USB. Zamiast pisać ten sam kod logiki bezpieczeństwa w 10 miejscach, napisaliśmy go raz. Wystarczy podpiąć tę wtyczkę (`use HasBedrijf;`) w modelu faktury i nagle model ten zyskuje nowe supermoce.

```php
    public static function bootHasBedrijf(): void
    {
```
**Co masz powiedzieć jury:** *"Wykorzystałem konwencję nazewniczą boot-metod w Traitach (bootHasBedrijf), aby wstrzyknąć logikę zabezpieczeń w tzw. cykl życia obiektu (Lifecycle Hook) dokładnie w momencie ładowania jądra modelu przez framework."*

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `public` – Oznacza, że ta funkcja jest publicznie dostępna i system może ją uruchomić z zewnątrz.
> *   `static` – Oznacza funkcję "statyczną", czyli taką, którą można odpalić na poziomie ogólnym, bez konieczności wskazywania palcem jednej, konkretnej faktury.
> *   `function` – Słowo kluczowe mówiące: "teraz definiuję nową akcję/metodę".
> *   `bootHasBedrijf()` – Nazwa tej funkcji. Słówko `boot` to u Laravela magiczny "zapalnik" – uruchomi to sam, zanim cokolwiek innego się wydarzy.
> *   `: void` – Ścisła deklaracja, która mówi: "Ta funkcja po prostu zrobi swoją robotę i nic nie zwróci z powrotem na zewnątrz" (void to z angielskiego "pustka").
>
> **Na chłopski rozum:** Słowo `static` jest tu jak "rozkaz dyrektora fabryki". Wydajemy polecenie ogólne dla całego modelu Faktur. Nie musimy pobierać konkretnej pojedynczej faktury z bazy, by uruchomić ten kod. Zabezpieczamy całą fabrykę z góry, zanim wyprodukuje pierwszą sztukę.

```php
        static::addGlobalScope(new BedrijfScope());
```
**Co masz powiedzieć jury:** *" Metoda addGlobalScope przypina naszą klasę BedrijfScope na stałe do rdzenia Query Buildera. Od tego momentu każde żądanie SQL z tego modelu zostanie przez nią przefiltrowane."*

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `static::` – Mówi PHP: "Odwołaj się do samego siebie, do ogólnego modelu". Jest to tzw. "Późne wiązanie statyczne" - dostosowuje się w locie do modelu, w którym wpięto Traita.
> *   `addGlobalScope()` – Wbudowana funkcja Laravela, która doczepia "niewidzialny filtr" do zapytań bazy danych.
> *   `new` – Komenda, która "powołuje do życia" (tworzy fizyczną instancję w pamięci RAM) nasz filtr (`BedrijfScope`).
>
> **Na chłopski rozum:** `addGlobalScope` to jest "taśma klejąca". Używamy jej TYLKO RAZ w momencie startu. Przykleja ona na stałe nasz właściwy plugin bezpieczeństwa (`BedrijfScope`) do modelu. Od tej pory każdy `Invoice::all()` automatycznie przejdzie przez ten filtr w tle.

```php
        static::creating(function (Model $model) {
```
**Co masz powiedzieć jury:** *"Uruchamiam (Event Listener), który w ułamku sekundy po wywołaniu zapisu do bazy przechwytuje akcję powoływania nowego rekordu, ZANIM zostanie wygenerowane zapytanie SQL."*

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `creating` – Magiczny zapalnik. Znaczy: "Wykonaj poniższy kod W TRAKCIE tworzenia nowego wpisu, ale zanim poleci on do bazy jako zapytanie SQL".
> *   `function (Model $model)` – Tworzymy tu tzw. "funkcję anonimową", do której wrzucamy to, co aktualnie ratujemy (czyli nasz obiekt faktury/leada, reprezentowany przez słówko `$model`).
>
> **Na chłopski rozum:** Działa to jak **Bramka na lotnisku**. Kiedy w programie próbujesz zapisać nową fakturę (`$invoice->save()`), system nie uderza od razu do bazy z czystym SQL. Przechodzi przez tę bramkę. My zatrzymujemy "walizkę" z danymi faktury, żeby ją po cichu zmodyfikować, zanim poleci w świat.

```php
            if (\Illuminate\Support\Facades\Auth::hasUser() && ! $model->bedrijf_id) {
```
**Co masz powiedzieć jury:** *"To instrukcja zabezpieczająca. Zanim automatycznie przypiszę firmę do nowej faktury, upewniam się, że w request'cie HTTP faktycznie znajduje się sesja zalogowanego użytkownika oraz upewniam się, że model nie otrzymał już wartości z innego systemu."*

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `if (...)` – Instrukcja warunkowa ("Jeśli...").
> *   `Auth::hasUser()` – Funkcja frameworka pytająca: "Czy ktokolwiek jest teraz fizycznie zalogowany na stronie?".
> *   `&&` – Oznacza "ORAZ". Służy do łączenia dwóch warunków, które muszą być spełnione jednocześnie.
> *   `!` – Wykrzyknik to negacja. Zmienia warunek na przeciwieństwo. `! $model->bedrijf_id` znaczy: "Nieprawda, że model ma już podane ID firmy" (czyli jest ono puste).
> *   `->` – tzw. "strzałka dostępu". Oznacza "wejdź do środka tego obiektu i weź jego element" (np. `$model->bedrijf_id` to wejście do modelu faktury).

```php
                $model->bedrijf_id = \Illuminate\Support\Facades\Auth::user()->bedrijf_id;
            }
        });
    }
```
**Co masz powiedzieć jury:** *"Dzięki temu osiągamy pełną automatyzację przypisywania danych. Model sam w locie wie, jaka firma go generuje."*

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `=` – Przypisanie wartości (nie mylić z porównaniem `==`). Mówisz tu: "Weź ID firmy dla tej nowej faktury i ustaw je równo z ID firmy pracownika, który jest obecnie zalogowany".
>
> **Na chłopski rozum:** Na bramce lotniskowej upewniamy się, czy ktokolwiek nadaje walizkę (`hasUser()`) i czy walizka nie ma już zawieszki z ID firmy (`! $model->bedrijf_id`). Jeśli nie ma, to sami w locie pakujemy do niej ID firmy zalogowanego pracownika. Dzięki temu programista nie musi pamiętać o podawaniu firmy w każdym formularzu na stronie.

```php
    public function bedrijf()
    {
        return $this->belongsTo(\App\Models\Bedrijf::class);
    }
}
```
**Co masz powiedzieć jury:** *"Na końcu definiuję relacyjną odpowiedniość. Definiując `belongsTo` udostępniam każdemu dziedziczącemu modelowi błyskawiczną ścieżkę do pobrania danych o firmie powiązanej."*

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `return` – Komenda oznaczająca: "Oddaj to na zewnątrz i zakończ działanie funkcji". Zwracamy tu tzw. Obiekt Relacji (instrukcję dla bazy danych), a nie jeszcze same fizyczne dane firmy.
> *   `$this->` – Oznacza "ja sam" / "ten konkretny dokument". To odniesienie obiektu do samego siebie (np. ta jedna, specyficzna faktura).
> *   `belongsTo()` – Słowo z jądra Laravela definiujące relację w bazie danych: "Przynależy do...". Buduje "kabel" łączący np. Fakturę z Firmą.
> *   `::class` – Magiczna stała z PHP. Zamienia klasę na jej czystą nazwę tekstową (czyli wypluje "App\Models\Bedrijf"). Programiści używają tego zamiast wpisywania tekstu z palca, bo jak zrobisz literówkę w `::class`, to edytor od razu podświetli Ci błąd.

> **Na chłopski rozum:** Zwracanie (`return`) relacji `belongsTo` to nie jest przyniesienie gotowych danych z bazy. To jest oddanie szefowi **"Mapy Skarbów"**. Mówisz systemowi: *"Zwracam Ci instrukcję, która mówi, że ta faktura przynależy do (`belongsTo`) folderu z etykietą Firmy (`Bedrijf::class`). Jak będziesz kiedyś potrzebował danych tej firmy, to użyj tej mapy i sobie je pobierz"*.

---

## Plik 2: `app/Models/Scopes/BedrijfScope.php`

To jest sam "silnik" naszego filtru (Plugin), którego doczepiliśmy we wtyczce wyżej.

### Omówienie linijka po linijce:

```php
namespace App\Models\Scopes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
```
*(Import klas - omówione powyżej).*

```php
class BedrijfScope implements Scope
{
```
**Co masz powiedzieć jury:** *"Użyłem silnie typowanego interfejsu `Scope` dostarczanego z jądra Eloquenta. To gwarantuje zachowanie czystości architektury SOLID."*

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `class` – Definicja całego fizycznego obiektu (Klasy). To pojemnik na funkcje i dane.
> *   `implements Scope` – "Podpisanie prawnego kontraktu". Zmuszasz swoją klasę `BedrijfScope`, by zastosowała się do zasad z pustego wzorca (interfejsu `Scope`), wbudowanego w system Laravel.
> *   **Co to jest interfejs (Scope)?** To zbiór reguł, w którym *nie ma żadnego działającego kodu*. On fizycznie niczego nie oblicza ani nie filtruje. Posiada on jednak prawny nakaz: "Każdy, kto mnie zaimplementuje, MUSI posiadać u siebie funkcję o nazwie `apply`".

> **Na chłopski rozum:** Interfejs `Scope` to jak pusta w środku legitymacja ochroniarza (Certyfikat). Zanim Laravel (Szef) wyśle Twojego ochroniarza (czyli kod z `BedrijfScope`) na drzwi klubu, sprawdza czy ma on przypiętą naklejkę `implements Scope`. Szef nie patrzy co Twój ochroniarz potrafi, interesuje go tylko ten Certyfikat. Bo Certyfikat prawnie gwarantuje, że ten ochroniarz przeszedł szkolenie i na 100% ma w sobie funkcję `apply()` (czyli umiejętność sprawdzania biletów). Gdybyś usunął słowo `implements Scope`, system natychmiast wręczyłby Ci zwolnienie dyscyplinarne (Crash aplikacji), bo bałby się, że postawiłeś na drzwiach człowieka z ulicy, który nie wie co robić.

```php
    public function apply(Builder $builder, Model $model): void
    {
```
**Co masz powiedzieć jury:** *"Metoda apply to wymuszony przez interfejs punkt wejścia filtru. Jako argumenty przyjmuje jądro kompilatora SQL (Builder) oraz instancję Modelu. Funkcja mutuje obiekt Buildera w pamięci, dlatego zwraca typ void (pustkę)."*

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `apply(...)` – Z ang. "Zastosuj". To właśnie ta funkcja, którą narzucił nam Certyfikat `Scope` wyżej. Jest wywoływana automatycznie przez system przy każdym zapytaniu do bazy.
> *   `Builder $builder` – Konstruktor SQL. Obiekt, który tłumaczy nasz kod PHP na czysty język bazy danych (np. `SELECT * FROM...`).
> *   `Model $model` – Pusty model (np. Faktura), na którym aktualnie działamy. Podany po to, żebyśmy w środku wiedzieli, o jakiej tabeli mowa.
> *   `: void` – Typ zwracany. Oznacza "Pustkę". Funkcja niczego nie oddaje (nie ma tu słowa `return`). Ona jedynie po cichu modyfikuje podany jej obiekt `$builder`.
>
> **Na chłopski rozum:** Zobacz na to jak na "Księgę Zasad" przy wejściu do biurowca. 
> `apply` to moment, w którym ochroniarz otwiera księgę. 
> Ochroniarz dostaje do ręki notes ze spisanymi przez kogoś zasadami (`Builder $builder`) i identyfikator gościa (`Model $model`). 
> Ochroniarz dopisuje w notesie długopisem nową twardą zasadę: *"Wpuszczać tylko z firmy nr 15!"* (to dzieje się linijkę niżej w kodzie). Ponieważ ochroniarz tylko nabazgrał Ci w Twoim notesie i oddał Ci ten sam notes, a nie dał nic nowego, funkcja używa słowa `: void` (czyli "nie dam ci żadnej nowej rzeczy, po prostu zedytowałem to, co miałeś").

```php
        if (Auth::hasUser()) {
            $builder->where($model->getTable() . '.bedrijf_id', Auth::user()->bedrijf_id);
        }
    }
}
```
**Co masz powiedzieć jury:** *"To jest serce zabezpieczeń SaaS. Aplikując warunek `where` na poziomie silnika bazy danych, dynamicznie ucinam zasięg zapytań wyłącznie do ID firmy zalogowanego pracownika."*

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `$builder->where(...)` – Nakazujesz kreatorowi zapytań (Builderowi): "Dopisz klauzulę WHERE (gdzie) do generowanego kodu SQL".
> *   `$model->getTable()` – Zabezpieczenie. Funkcja wyciąga dokładną nazwę tabeli w bazie danych (np. `invoices`), żeby złączyć ją w całość: `invoices.bedrijf_id`. Zapobiega to gubieniu się bazy przy złączeniach JOIN.
> *   `.` (kropka) – W PHP służy do "sklejania" tekstów. Skleja nazwę tabeli z tekstem `'.bedrijf_id'`.
>
> **Na chłopski rozum:** To tutaj znajduje się precyzyjna instrukcja, jak zmodyfikować SQL. Jeśli loguje się pracownik firmy nr 5, każde `SELECT * FROM invoices` zostanie tu brutalnie ucięte i zmienione na `SELECT * FROM invoices WHERE invoices.bedrijf_id = 5`.

---

## Pytania, na które musisz być gotów:

1. **Jury:** *"Co by się stało, gdyby programista zapomniał dodać klauzulę `where` wyświetlając listę faktur z bazy?"*
   **Twoja odpowiedź:** *"Absolutnie nic. Dzięki `BedrijfScope`, warunek bezpieczeństwa jest doczepiany na poziomie jądra ORM. Nawet proste polecenie `Invoice::all()` zostanie w tle zmodyfikowane i wysłane do serwera SQLite z już wpiętą barierą bezpieczeństwa ograniczającą rekordy do jednego klienta."*

2. **Jury:** *"Jak w takim razie jako Super Admin przeglądasz statystyki wszystkich firm w systemie?"*
   **Twoja odpowiedź:** *"Framework zapewnia wbudowaną furtkę (Escape Hatch). W kodzie zarezerwowanym wyłącznie dla funkcji administracyjnych, używam połączonej metody ignorującej filtry: `Invoice::withoutGlobalScopes()`. Zdejmuje ona tę barierę bezpieczeństwa."*
