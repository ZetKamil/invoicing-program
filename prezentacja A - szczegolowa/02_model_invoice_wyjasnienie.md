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
**Co masz powiedzieć jury:** *"Korzystam tu z najnowocześniejszych paczek frameworka, od obsługi miękkiego usuwania `SoftDeletes`, po wsparcie standardów ULID i polimorfizmu poprzez `MorphTo`."*

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `use ...` – Importujemy do pliku gotowe wtyczki i zewnętrzne narzędzia, z których będziemy korzystać poniżej w klasie.
>
> **Na chłopski rozum:** Zanim wniesiesz nową fakturę do warsztatu i zaczniesz nad nią pracować, idziesz do magazynu po skrzynkę z odpowiednimi narzędziami (importujesz `use`). Wyciągasz młotek (SoftDeletes) czy śrubokręt (MorphTo) z frameworka, kładziesz na stole i od tej pory możesz z nich korzystać pod ich krótką nazwą.

```php
#[Fillable([
    'bedrijf_id', 'customer_type', 'customer_id', 'quote_id', 'invoice_number', 'trailer_type',
    'subtotal', 'tax_total', 'total_amount', 'ubl_xml_path',
    'buyer_reference', 'notes', 'cmr_number', 'truck_license_plate', 'trailer_license_plate',
    'loading_address', 'delivery_address', 'loading_date', 'delivery_date', 'due_date', 'stripe_payment_intent_id',
    'paid_at', 'status', 'last_reminder_sent_at', 'cargo_weight_kg', 'pallet_count', 'incoterms', 'driver_name', 'driver_phone', 'is_reverse_charge', 'cmr_document_path'
])]
```
**Co masz powiedzieć jury:** *"Odszedłem od klasycznego przypisywania pól do chronionej tablicy `$fillable` na rzecz nowoczesnych Atrybutów (Attributes) z najnowszych wersji PHP. Definiuję tu zbiór autoryzowanych kolumn, blokując potencjalnym atakom typu Mass-Assignment szansę na nadpisanie kluczowych pól systemowych."*

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `#[]` – Tzw. "Atrybut" w PHP 8. Zawiera on metadane przekazywane do silnika PHP bez tworzenia fizycznych zmiennych w klasie.
> *   `Fillable` – Wskazuje autoryzowane pola. Mówi: "Tylko te wymienione kolumny mogą być zapisywane do bazy danych z tablic masowych (np. bezpośrednio z wysłanego formularza)".
> *   `[ ... ]` – Nawiasy kwadratowe to w PHP "Tablica" (Array), czyli lista elementów ujętych w cudzysłowy.
>
> **Na chłopski rozum:** Wyobraź sobie Atrybut `#[]` jako magiczną "Naklejkę" (żółtą karteczkę Post-it), którą przyklejamy na zewnątrz teczki z Fakturą. Zanim system w ogóle otworzy teczkę, widzi naklejkę z instrukcją `Fillable`, która mówi jak zachować się na "bramce w klubie" - to jest ostra lista gości (lista autoryzowanych kolumn). Jeśli ktoś złośliwy spróbuje z zewnątrz przepchnąć kolumnę `is_admin`, system spojrzy na naklejkę, nie znajdzie jej na liście gości i odrzuci te dane bez mrugnięcia okiem. Zabezpiecza to przed atakiem Mass-Assignment.

