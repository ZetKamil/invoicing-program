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
use App\Traits\HasBedrijf;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
```
**Co to jest:** Typowe importy klas z jądra Eloquent ORM. 
**Co masz powiedzieć:** *"Korzystam tu z najnowocześniejszych paczek frameworka, od obsługi miękkiego usuwania `SoftDeletes`, po wsparcie standardów ULID i polimorfizmu poprzez `MorphTo`."*

```php
#[Fillable([
    'bedrijf_id', 'customer_type', 'customer_id', 'quote_id', 'invoice_number', 'trailer_type',
    'subtotal', 'tax_total', 'total_amount', 'ubl_xml_path',
    'buyer_reference', 'notes', 'cmr_number', 'truck_license_plate', 'trailer_license_plate',
    'loading_address', 'delivery_address', 'loading_date', 'delivery_date', 'due_date', 'stripe_payment_intent_id',
    'paid_at', 'status', 'last_reminder_sent_at', 'cargo_weight_kg', 'pallet_count', 'incoterms', 'driver_name', 'driver_phone', 'is_reverse_charge', 'cmr_document_path'
])]
```
**Co to jest:** To jest nowość z PHP 8 zaimplementowana od niedawna w Laravelu. Zamiast starych tablic ukrytych w zmiennej `$fillable`, użyto mechanizmu Atrybutów (Attributes) z PHP. Deklaruje to, które kolumny bazy mogą być masowo modyfikowane za pomocą wejściowej tablicy z formularza (ochrona przed `Mass Assignment Vulnerability`).
**Co masz powiedzieć:** *"Odszedłem od klasycznego przypisywania pól do chronionej własności klasy `$fillable` na rzecz nowoczesnych Atrybutów wprowadzonych w najnowszych wersjach PHP. Definiuję tu zbiór autoryzowanych kolumn, blokując potencjalnym atakom typu Mass-Assignment szansę na nadpisanie kluczowych pól systemowych za pomocą spreparowanych formularzy."*

```php
class Invoice extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceFactory> */
    use HasFactory, HasUlids, HasBedrijf, SoftDeletes;
```
**Co to jest:** Definicja Klasy z podpiętymi czterema potężnymi Modułami (Traitami). `HasFactory` dla testów. `HasUlids` zmienia domyślne identyfikatory 1, 2, 3 na skomplikowane zbiory znaków. `HasBedrijf` opisywaliśmy wcześniej. `SoftDeletes` zamienia twarde usuwanie rekordu na dopisanie do niego daty kasacji, co pozwala na cofnięcie "kosza".
**Co masz powiedzieć:** *"Model wykorzystuje cztery filary infrastruktury. Szczególnie zwracam uwagę na trait `HasUlids`. Całkowicie porzuciłem standardowe, auto-inkrementowane ID na rzecz ULID-ów (Universally Unique Lexicographically Sortable Identifiers). Gwarantują one globalną odporność na ataki typu ID Enumeration, jednocześnie eliminując największą wadę starych UUID, czyli powolną fragmentację drzew decyzyjnych indeksów (B-Tree) w relacyjnych bazach danych, jako że ULID jest naturalnie sortowalny chronologicznie."*

```php
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'status' => InvoiceStatus::class,
            'last_reminder_sent_at' => 'datetime',
        ];
    }
```
**Co to jest:** To funkcja `casts()` (Rzutowanie Typów). Kiedy program pobiera te wartości z bazy danych SQLite, framework upewnia się, że nie odczyta ich jako zwykły "ciąg znaków" (string), ale zamieni na rygorystyczne obiekty PHP.
**Co masz powiedzieć:** *"Korzystam ze zdefiniowanej architektury rzutowania danych. Każda kwota, zanim trafi z bazy do mojego kodu, jest rygorystycznie przerabiana na dwumiejscowe wartości dziesiętne (`decimal:2`), aby uniknąć błędów zmiennoprzecinkowych przy księgowaniu. Dodatkowo kolumnę `status` w locie rzutuję na dedykowanego wyliczeniowca `Enum` (InvoiceStatus::class). Dzięki temu w całym kodzie operuję na bezpiecznych, ścisłych stałych, a nie na podatnych na literówki ciągach znakowych (magic strings)."*

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
**Co to jest:** Relacja Polimorficzna (`customer`) oraz standardowa (`quote`). Pozwala fakturze dynamicznie należeć do rożnych tabel (prospekt lub stały klient), a jednoczesnie trzyma rygorystyczne powiązanie (snapshot) do oryginalnej wyceny z której powstała (`quote_id`).
**Co masz powiedzieć:** *"Z uwagi na wymagania zwinności (Agility), wdrożyłem relację polimorficzną `morphTo` dla klienta, unikając pustych kolumn. Ponadto, wdrażając zasady Data Immutability, zapiąłem fakturę z oryginalną wyceną (`quote()`). Dzięki temu faktura jest prawdziwym, historycznym snapshotem operacji logistycznej, nienaruszającym oryginalnej umowy."*

```php
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function recalculateTotals(): void
    {
        // ... kod obliczający sumy z użyciem BCMath ...
    }
}
```
**Co to jest:** Klasyczna relacja "Jeden-do-Wielu" (One-to-Many) łącząca z tabelą InvoiceItems. Następnie znajduje się potężna metoda `recalculateTotals()`, wykorzystująca moduł BCMath (np. `bcmul`, `bcadd`) do przeliczania podatków i sum częściowych linijka po linijce.
**Co masz powiedzieć:** *"Zwieńczeniem modelu jest definicja klasycznej encji jeden-do-wielu dla wierszy na dokumencie fakturowym. Pod nią zaimplementowałem autorską logikę `recalculateTotals()`. 

Dane wewnątrz pętli pochodzą bezpośrednio z pozycji na fakturze (tabela `invoice_items`). Świadomie odrzuciłem standardowe operatory matematyczne PHP (takie jak `*` czy `+`), które w systemach informatycznych są obarczone błędem precyzji zmiennoprzecinkowej IEEE 754. Zamiast tego, wykorzystałem niskopoziomową bibliotekę BCMath (Binary Calculator), gwarantując stuprocentową, księgową precyzję na każdym etapie obliczeń. 

Aby jednak biblioteka BCMath działała z całkowitą dokładnością, wymaga przekazania do niej liczb nie w typie klasycznym (float/int), ale jako tekst (string). Dlatego rygorystycznie wymuszam to rzutowaniem typu `(string) $item->quantity` przed każdym obliczeniem. Gwarantuje to, że ominiemy fizyczne ograniczenia architektury procesora w obliczeniach zmiennoprzecinkowych, zapobiegając rozjazdowi salda na koniec roku księgowego o choćby jeden grosz."*

---

## Pytania, na które musisz być gotów:

1. **Jury:** *"Dlaczego nie zastosowaliście klasycznego pola `customer_id` z jednym przypisaniem w bazie, skoro ułatwia to tworzenie relacji klucza obcego w SQLite?"*
   **Twoja odpowiedź:** *"Ograniczenie klucza obcego ułatwia zapytania bezpośrednie w SQL, to prawda. Ale w systemie CRM musimy zachować niezwykłą elastyczność (tzw. zwinność – Agility). Relacja polimorficzna `morphTo` sprawia, że jeśli za rok wprowadzimy całkiem nowy typ klienta biznesowego, aplikacja natychmiast potrafi wystawiać mu faktury bez najmniejszej ingerencji w schematy starych tabel (Schema Migrations). Zapytania i tak są perfekcyjnie optymalne, ponieważ system operuje na założonych przeze mnie połączonych indeksach kompozytowych."*
