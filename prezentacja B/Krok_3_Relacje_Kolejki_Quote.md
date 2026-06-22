# 🧠 KROK 3: Tworzenie Wyceny (Relacje Bazy Danych i Asynchroniczna Kolejka E-mail)

**Pliki:** `app/Models/Quote.php` oraz Twój otwarty terminal `php artisan queue:work`

## O czym jest ten krok?
Pokazujesz, że nie używasz przestarzałych zapytań SQL łączących tabele ręcznie (JOIN), tylko nowoczesnego mapowania obiektowo-relacyjnego (ORM). Dodatkowo zamykasz usta Jury pokazując w 100% zaimplementowany wymóg "Asynchronicznego wysyłania e-maili" (Queue).

## Co musisz wytłumaczyć z kodu?

### 1. Relacje Eloquent ORM
W pliku `Quote.php` pokazujesz metody relacyjne.
- **`BelongsTo` (Należy Do):** Każda wycena jest przypisana do dokładnie jednego Klienta. Zamiast pisać `SELECT * FROM customers WHERE id = ...`, używasz prostej strzałki w obiekcie `$quote->customer->name`.
- **`HasMany` (Posiada Wiele):** Wycena może składać się z wielu pozycji (wiele linii na dokumencie). Laravel sam załatwia klucze obce pod maską. To dowód, że potrafisz programować obiektowo w nowoczesnych frameworkach.

### 2. Kolejkowanie E-maili (Requirement 3.7)
Kiedy klikasz przycisk w panelu, żeby wysłać Ofertę do klienta.
- **Tłumaczenie dla Jury:** "Wysyłka maila na zewnętrzny serwer SMTP (np. Gmail czy Mailtrap) zajmuje serwerowi około 1-2 sekund. Zamiast zmuszać przeglądarkę do zawieszenia się i czekania na tę odpowiedź, użyłem mechanizmu kolejek (Queue)."
- **Jak to działa pod spodem:** Zamiast uruchamiać klasę Mail bezpośrednio, system wkłada tzw. 'Zadanie' (Job) do specjalnej tabeli w bazie danych (jobs). Twoja strona od razu się przeładowuje z sukcesem. W tle działa cały czas osobny proces – "Worker" (pokazujesz go w terminalu), który zauważa nowy wiersz w bazie, pobiera go i wysyła e-mail cichutko, nie przeszkadzając użytkownikowi w pracy. To wymóg systemów klasy korporacyjnej.
