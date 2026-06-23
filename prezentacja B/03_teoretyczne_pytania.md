# 🧠 Parate Kennis - Baza Pytań Teoretycznych (100 pkt)

Ta część egzaminu sprawdza Twoją ogólną wiedzę programistyczną. Nauczyciel zapyta Cię o teorię, niezależnie od Twojego projektu. Poniżej znajduje się lista najczęściej zadawanych pytań "z zaskoczenia" wraz z odpowiedziami napisanymi profesjonalnym, ale łatwym do zrozumienia językiem.

---

## 🎨 FRONTEND

### 1. Jaka jest różnica między Flexbox a CSS Grid?
**Odpowiedź:** 
"Flexbox służy do układów jednowymiarowych (1D) – idealnie sprawdza się do układania elementów w jednym rzędzie lub w jednej kolumnie, na przykład do nawigacji (navbar) lub paska narzędzi. CSS Grid służy do układów dwuwymiarowych (2D) – pozwala kontrolować zarówno wiersze, jak i kolumny jednocześnie. Wybieram go do budowania kompletnej makiety strony (page layout), gdzie muszę kontrolować ułożenie głównych sekcji."

### 2. Czym jest semantyczny HTML i dlaczego jest ważny dla SEO i Dostępności (Accessibility)?
**Odpowiedź:** 
"Semantyczny HTML polega na używaniu tagów, które opisują swoje znaczenie (np. `<header>`, `<nav>`, `<article>`, `<aside>`, `<main>`, `<footer>` zamiast samych `<div>`). Jest to kluczowe z dwóch powodów: SEO i Toegankelijkheid. Robot wyszukiwarki (np. Googlebot) lepiej rozumie strukturę i hierarchię ważności treści na stronie. Ponadto czytniki ekranu (screen readers) dla osób niedowidzących potrafią dzięki temu poprawnie nawigować po dokumencie, co jest fundamentem standardów WCAG."

### 3. Jak działa asynchroniczność w JavaScript i czym różni się manipulacja DOM od Livewire?
**Odpowiedź:** 
"W czystym JS asynchroniczność opiera się na architekturze Event Loop, instrukcjach `async/await` oraz `Promises`. Wykonujemy zapytania do API (np. przez `fetch()`) bez blokowania głównego wątku przeglądarki. DOM (Document Object Model) to drzewo struktury HTML, a my za pomocą JS możemy je zmieniać po załadowaniu. Z kolei Livewire automatyzuje ten proces. Przechwytuje akcje użytkownika, wysyła pod spodem szybkie zapytanie AJAX do serwera, a po przetworzeniu kodu w PHP, aktualizuje zmieniony kod HTML bezpośrednio w drzewie DOM przy użyciu inteligentnego algorytmu 'DOM diffing'."

### 4. Gdzie i jak przeprowadzasz walidację po stronie frontendu?
**Odpowiedź:** 
"Na frontendzie dodaję atrybuty takie jak `required`, `type="email"` czy `maxlength` w formularzach HTML. Daje to użytkownikowi natychmiastową odpowiedź zanim wyśle żądanie (tzw. Client-Side Validation). Jednak zawsze pamiętam, że walidacja frontendowa służy tylko UX (User Experience), a prawdziwe bezpieczeństwo zapewnia dopiero walidacja na Backendzie, bo HTML łatwo zmodyfikować w narzędziach deweloperskich."

---

## ⚙️ BACKEND (Laravel / Architektura)

### 5. Jaka jest różnica między Uwierzytelnianiem (Authentication) a Autoryzacją (Authorization)?
**Odpowiedź:** 
"Uwierzytelnianie (Authentication - AuthN) odpowiada na pytanie *'Kim jesteś?'* (np. sprawdzanie loginu i hasła). Autoryzacja (Authorization - AuthZ) odpowiada na pytanie *'Co wolno Ci zrobić?'* (np. czy jako zalogowany użytkownik masz uprawnienia administratora do skasowania faktury, do czego służą tzw. Policies w Laravelu)."

### 6. Do czego służy Middleware w aplikacjach webowych?
**Odpowiedź:** 
"Middleware to warstwa pośrednicząca (filtr) między żądaniem HTTP wysłanym przez użytkownika a kontrolerem. Doskonałym przykładem jest autentykacja. Middleware sprawdza, czy użytkownik posiada aktywną sesję. Jeśli nie – wykonuje przekierowanie (redirect) do strony logowania i przerywa cykl żądania, chroniąc w ten sposób zasoby kontrolera przed niepowołanym dostępem."