```php
class Invoice extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceFactory> */
    use HasFactory, HasUlids, HasBedrijf, SoftDeletes;
```
**Co masz powiedzieć jury:** *"Model wykorzystuje cztery filary infrastruktury. Szczególnie zwracam uwagę na trait `HasUlids`. Całkowicie porzuciłem standardowe, auto-inkrementowane ID na rzecz ULID-ów (Universally Unique Lexicographically Sortable Identifiers)."*

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `class Invoice` – Definicja nowego obiektu (encj) "Faktury".
> *   `extends Model` – Dziedziczenie. Obiekt `Invoice` dziedziczy po potężnej wbudowanej klasie `Model` dostarczonej przez Laravela, przejmując jej wszystkie mechanizmy orm (zapis/odczyt z bazy).
> *   `/** ... */` – DocBlock, czyli komentarz dla programisty (ignorowany przez kod).
> *   `use ...` (wewnątrz klasy) – Załączenie (wstrzyknięcie) traitów, modyfikujących zachowanie klasy.
>
> **Na chłopski rozum:**
> Słowo `extends` to jak "genetyka". Mówisz: "Stwórz dziecko o nazwie Faktura, ale niech odziedziczy ono absolutnie wszystkie nadludzkie moce po ojcu zwanym Model (który potrafi sam pisać do bazy danych)".
> A `use HasFactory...` wewnątrz to wpinanie wtyczek USB do tego dziecka. Np. wtyczka `SoftDeletes` daje supermoc: "Kosza Windowsa". Gdy usuniesz fakturę, w bazie pojawi się tylko pieczątka "Skasowane dzisiaj", ale wpis nadal fizycznie zostaje w bazie do celów księgowych!

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

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `protected` – Widoczność funkcji tylko dla "siebie i swoich dzieci". Kod z innych modułów nie może tego samodzielnie wywołać.
> *   `array` – Zadeklarowany typ zwracany: funkcja musi oddać tablicę/listę.
> *   `=>` – Operator przypisania w tablicy (klucz przypisany do wartości).
> *   `::class` – Bezwzględna ścieżka do obiektu wyciągana w formie tekstu.
> *   `Enum` (Wyliczeniowiec) – Specjalny obiekt w PHP (np. `InvoiceStatus`), który zawiera sztywną, zamkniętą listę dozwolonych opcji (np. tylko: DRAFT, SENT, PAID).
>
> **Na chłopski rozum:**
> `protected` to "zamknięty pokój dla personelu". Ta funkcja to translator (tłumacz przysięgły). Kiedy bierzesz brzydką kwotę z bazy danych, translator natychmiast upewnia się (`=> 'decimal:2'`), że w PHP ta kwota ma równo dwa miejsca po przecinku.
> Z kolei rzutowanie statusu na **Enum** to zastąpienie pustego pola tekstowego na **sztywną listę rozwijaną**. Gdybyś trzymał status jako zwykły tekst, jakiś programista mógłby zrobić literówkę i zapisać do bazy status `"paidd"` zamiast `"paid"`, co zepsułoby cały system. Enum to wydrukowane menu w restauracji. System mówi bazie: *"Od teraz status to nie jest byle jaki tekst. To musi być jedna z precyzyjnie określonych opcji dostępnych w zamkniętej karcie menu InvoiceStatus::class. Innej nie przyjmę!"*.

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

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `: MorphTo` – Silne typowanie zwracanego wyniku. Informuje kompilator, że wraca obiekt specyficznej relacji polimorficznej.
> *   `$this->morphTo()` – Uruchamia dynamiczne wiązanie polimorficzne na bazie kolumn z sufiksem `_type` i `_id`.
>
> **Na chłopski rozum:**
> Relacja polimorficzna `MorphTo` to po prostu "Uniwersalny Pilot RTV". Nie ma on przypisanego jednego na stałe telewizora. Model sam patrzy w bazę na pole `customer_type` i wie z niego: "Aha! Przesuwam przełącznik na pilocie! Tym razem obsługuję Lead'a, a nie Klienta!". Dzięki temu Faktura może wystawiona na cokolwiek – i na firmę, i na osobę prywatną, bez dodawania kolejnych niepotrzebnych kolumn w bazie.

```php
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
```
**Co masz powiedzieć jury:** *"Zwieńczeniem modelu jest definicja klasycznej encji jeden-do-wielu dla wierszy na dokumencie fakturowym."*

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `HasMany` – Typ relacji bazy danych jeden-do-wielu (One to Many).
> *   `$this->hasMany(...)` – Znajduje połączone rekordy w obcej tabeli posiłkując się id_faktury.
>
> **Na chłopski rozum:** `HasMany` ("Ma wiele") to klasyczny "Segregator na rachunki". Mówisz Fakturze: "Weź swój własny numer ID, pójdź do szafy pełnej wszystkich pozycji na świecie (InvoiceItems) i wyciągnij całą teczkę linijek, które mają napisany Twój numerek".

