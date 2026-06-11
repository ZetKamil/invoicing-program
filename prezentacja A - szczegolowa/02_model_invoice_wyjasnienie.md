# 2. Zaawansowana Logika Biznesowa (Grube Modele / Fat Models)

**Gdzie znajduje się kod:**
- `app/Models/Invoice.php`

**Co musisz wiedzieć z lotu ptaka:**
W klasycznych, starych projektach cała skomplikowana logika bywa wypisywana w kontrolerach. W nowoczesnych projektach używamy tzw. podejścia "Fat Models" (Grube modele) – co oznacza, że sam model potrafi zaopiekować się rzutowaniem własnych typów danych, zaokrąglaniem kwot na etapie zapisu oraz niezwykle rozbudowanymi relacjami polimorficznymi. Omówienie tego pliku po linijce dowiedzie, że operujesz najnowszymi funkcjonalnościami PHP (np. Atrybutami klas z PHP 8).

---

## Plik: `app/Models/Invoice.php`

### Omówienie linijka po linijce:

```php
namespace App\Models;
use App\Enums\InvoiceStatus;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
```
**Co masz powiedzieć jury:** *"Korzystam tu z najnowocześniejszych paczek frameworka, od obsługi miękkiego usuwania `SoftDeletes`, po wsparcie standardów ULID i polimorfizmu poprzez `MorphTo`."*

> **Edukacja dla Ciebie:**
> *   `use ...` – Importujemy do pliku gotowe wtyczki. To tak jak byśmy włożyli nowe narzędzia do walizki, zanim zaczniemy budować fakturę (np. wtyczkę `SoftDeletes`, która zapobiega trwałemu usunięciu danych z bazy).

```php
#[Fillable([
    'tenant_id', 'customer_type', 'customer_id', 'quote_id', 'invoice_number', 'trailer_type',
    'subtotal', 'tax_total', 'total_amount', 'ubl_xml_path',
    'buyer_reference', 'notes', 'cmr_number', 'truck_license_plate', 'trailer_license_plate',
    'loading_address', 'delivery_address', 'loading_date', 'delivery_date', 'due_date', 'stripe_payment_intent_id',
    'paid_at', 'status', 'last_reminder_sent_at', 'cargo_weight_kg', 'pallet_count', 'incoterms', 'driver_name', 'driver_phone', 'is_reverse_charge', 'cmr_document_path'
])]
```
**Co masz powiedzieć jury:** *"Odszedłem od klasycznego przypisywania pól do chronionej tablicy `$fillable` na rzecz nowoczesnych Atrybutów (Attributes) z najnowszych wersji PHP. Definiuję tu zbiór autoryzowanych kolumn, blokując potencjalnym atakom typu Mass-Assignment szansę na nadpisanie kluczowych pól systemowych."*

> **Edukacja dla Ciebie:**
> *   `#[]` – Tzw. "Atrybut" w PHP 8. To "magiczna naklejka", którą przyklejamy nad klasą. Zawiera ona metadane. Zamiast pisać normalny kod w środku klasy, dajemy systemowi informację z góry.
> *   `Fillable` – Mówi: "Tylko te wymienione pola mogą być nadpisane w bazie danych bezpośrednio z formularza internetowego". Wszystko inne zostanie zignorowane.
> *   `[ 'tenant_id', ... ]` – Nawiasy kwadratowe `[]` w PHP oznaczają "Tablicę" (Array), czyli po prostu listę elementów. Podajemy w niej listę dozwolonych nazw kolumn ujętych w cudzysłowach `''`.

```php
class Invoice extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceFactory> */
    use HasFactory, HasUlids, HasTenant, SoftDeletes;
```
**Co masz powiedzieć jury:** *"Model wykorzystuje cztery filary infrastruktury. Szczególnie zwracam uwagę na trait `HasUlids`. Całkowicie porzuciłem standardowe, auto-inkrementowane ID na rzecz ULID-ów (Universally Unique Lexicographically Sortable Identifiers)."*

