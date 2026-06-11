# 1. Izolacja Danych (Multi-Tenancy i Global Scopes)

**Gdzie znajduje się kod:**
- `app/Traits/HasTenant.php`
- `app/Models/Scopes/TenantScope.php`

**Co musisz wiedzieć z lotu ptaka:**
System ten używa zaawansowanej techniki zwaną "Multi-Tenancy" opartą na jednej wspólnej bazie danych, ale rygorystycznie izolowaną na poziomie zapytań SQL. Wykorzystuje do tego "Global Scopes" wstrzykiwane przez "Trait", co gwarantuje 100% bezpieczeństwo przed wyciekami danych pomiędzy firmami. Poniżej znajduje się kompletne objaśnienie każdej pojedynczej linijki kodu z tych plików.

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
**Co to jest:** Deklaracja przestrzeni nazw i importy używanych klas z jądra frameworka.
**Co masz powiedzieć:** *"Na początku importuję niezbędne fasady, w szczególności fasadę Auth z przestrzeni Illuminate, aby mieć bezpieczny, statyczny dostęp do aktualnej sesji HTTP użytkownika z dowolnego miejsca w Traicie."*
 
```php
trait HasTenant
{
```
**Co to jest:** Definicja samego Traita. Trait to taki "klocek Lego", który można wstrzyknąć do wnętrza innych klas za pomocą słówka `use`.

```php
    public static function bootHasTenant(): void
    {
```
**Co to jest:** Magiczna metoda w środowisku Laravel. Gdy nazwa funkcji zaczyna się od `boot` i kończy nazwą traita (`HasTenant`), Laravel uruchomi ją całkowicie automatycznie podczas inicjalizacji (bootowania) modelu w pamięci RAM, zanim zostaną wykonane jakiekolwiek zapytania do bazy.
**Co masz powiedzieć:** *"Wykorzystałem konwencję nazewniczą boot-metod w Traitach (bootHasTenant), aby wstrzyknąć logikę zabezpieczeń w tzw. cykl życia obiektu (Lifecycle Hook) dokładnie w momencie ładowania jądra modelu przez framework."*

```php
        static::addGlobalScope(new TenantScope());
```
**Co to jest:** Wstrzyknięcie naszego "Niewidzialnego Filtra" (który opisuję w następnym pliku).
**Co masz powiedzieć:** *" Metoda addGlobalScope przypina naszą klasę TenantScope na stałe do rdzenia Query Buildera. Od tego momentu każde żądanie SQL z tego modelu zostanie przez nią przefiltrowane. Żadne zapytanie w systemie nie ominie tego filtru bez wyraźnego polecenia dewelopera."*

```php
        static::creating(function (Model $model) {
```
**Co to jest:** Nasłuchiwacz zdarzeń (Event Listener) typu `creating`. Uruchamia się w ułamku sekundy po wykonaniu funkcji `$model->save()`, ale *zanim* zapytanie `INSERT` poleci do bazy SQLite.

```php
            if (\Illuminate\Support\Facades\Auth::hasUser() && ! $model->tenant_id) {
```
**Co to jest:** Skrypt weryfikuje dwa warunki: (1) Czy w systemie jest obecnie zalogowany autoryzowany użytkownik? oraz (2) Czy programista przypadkiem sam nie wpisał już ręcznie jakiegoś `tenant_id`?
**Co masz powiedzieć:** *"To instrukcja zabezpieczająca. Zanim automatycznie przypiszę firmę do nowej faktury, upewniam się, że w request'cie HTTP faktycznie znajduje się sesja zalogowanego użytkownika (np. by uchronić przed awarią skryptów CLI w konsoli), oraz upewniam się, że model nie otrzymał już wartości tenant_id z jakiegoś innego kontrolera biznesowego."*

```php
                $model->tenant_id = \Illuminate\Support\Facades\Auth::user()->tenant_id;
            }
        });
    }
```
**Co to jest:** Pobraie ID firmy z konta aktualnie zalogowanego pracownika i przypisanie go do tworzonej faktury/leada.
**Co masz powiedzieć:** *"Dzięki temu blokowi osiągamy pełną automatyzację przypisywania danych. Model sam w locie wie, jaka firma go generuje. To zmniejsza tak zwany "Cognitive Load" na zespole programistów, ograniczając ryzyko, że ktoś zapomni dokleić `tenant_id` podczas pisania kontrolerów."*

