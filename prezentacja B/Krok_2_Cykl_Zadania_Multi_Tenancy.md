# 🧠 KROK 2: Cykl Żądania i Architektura Multi-Tenancy (Middleware, Trait, Global Scope)

**Pliki do omówienia:** `app/Traits/HasBedrijf.php` oraz `app/Models/Scopes/BedrijfScope.php`

## O czym jest ten krok?
Jako twórca systemu na zaliczenie przed Vakjury, musisz udowodnić zrozumienie wielowarstwowości aplikacji i przepływu danych. Aplikacja nie działa magicznie – jest zbiorem warstw: HTTP, Middleware, Aplikacji (Kontrolery), ORM (Modele) i Bazy Danych.

Ten krok tłumaczy, w jaki sposób Twoja aplikacja bezpiecznie radzi sobie z obsługą wielu firm naraz (Multi-Tenancy) bez powtarzania kodu.

## Co musisz wytłumaczyć z architektury (dla Vakjury):

### 1. Warstwa HTTP i Middleware (Skąd mamy Auth::user?)
Zanim model dotknie bazy danych, musimy wiedzieć, *kto* i w imieniu *jakiej firmy* wykonuje akcję.
- **Tłumaczenie dla Jury:** "Wszystko zaczyna się od warstwy HTTP. Zanim żądanie dotrze do logiki biznesowej, przechodzi przez Middleware autentykacji (auth). To tam Laravel weryfikuje zaszyfrowane ciastko sesyjne i ładuje obiekt użytkownika do pamięci. Od tej pory bezpiecznie wiemy, jakie `bedrijf_id` należy użyć."

### 2. Warstwa ORM - Trait i Model Events (Faza Zapisu)
Nie chcemy w każdym kontrolerze pisać `$customer->bedrijf_id = Auth::user()->bedrijf_id`. Szanujemy zasadę DRY.
- **Tłumaczenie dla Jury:** "Stworzyłem Trait `HasBedrijf`, co pozwala mi łatwo reużywać kod we wszystkich modelach. W jego metodzie statycznej `bootHasBedrijf()` nasłuchuję na zdarzenie (Event) `creating`. Zanim ORM wygeneruje ostateczny SQL INSERT, automatycznie 'chwyta' on `bedrijf_id` z pamięci i przypisuje do nowego rekordu."

### 3. Warstwa ORM - Global Scopes (Faza Odczytu)
System musi być absolutnie szczelny. Firma A nie może zobaczyć faktur firmy B, nawet jeśli użytkownik spróbuje odgadnąć ID w adresie URL (Atak IDOR).
- **Tłumaczenie dla Jury:** "W tym samym traicie rejestruję `BedrijfScope`. Z punktu widzenia odczytu danych, jest to automatyczny wstrzykiwacz klauzuli `WHERE`. Kiedy jakikolwiek proces pobiera dane poprzez Query Buildera, Scope modyfikuje zapytanie. Wyciek faktur między firmami jest niemożliwy, a wszystkie kontrolery pozostają czyste i wolne od redundantnych warunków WHERE."
