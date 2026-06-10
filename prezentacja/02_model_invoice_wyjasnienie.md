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
**Co to jest:** Typowe importy klas z jądra Eloquent ORM. 
**Co masz powiedzieć:** *"Korzystam tu z najnowocześniejszych paczek frameworka, od obsługi miękkiego usuwania `SoftDeletes`, po wsparcie standardów ULID i polimorfizmu poprzez `MorphTo`."*

```php
#[Fillable([
    'tenant_id', 'customer_type', 'customer_id', 'invoice_number',
    'subtotal', 'tax_total', 'total_amount', 'ubl_xml_path',
    'buyer_reference', 'due_date', 'stripe_payment_intent_id',
    'paid_at', 'status', 'last_reminder_sent_at'
])]
```
**Co to jest:** To jest nowość z PHP 8 zaimplementowana od niedawna w Laravelu. Zamiast starych tablic ukrytych w zmiennej `$fillable`, użyto mechanizmu Atrybutów (Attributes) z PHP. Deklaruje to, które kolumny bazy mogą być masowo modyfikowane za pomocą wejściowej tablicy z formularza (ochrona przed `Mass Assignment Vulnerability`).
**Co masz powiedzieć:** *"Odszedłem od klasycznego przypisywania pól do chronionej własności klasy `$fillable` na rzecz nowoczesnych Atrybutów wprowadzonych w najnowszych wersjach PHP. Definiuję tu zbiór autoryzowanych kolumn, blokując potencjalnym atakom typu Mass-Assignment szansę na nadpisanie kluczowych pól systemowych za pomocą spreparowanych formularzy."*

```php
class Invoice extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceFactory> */
    use HasFactory, HasUlids, HasTenant, SoftDeletes;
```
**Co to jest:** Definicja Klasy z podpiętymi czterema potężnymi Modułami (Traitami). `HasFactory` dla testów. `HasUlids` zmienia domyślne identyfikatory 1, 2, 3 na skomplikowane zbiory znaków. `HasTenant` opisywaliśmy wcześniej. `SoftDeletes` zamienia twarde usuwanie rekordu na dopisanie do niego daty kasacji, co pozwala na cofnięcie "kosza".
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
```
**Co to jest:** Relacja Polimorficzna (Polymorphic Relation). Pozwala fakturze przypiąć się nie tylko do jednej z góry ustalonej tabeli (np. do użytkownika), ale dynamicznie wpinać się do *różnych* tabel. Kolumny `customer_type` i `customer_id` przechowują informację do *jakiej tabeli* należy ten klucz obcy.
**Co masz powiedzieć:** *"Ze względu na wymagania modelu biznesowego (gdzie dokument może należeć albo do surowego prospektu/leada, albo już stałego klienta/usera), odrzuciłem klasyczne podejście statycznych kluczy obcych na rzecz relacji polimorficznej `morphTo`. Skutkuje to dużo czystszą architekturą bazy danych: unikamy tzw. długich tabel z pustymi kolumnami `lead_id` czy `user_id`."*

```php
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
```
**Co to jest:** Klasyczna relacja "Jeden-do-Wielu" (One-to-Many). Każda faktura składa się z wielu pozycji. Ta funkcja to most łączący z tabelą InvoiceItems.
**Co masz powiedzieć:** *"Zwieńczeniem modelu jest definicja klasycznej encji jeden-do-wielu dla wierszy na dokumencie fakturowym, rozwiązana z zachowaniem dobrych praktyk nazewnictwa metod Eloquent."*

---

## Pytania, na które musisz być gotów:

1. **Jury:** *"Dlaczego nie zastosowaliście klasycznego pola `customer_id` z jednym przypisaniem w bazie, skoro ułatwia to tworzenie relacji klucza obcego w SQLite?"*
   **Twoja odpowiedź:** *"Ograniczenie klucza obcego ułatwia zapytania bezpośrednie w SQL, to prawda. Ale w systemie CRM musimy zachować niezwykłą elastyczność (tzw. zwinność – Agility). Relacja polimorficzna `morphTo` sprawia, że jeśli za rok wprowadzimy całkiem nowy typ klienta biznesowego, aplikacja natychmiast potrafi wystawiać mu faktury bez najmniejszej ingerencji w schematy starych tabel (Schema Migrations). Zapytania i tak są perfekcyjnie optymalne, ponieważ system operuje na założonych przeze mnie połączonych indeksach kompozytowych."*