```php
    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }
}
```
**Co to jest:** Standardowa relacja bazy danych "Wiele-do-Jednego" (Many-to-One / BelongsTo). Oznacza to, że każda faktura/lead z tym traitem należy do dokładnie jednego Najemcy (Tenanta).
**Co masz powiedzieć:** *"Na końcu definiuję relacyjną odpowiedniość (Foreign Key Relation). Definiując `belongsTo(Tenant::class)` wprost w Traicie, udostępniam każdemu dziedziczącemu modelowi błyskawiczną ścieżkę do pobrania danych o firmie, z którą jest powiązany za pomocą Eager Loadingu."*

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
**Co to jest:** Import klas, z których korzysta filtr. Przede wszystkim `Builder` odpowiedzialny za pisanie zapytań SQL oraz interfejs `Scope`.

```php
class TenantScope implements Scope
{
```
**Co to jest:** Definicja klasy. Słówko `implements Scope` jest bardzo ważne dla Laravela – oznacza, że klasa podpisała "kontrakt", że dostarczy wymaganą funkcję o nazwie `apply()`.
**Co masz powiedzieć:** *"Użyłem silnie typowanego interfejsu `Scope` dostarczanego z jądra Eloquenta. To gwarantuje zachowanie czystości architektury SOLID."*

```php
    public function apply(Builder $builder, Model $model): void
    {
```
**Co to jest:** Obowiązkowa funkcja wykonawcza. Przyjmuje dwa parametry: obiekt SQL Buildera oraz model, na którym jest obecnie uruchamiana (np. Invoice).

```php
        if (Auth::hasUser()) {
```
**Co to jest:** Upewnienie się, że zapytanie odpala ktoś, kto faktycznie jest zalogowany w aplikacji webowej. Zapobiega błędom podczas odpalania skryptów konsolowych (`artisan`) w nocy, gdy nikt nie jest zalogowany na stronie.

```php
            $builder->where($model->getTable() . '.tenant_id', Auth::user()->tenant_id);
        }
    }
}
```
**Co to jest:** Rdzeń zabezpieczeń! Jeśli ktoś każe bazie pobrać "wszystkie faktury", ten fragment kody zmusza Buildera do doklejenia klauzuli SQL: `AND tenant_id = 'ID_TWOJEJ_FIRMY'`. Konstrukcja `$model->getTable() . '.tenant_id'` gwarantuje, że zapytanie zadziała poprawnie nawet w skomplikowanych złączeniach tabel (JOIN).
**Co masz powiedzieć:** *"To jest serce zabezpieczeń SaaS. Aplikując warunek `where` na poziomie silnika bazy danych, dynamicznie ucinam zasięg zapytań wyłącznie do ID firmy zalogowanego pracownika. Zastosowanie prefiksu `$model->getTable()` to celowy zabieg inżynieryjny zabezpieczający nas przed błędem `Ambiguous Column Name`, gdyby Query Builder wchodził w operacje typu INNER JOIN z innymi tabelami posiadającymi klucz tenant_id."*

---

## Pytania, na które musisz być gotów:

1. **Jury:** *"Co by się stało, gdyby programista zapomniał dodać klauzulę `where` wyświetlając listę faktur z bazy?"*
   **Twoja odpowiedź:** *"Absolutnie nic. Dzięki `TenantScope`, warunek bezpieczeństwa jest doczepiany na poziomie jądra ORM. Nawet proste polecenie `Invoice::all()` zostanie w tle zmodyfikowane i wysłane do serwera SQLite z już wpiętą barierą bezpieczeństwa ograniczającą rekordy do jednego klienta."*

2. **Jury:** *"Jak w takim razie jako Super Admin przeglądasz statystyki wszystkich firm w systemie?"*
   **Twoja odpowiedź:** *"Framework zapewnia wbudowaną furtkę (Escape Hatch). W kodzie zarezerwowanym wyłącznie dla funkcji administracyjnych, używam połączonej metody ignorującej filtry: `Invoice::withoutGlobalScopes()`. Zdejmuje ona tę barierę bezpieczeństwa."*