```php
    public function recalculateTotals(): void
    {
        $sub = '0.00';
        $tax = '0.00';
        $total = '0.00';

        foreach ($this->items as $item) {
            $qty = (string) $item->quantity;
            $price = (string) $item->unit_price;
            $taxRate = (string) $item->tax_rate;

            $lineSubtotal = bcmul($qty, $price, 10);
            $taxMultiplier = bcdiv($taxRate, '100', 10);
            $lineTax = bcmul($lineSubtotal, $taxMultiplier, 10);

            $sub = bcadd($sub, $lineSubtotal, 10);
            $tax = bcadd($tax, $lineTax, 10);
        }

        $this->subtotal = bcadd($sub, '0', 2);
        $this->tax_total = bcadd($tax, '0', 2);
        $this->total_amount = bcadd($this->subtotal, $this->tax_total, 2);
    }
}
```

**Co masz powiedzieć jury:** *"Aby zagwarantować bezwzględną precyzję finansową i uniknąć odchyleń zmiennoprzecinkowych (floating-point drift), wdrożyłem funkcję recalculateTotals używającą kalkulacji BCMath na typach string. Gwarantuje to spójność z bramkami takimi jak Stripe czy Peppol."*

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `foreach ($this->items as $item)` – Pętla. Bierze wszystkie "wiele linijek" (z funkcji `hasMany`) i po kolei wykonuje na nich obliczenia.
> *   `(string)` – Rzutowanie zmiennej na tekst (napis). Traktuje liczby (np. 14.50) tak, jakby były literami, żeby procesor nie zepsuł ułamków przy przeliczaniu.
> *   `bcmul(...)`, `bcdiv(...)`, `bcadd(...)` – Z ang. *Math Multiply*, *Divide*, *Add*. Funkcje z wbudowanego kalkulatora absolutnej precyzji PHP (BCMath). Wykonują mnożenie, dzielenie i dodawanie na tekście z precyzją, którą wpiszemy na końcu (np. `10` miejsc po przecinku w trakcie obliczeń, obcięte do `2` miejsc przy podsumowaniu).
>
> **Na chłopski rozum:** To jest ten słynny "Kalkulator FinTech", o którym mówiłeś przy okazji Fazy 4 (plik nr 08). Zwykły procesor, jak każesz mu pomnożyć `14.90 * 3 * 1.21`, robi się głupi i wypluwa np. `54.08699999`. 
> Dlatego wyłapujesz każdą usługę (każdą linijkę z `$this->items`), po chamsku zamieniasz ją na czysty tekst (`string`) i wrzucasz do kalkulatora `BCMath`. Kalkulator ten wykonuje działania matematyczne dokładnie tak, jak uczyłeś się w szkole podstawowej (podpisując sobie cyferki i licząc po jednym znaku na kartce). Na końcu obcinasz wynik do 2 miejsc po przecinku (np. `bcadd(..., 2)`). System podatkowy i Stripe dostają kwoty zgadzające się do jednego ułamka centa! 
---

## Pytania, na które musisz być gotów:

1. **Jury:** *"Dlaczego nie zastosowaliście klasycznego pola `customer_id` z jednym przypisaniem w bazie, skoro ułatwia to tworzenie relacji klucza obcego w SQLite?"*
   **Twoja odpowiedź:** *"Ograniczenie klucza obcego ułatwia zapytania w surowym SQL, to prawda. Ale w systemie CRM musimy zachować niezwykłą elastyczność. Relacja polimorficzna `morphTo` sprawia, że jeśli za rok wprowadzimy całkiem nowy typ klienta biznesowego, aplikacja natychmiast potrafi wystawiać mu faktury bez najmniejszej ingerencji w schematy starych tabel (brak potrzeby ciężkich Schema Migrations)."*