### 7. Jaka jest różnica między Migracją, Seederem a Factory?
**Odpowiedź:** 
* **Migracje** (Migrations) budują strukturę bazy danych (tworzą tabele i kolumny, np. `users`, `varchar`). To tzw. "kontrola wersji dla bazy".
* **Factories** (Fabryki) to "przepisy" na generowanie fałszywych modeli (definiują, że kolumna `name` ma dostać fałszywe imię, a `email` fałszywy adres e-mail).
* **Seedery** (Siewniki) to skrypty wykonawcze, które wywołują Factories, aby faktycznie wstawić wygenerowane rekordy testowe do bazy danych poleceniem `db:seed`.

### 8. Co to jest Normalizacja Bazy Danych i czym różnią się relacje Eloquent?
**Odpowiedź:** 
"To proces projektowania bazy danych w celu eliminacji redundancji i zapewnienia spójności (np. poprzez formy 1NF, 2NF, 3NF). Na przykład: zamiast wpisywać nazwę i adres klienta na każdej pojedynczej fakturze jako tekst, przechowuję klienta w tabeli `Customers` z unikalnym ID, a na fakturze zapisuję tylko referencyjny `customer_id` (Klucz Obcy). 
W systemie ORM Eloquent, relacja `HasMany` (jeden do wielu) oznacza, że jeden model posiada wiele modeli zależnych (np. jedno `Bedrijf` ma wiele `Invoices`). Z perspektywy tabeli podrzędnej, deklarujemy `BelongsTo`, co oznacza obecność klucza obcego wskazującego na rekord nadrzędny."

### 9. Omów główne zagrożenia Security i jak się przed nimi bronisz.
**Odpowiedź:** 
Laravel domyślnie chroni mnie przed wieloma atakami, ale o niektóre muszę dbać sam:
* **SQL Injection:** Atak przez wstrzyknięcie złośliwego kodu SQL. *Obrona:* Eloquent korzysta z PDO i "prepared statements". Zapytania SQL są oddzielane od zmiennych tekstowych.
* **XSS (Cross-Site Scripting):** Wstrzyknięcie skryptu JS np. w opis faktury. *Obrona:* Szablony Blade (`{{ $text }}`) używają `htmlspecialchars`, co zamienia skrypty na niegroźny tekst.
* **CSRF (Cross-Site Request Forgery):** Wymuszenie na zalogowanym użytkowniku wysłania niechcianego żądania z innej strony. *Obrona:* Laravel wymusza unikalne, ukryte tokeny CSRF dla żądań POST.
* **IDOR (Insecure Direct Object Reference):** Próba podglądania cudzych rekordów przez zmianę ID w adresie URL (np. `/invoices/5`). *Obrona:* Rozwiązałem to sam, używając Eloquent Global Scopes (`BedrijfScope`), co automatycznie izoluje zapytania na poziomie bazy danych.

### 10. Architektura MVC. Czym jest Model, View i Controller?
**Odpowiedź:** 
"To wzorzec projektowy oddzielający logikę aplikacji od jej prezentacji:
* **Model:** Zarządza danymi i regułami biznesowymi (np. pobieranie faktur z bazy przez Eloquent).
* **View (Widok):** To, co widzi użytkownik, interfejs graficzny (pliki `.blade.php`).
* **Controller:** Centrala. Odbiera żądanie HTTP, prosi Model o dane i przekazuje te dane do Widoku."

---

## 🧹 OGÓLNE (Code Quality & General)

### 11. Czym jest zasada DRY oraz Clean Code (SRP) i jak je zastosowałeś?
**Odpowiedź:** 
"DRY oznacza Don't Repeat Yourself. Zamiast powtarzać ten sam warunek sprawdzania firmy w 10 kontrolerach, wydzieliłem tę logikę do jednego Traitu `HasBedrijf.php`. Kod jest czysty, centralnie zarządzany i łatwy w utrzymaniu.
Z kolei dbając o Clean Code, stosuję zasadę SRP (Single Responsibility Principle). Wszystkie moje klasy akcji (np. `CreateInvoiceFromQuoteAction`) mają jedną, wąską odpowiedzialność, a nazwy plików od razu mówią, co dany kod robi."
