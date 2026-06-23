# 🌟 KROK 1B: Architektura Modelu i Bazy Danych (Customer)

Ten plik to kontynuacja Kroku 1 (gdzie mówiłeś o formularzu). Tutaj skupiamy się na tym, co dzieje się głębiej – czyli jak ten formularz łączy się z bazą danych i jak zabezpieczyłeś sam Model.

**Pliki do omówienia:** `app/Models/Customer.php` oraz `app/Filament/Resources/Customers/CustomerResource.php`

---

## 1. Menedżer (Resource) - `CustomerResource.php`
Zanim przejdziesz do bazy, pokaż jak łączysz Formularz z Modelem. Ten plik to "dyrygent". Nie zajmuje się widokiem ani bazą bezpośrednio, tylko wskazuje palcem, kto ma co robić.

**Co warto pokazać w kodzie:**
- `protected static ?string $model = Customer::class;` (linijka 21)
- **Tłumaczenie dla Jury:** "Plik `CustomerResource` to serce mojego CRUDa. To on spina formularz (widok) z logiką bazy danych. W linijce z `$model` przypisuję mu, że ma komunikować się wyłącznie z modelem klasy `Customer`. Taki wzorzec architektoniczny gwarantuje, że pliki widoku (np. formularze) nigdy bezpośrednio nie dotykają bazy danych, co chroni kod przed tzw. 'spaghetti code'."

---

## 2. Linia po Linii: Model `Customer.php`
Ten plik to ostateczna brama do bazy danych. Jury będzie pod ogromnym wrażeniem, bo używasz tu technik programowania z zaawansowanych systemów biznesowych (Enterprise).

### A) PHP 8 Attributes (Nowoczesny kod)
- Zaznacz linijkę 15: `#[Fillable(['bedrijf_id', 'company_name', ...])]`
- **Co mówisz Jury:** "Zabezpieczam tu model przed atakiem typu **Mass Assignment Vulnerability**. Zamiast przestarzałych tablic `protected $fillable`, używam nowoczesnych Atrybutów PHP 8. Wpuszczam do bazy tylko i wyłącznie te kolumny, które tutaj wymieniłem, a całą resztę (np. próbę wstrzyknięcia przez hakera `is_admin=true`) system ignoruje."

### B) HasUlids (Bezpieczeństwo adresów URL)
- Zaznacz w linijce 18: `use HasUlids;`
- **Co mówisz Jury:** "Zamiast przewidywalnego id=1, id=2, tworzę potężne kryptograficzne ciągi znaków (ULID). Haker nie odgadnie id innej firmy patrząc w URL (zapobiega to enumeracji). Dodatkowo, w przeciwieństwie do UUID, ULIDy są sortowalne po czasie, co przyspiesza odczyt bazy."

### C) SoftDeletes & Prunable (Prawo RODO / GDPR)
- Zaznacz: Traity `SoftDeletes, Prunable` oraz funkcję `prunable()` (linijki 23-26).
- **Co mówisz Jury:** "Gdy dyspozytor omyłkowo usunie klienta, `SoftDeletes` ukrywa go (nadając datę `deleted_at`), ale pozwala mi odzyskać dane w razie błędu. Jednak ze względu na europejskie prawo RODO (prawo do bycia zapomnianym), wdrożyłem funkcję `prunable()`. Dzięki niej, serwer w nocy automatycznie kasuje bezpowrotnie z bazy SQL wszystkich klientów, którzy byli 'miękko' usunięci ponad rok temu. Baza jest szybka i legalna."

### D) Funkcja `casts()` (Automatyczna konwersja)
- Zaznacz: linijki 33-38 z `'metadata' => 'array'`.
- **Co mówisz Jury:** "W bazie trzymam pole `metadata` jako tekst JSON. Funkcja `casts()` automatycznie dekoduje ten tekst do wygodnej tablicy (Array) w PHP przy odczycie, i kompresuje go z powrotem do tekstu przy zapisie. Nie muszę tego robić ręcznie."

### E) Funkcja `invoices()` z `MorphMany` (Polimorfizm)
- Zaznacz: linijki 43-45 z `morphMany(Invoice::class, 'customer')`.
- **Co mówisz Jury:** "Zamiast zwykłego `HasMany` używam tu relacji polimorficznej (`MorphMany`). Dlaczego? W ten sposób faktura w moim systemie może być w przyszłości przypisana nie tylko do Klienta, ale np. do Jednorazowego Leada z innej tabeli. Daje mi to ogromną elastyczność i zapobiega wstawianiu wielu pustych kolumn (Null) do bazy danych."
