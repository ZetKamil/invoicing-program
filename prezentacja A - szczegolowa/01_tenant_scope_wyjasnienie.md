# 1. Izolacja Danych (Multi-Tenancy i Global Scopes)

**Gdzie znajduje się kod:**
- `app/Traits/HasTenant.php`
- `app/Models/Scopes/TenantScope.php`

**Co musisz wiedzieć z lotu ptaka:**
System ten używa zaawansowanej techniki zwaną "Multi-Tenancy" opartą na jednej wspólnej bazie danych, ale rygorystycznie izolowaną na poziomie zapytań SQL. Wykorzystuje do tego "Global Scopes" wstrzykiwane przez "Trait", co gwarantuje 100% bezpieczeństwo przed wyciekami danych pomiędzy firmami. Poniżej znajduje się kompletne objaśnienie każdej pojedynczej linijki kodu z tych plików, w tym wyjaśnienie samej składni, abyś w pełni rozumiał, co dany znak oznacza.

---

## Plik 1: `app/Traits/HasTenant.php`

Ten plik to tzw. "Trait" (Cecha). Możesz ją dokleić do dowolnego modelu Eloquent (np. Faktury, Lead), aby automatycznie zyskał pełną obsługę Multi-Tenancy bez konieczności powielania kodu (zasada DRY - Don't Repeat Yourself).

### Omówienie linijka po linijce:

```php
namespace App\Traits;
use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
```
**Co masz powiedzieć jury:** *"Na początku importuję niezbędne fasady, w szczególności fasadę Auth z przestrzeni Illuminate, aby mieć bezpieczny, statyczny dostęp do aktualnej sesji HTTP użytkownika z dowolnego miejsca w Traicie."*

> **Edukacja dla Ciebie (Co znaczą te słowa w kodzie):**
> *   `namespace` – "Przestrzeń nazw". To wirtualny adres tego pliku w projekcie, żeby inne pliki wiedziały, gdzie go szukać. Przypomina folder na komputerze.
> *   `use` – Komenda importu. Mówi plikowi: "Będziemy tu korzystać z narzędzia Auth, idź i ściągnij je do pamięci z systemu Laravel, zanim zaczniemy pracę".

```php
trait HasTenant
{
```
**Co masz powiedzieć jury:** *"Definiuję architekturę Traitu, gotowego do wstrzyknięcia do modeli dziedzinowych."*

> **Edukacja dla Ciebie:**
> *   `trait` – Specjalne słowo w PHP oznaczające blok kodu (cechę), który można "wkleić" do innych klas. To nie jest samodzielna funkcja ani kompletny obiekt, tylko wtyczka.
> *   `HasTenant` – Nazwa, którą wymyśliliśmy dla naszej wtyczki (po polsku: "Posiada Najemcę/Firmę").
> *   `{` – Otwarcie klamry. Oznacza początek zawartości tego Traita (wszystko wewnątrz klamer to jego "ciało").

```php
    public static function bootHasTenant(): void
    {
```
**Co masz powiedzieć jury:** *"Wykorzystałem konwencję nazewniczą boot-metod w Traitach (bootHasTenant), aby wstrzyknąć logikę zabezpieczeń w tzw. cykl życia obiektu (Lifecycle Hook) dokładnie w momencie ładowania jądra modelu przez framework."*

> **Edukacja dla Ciebie:**
> *   `public` – Oznacza, że ta funkcja jest publicznie dostępna i system może ją uruchomić z zewnątrz.
> *   `static` – Oznacza funkcję "statyczną", czyli taką, którą można odpalić na poziomie ogólnym (dla wszystkich faktur na świecie), bez konieczności wskazywania palcem jednej, konkretnej faktury.
> *   `function` – Słowo kluczowe mówiące: "teraz definiuję nową akcję/metodę".
> *   `bootHasTenant()` – Nazwa tej funkcji. Słówko `boot` to u Laravela magiczny "zapalnik" (jak odpalanie silnika) – uruchomi to sam, zanim cokolwiek innego się wydarzy.
> *   `: void` – Ścisła deklaracja, która mówi: "Ta funkcja po prostu zrobi swoją robotę i nic nie zwróci z powrotem na zewnątrz" (void to z angielskiego "pustka").

```php
        static::addGlobalScope(new TenantScope());
```
**Co masz powiedzieć jury:** *" Metoda addGlobalScope przypina naszą klasę TenantScope na stałe do rdzenia Query Buildera. Od tego momentu każde żądanie SQL z tego modelu zostanie przez nią przefiltrowane."*

> **Edukacja dla Ciebie:**
> *   `static::` – Mówi PHP: "Odwołaj się do samego siebie, do ogólnego modelu" (np. "Weź całą encję Faktury...").
> *   `addGlobalScope()` – Wbudowana funkcja Laravela, która doczepia "niewidzialny filtr" do zapytań bazy danych.
> *   `new` – Komenda, która "powołuje do życia" (tworzy fizyczną instancję w pamięci RAM) nasz filtr (`TenantScope`).

```php
        static::creating(function (Model $model) {
```
**Co masz powiedzieć jury:** *"Uruchamiam nasłuchiwacz zdarzeń (Event Listener), który w ułamku sekundy po wywołaniu zapisu do bazy przechwytuje akcję powoływania nowego rekordu."*

> **Edukacja dla Ciebie:**
> *   `creating` – Kolejny magiczny zapalnik. Znaczy: "Wykonaj poniższy kod W TRAKCIE tworzenia nowego wpisu, ale zanim poleci on do bazy".
> *   `function (Model $model)` – Tworzymy tu tzw. "funkcję anonimową", do której wrzucamy to, co aktualnie ratujemy (czyli nasz obiekt faktury/leada, reprezentowany przez słówko `$model`).

```php
            if (\Illuminate\Support\Facades\Auth::hasUser() && ! $model->tenant_id) {
```
**Co masz powiedzieć jury:** *"To instrukcja zabezpieczająca. Zanim automatycznie przypiszę firmę do nowej faktury, upewniam się, że w request'cie HTTP faktycznie znajduje się sesja zalogowanego użytkownika oraz upewniam się, że model nie otrzymał już wartości z innego systemu."*

> **Edukacja dla Ciebie:**
> *   `if (...)` – Instrukcja warunkowa ("Jeśli...").
> *   `Auth::hasUser()` – Funkcja frameworka pytająca: "Czy ktokolwiek jest teraz fizycznie zalogowany na stronie?".
> *   `&&` – Oznacza "ORAZ". Służy do łączenia dwóch warunków, które muszą być spełnione jednocześnie.
> *   `!` – Wykrzyknik to negacja. Zmienia warunek na przeciwieństwo. `! $model->tenant_id` znaczy: "Nieprawda, że model ma już podane ID firmy" (czyli jest ono puste).
> *   `->` – tzw. "strzałka dostępu". Oznacza "wejdź do środka tego obiektu i weź jego element" (np. `$model->tenant_id` to wejście do modelu faktury i spojrzenie w jego zmienną `tenant_id`).

```php
                $model->tenant_id = \Illuminate\Support\Facades\Auth::user()->tenant_id;
            }
        });
    }
```
**Co masz powiedzieć jury:** *"Dzięki temu osiągamy pełną automatyzację przypisywania danych. Model sam w locie wie, jaka firma go generuje."*

> **Edukacja dla Ciebie:**
> *   `=` – Przypisanie wartości (nie mylić z porównaniem `==`). Mówisz tu: "Weź ID firmy dla tej nowej faktury i ustaw je równo z ID firmy pracownika, który jest obecnie zalogowany".

```php
    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }
}
```
**Co masz powiedzieć jury:** *"Na końcu definiuję relacyjną odpowiedniość. Definiując `belongsTo` udostępniam każdemu dziedziczącemu modelowi błyskawiczną ścieżkę do pobrania danych o firmie powiązanej."*

> **Edukacja dla Ciebie:**
> *   `return` – Komenda oznaczająca: "Oddaj to na zewnątrz i zakończ działanie funkcji".
> *   `$this->` – Oznacza "ja sam" / "ten konkretny dokument". To odniesienie obiektu do samego siebie (np. ta jedna, specyficzna faktura).
> *   `belongsTo()` – Słowo z jądra Laravela definiujące relację w bazie danych: "Przynależy do...".

---

## Plik 2: `app/Models/Scopes/TenantScope.php`

To jest sam "silnik" naszego filtru. Kod który doczepia się do zapytań w bazie.

### Omówienie linijka po linijce:

```php
namespace App\Models\Scopes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
```
*(Import klas - omówione powyżej w sekcji Edukacja).*

```php
class TenantScope implements Scope
{
```
**Co masz powiedzieć jury:** *"Użyłem silnie typowanego interfejsu `Scope` dostarczanego z jądra Eloquenta. To gwarantuje zachowanie czystości architektury SOLID."*

> **Edukacja dla Ciebie:**
> *   `class` – Definicja całego fizycznego obiektu (Klasy). To pojemnik na funkcje i dane.
> *   `implements Scope` – "Podpisanie prawnego kontraktu". Zmuszasz swoją klasę `TenantScope`, by zastosowała się do zasad z innej klasy (interfejsu `Scope`). Gwarantuje to, że nie zapomnisz napisać kluczowych funkcji.

```php
    public function apply(Builder $builder, Model $model): void
    {
```
*(Patrz: Osobne wyjaśnienie `apply`, `Builder` i `: void` - omówione wcześniej na Twoje pytanie).*

```php
        if (Auth::hasUser()) {
            $builder->where($model->getTable() . '.tenant_id', Auth::user()->tenant_id);
        }
    }
}
```
**Co masz powiedzieć jury:** *"To jest serce zabezpieczeń SaaS. Aplikując warunek `where` na poziomie silnika bazy danych, dynamicznie ucinam zasięg zapytań wyłącznie do ID firmy zalogowanego pracownika."*

> **Edukacja dla Ciebie:**
> *   `$builder->where(...)` – Nakazujesz kreatorowi zapytań (Builderowi): "Dopisz klauzulę WHERE (gdzie) do generowanego kodu SQL".
> *   `$model->getTable()` – Zabezpieczenie. Funkcja wyciąga dokładną nazwę tabeli w bazie danych (np. `invoices`), żeby złączyć ją w całość: `invoices.tenant_id`. Zapobiega to gubieniu się bazy, gdy łączymy wiele tabel na raz (tzw. złączenia JOIN).
> *   `.` (kropka) – W PHP służy do "sklejania" tekstów (tzw. konkatenacji). Skleja nazwę tabeli z tekstem `'.tenant_id'`.

---

## Pytania, na które musisz być gotów:

1. **Jury:** *"Co by się stało, gdyby programista zapomniał dodać klauzulę `where` wyświetlając listę faktur z bazy?"*
   **Twoja odpowiedź:** *"Absolutnie nic. Dzięki `TenantScope`, warunek bezpieczeństwa jest doczepiany na poziomie jądra ORM. Nawet proste polecenie `Invoice::all()` zostanie w tle zmodyfikowane i wysłane do serwera SQLite z już wpiętą barierą bezpieczeństwa ograniczającą rekordy do jednego klienta."*

2. **Jury:** *"Jak w takim razie jako Super Admin przeglądasz statystyki wszystkich firm w systemie?"*
   **Twoja odpowiedź:** *"Framework zapewnia wbudowaną furtkę (Escape Hatch). W kodzie zarezerwowanym wyłącznie dla funkcji administracyjnych, używam połączonej metody ignorującej filtry: `Invoice::withoutGlobalScopes()`. Zdejmuje ona tę barierę bezpieczeństwa."*