> **Edukacja dla Ciebie:**
> *   `class Invoice` – Powołujemy do życia nowy obiekt: "Fakturę".
> *   `extends Model` – Słówko `extends` to dziedziczenie (Dziedziczy z "Model"). Mówisz przez to: "Moja Faktura to nie jest zwykła klasa. Ona odziedzicza wszystkie moce bazy danych po potężnej, wbudowanej klasie Model z frameworka Laravel".
> *   `/** ... */` – To po prostu komentarz w kodzie (tzw. DocBlock). Jest ignorowany przez serwer, ale pomaga programistom czytać kod.
> *   `use ...` (wewnątrz klamer klasy) – Służy do "wpinania" Traitów (wtyczek), o których mówiliśmy w poprzednim pliku. Dzięki jednemu słowu `SoftDeletes`, model uczy się tzw. miękkiego usuwania (zamiast wykasować wiersz z bazy, stawia przy nim datę skasowania - działa jak kosz Windows).

```php
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'status' => InvoiceStatus::class,
        ];
    }
```
**Co masz powiedzieć jury:** *"Każda kwota, zanim trafi z bazy do mojego kodu, jest rygorystycznie przerabiana na dwumiejscowe wartości dziesiętne (`decimal:2`), aby uniknąć błędów zmiennoprzecinkowych. Dodatkowo kolumnę `status` w locie rzutuję na dedykowanego wyliczeniowca `Enum` (InvoiceStatus::class)."*

> **Edukacja dla Ciebie:**
> *   `protected` – Widoczność funkcji. W przeciwieństwie do `public`, oznacza to, że tej funkcji nie może zawołać byle kontroler z zewnątrz. Jest ona "chroniona" i przeznaczona tylko do użytku wewnętrznego tej klasy (oraz jej dzieci).
> *   `array` – Deklaracja zwracanego typu. Mówisz tu: "Ta funkcja musi na końcu wyrzucić z siebie Tablicę (listę)".
> *   `=>` – Operator przypisania klucza do wartości w tablicy. Czytaj to jako: "Kolumna 'subtotal' MA SIĘ ZAMIENIĆ NA 'decimal:2'".
> *   `::class` – Specjalny zapis w PHP, który zamiast ściągać sam obiekt, pobiera jego bezwzględną, pełną ścieżkę jako tekst (aby program wiedział, jak się do niego odwołać).

```php
    public function customer(): MorphTo
    {
        return $this->morphTo();
    }

    public function quote(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }
```
**Co masz powiedzieć jury:** *"Ze względu na wymagania zwinności, wdrożyłem relację polimorficzną `morphTo` dla klienta, unikając pustych kolumn. Dodatkowo, aby zachować tzw. Data Immutability, zapiąłem fakturę twardo z oryginalną wyceną przez `quote()`. Dzięki temu faktura zawsze stanowi legalny snapshot z momentu wykonania zlecenia."*

> **Edukacja dla Ciebie:**
> *   `: MorphTo` – Mówisz programistom: "Ta funkcja zwraca powiązanie z inną tabelą w bazie danych (polimorficzne)". Znowu silne typowanie chroni przed głupimi błędami.
> *   `$this->morphTo()` – Uruchamiasz wewnętrzną maszynerię bazy danych, która sama sprawdza kolumny `customer_type` i `customer_id` i wie, w której tabeli (Users czy Leads) szukać klienta dla tej faktury.

```php
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
```
**Co masz powiedzieć jury:** *"Zwieńczeniem modelu jest definicja klasycznej encji jeden-do-wielu dla wierszy na dokumencie fakturowym."*

> **Edukacja dla Ciebie:**
> *   `HasMany` – Relacja "Ma wiele".
> *   `$this->hasMany(InvoiceItem::class)` – Mówi bazie danych: "Pójdź do tabeli WierszyFaktur (InvoiceItems) i znajdź mi wszystkie linijki, które na sobie mają przypięty mój własny identyfikator faktury".

---

## Pytania, na które musisz być gotów:

1. **Jury:** *"Dlaczego nie zastosowaliście klasycznego pola `customer_id` z jednym przypisaniem w bazie, skoro ułatwia to tworzenie relacji klucza obcego w SQLite?"*
   **Twoja odpowiedź:** *"Ograniczenie klucza obcego ułatwia zapytania w surowym SQL, to prawda. Ale w systemie CRM musimy zachować niezwykłą elastyczność. Relacja polimorficzna `morphTo` sprawia, że jeśli za rok wprowadzimy całkiem nowy typ klienta biznesowego, aplikacja natychmiast potrafi wystawiać mu faktury bez najmniejszej ingerencji w schematy starych tabel (brak potrzeby ciężkich Schema Migrations)."*
