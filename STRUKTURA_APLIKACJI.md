# Architektura i Działanie Systemu TransDigit

Witaj w szczegółowym omówieniu architektury Twojej aplikacji. Jako senior programista, przygotowałem dla Ciebie analizę "pod maską", która wyjaśnia, jak poszczególne elementy systemu współpracują ze sobą, tworząc nowoczesną platformę SaaS dla branży logistycznej.

---

## 1. Fundament Technologiczny (Tech Stack)
Aplikacja oparta jest o najnowocześniejsze standardy ekosystemu PHP w 2024/2025 roku:
- **Framework**: Laravel 11/13 (najwyższa wydajność i bezpieczeństwo).
- **Frontend Panelu Admina**: Filament v3 (oparty na TALL stack: Tailwind, Alpine.js, Laravel, Livewire).
- **Publiczny UI**: Livewire 4 + Flux UI (reaktywny frontend bez konieczności używania ciężkich frameworków JS jak React).
- **Identyfikatory**: ULID (Universally Unique Lexicographically Sortable Identifier) zamiast standardowych ID, co zapewnia bezpieczeństwo i wysoką wydajność indeksowania.

---

## 2. Multi-tenancy (Wielodostępność)
To serce systemu. Aplikacja została zaprojektowana tak, aby wiele firm (Bedrijfów) mogło korzystać z niej niezależnie, mając pewność izolacji swoich danych.

- **Mechanizm Izolacji**: Wykorzystujemy `Global Scopes` w Eloquent.
- **Trait `HasBedrijf`**: Każdy model (np. `Lead`, `Invoice`, `Quote`), który zawiera ten trait, automatycznie odfiltrowuje dane tak, aby użytkownik widział tylko rekordy swojej firmy (`bedrijf_id`).
- **Bezpieczeństwo**: Nawet jeśli ktoś spróbuje zgadnąć ID faktury innej firmy, system zwróci błąd 404 dzięki globalnemu zakresowi (Global Scope).

---

## 3. Kluczowe Moduły i Modele

### A. Moduł Sprzedażowy (CRM & Pipeline)
1. **Leads (Leady)**: Przechwytują dane z formularza na stronie głównej (`PackageInquiryForm`).
2. **Quotes (Oferty)**: Proces przekształcania Leada w konkretną ofertę handlową. Wykorzystujemy tu klasę `CreateQuoteAction`, która hermetyzuje całą logikę biznesową (obliczanie dat, generowanie numerów ofert).

### B. Moduł Finansowy (Billing Engine)
1. **Products (Produkty)**: Katalog usług transportowych.
2. **Invoices (Faktury)**: System generowania faktur zgodnych ze standardem **Peppol** (UBL XML), co pozwala na integrację z systemami administracji publicznej w UE.
3. **Observers**: `InvoiceItemObserver` automatycznie przelicza sumy faktury, gdy dodajesz do niej pozycje.

### C. Moduł Komunikacji i CMS
1. **Communications**: Polimorficzna relacja (`MorphTo`), która pozwala logować e-maile i notatki zarówno pod ofertami, jak i pod fakturami w jednej tabeli.
2. **CMS**: Możliwość prowadzenia bloga/aktualności przez każdego Bedrijf z osobna (`Posts`, `Categories`).

---

## 4. Przepływ Danych (Data Flow)

Oto jak "rozmawiają" ze sobą elementy systemu:

1. **Wejście**: Klient wypełnia formularz na stronie głównej (`welcome.blade.php`).
2. **Przetwarzanie**: Komponent Livewire wywołuje akcję `CreateLeadAction`.
3. **Baza Danych**: Tworzony jest rekord `Lead` z przypisanym `bedrijf_id` agencji (tzw. "dogfooding").
4. **Dashboard**: Pracownik loguje się do panelu Filament, widzi nowego Leada i jednym kliknięciem (poprzez `CreateQuoteAction`) generuje Ofertę.
5. **Finalizacja**: Po akceptacji oferty, system generuje Fakturę, tworzy plik PDF oraz plik XML (UBL) do e-fakturowania.

---

## 5. Logika Biznesowa (Gdzie szukać kodu?)

- **Widoki i Formularze**: `app/Livewire/` oraz `resources/views/`.
- **Panel Admina**: `app/Filament/Resources/` – tutaj definiujemy, jak wyglądają tabele i formularze w dashboardzie.
- **Logika "Ciężka"**: `app/Actions/` – to tutaj siedzi mózg aplikacji. Nie trzymamy logiki w kontrolerach, tylko w dedykowanych akcjach (Single Responsibility Principle).
- **Automatyzacja**: `app/Observers/` – rzeczy, które dzieją się "same" w tle przy zapisie modeli.
- **Bezpieczeństwo**: `app/Policies/` – tutaj sprawdzamy, czy dany użytkownik ma prawo edytować konkretny rekord.

---

## Podsumowanie "Seniora"
Ta aplikacja to nie jest zwykły "skrypt w PHP". To pełnoprawna platforma **Enterprise-ready**. Dzięki izolacji danych na poziomie bazy (Scopes), polimorficznym relacjom i architekturze opartej o akcje, system jest niezwykle łatwy w rozbudowie. Możesz dodać nowy moduł (np. "Zarządzanie Kierowcami") w kilka godzin, kopiując wzorce z istniejących modułów.

> [!TIP]
> Jeśli chcesz dodać nową funkcjonalność, zawsze zacznij od stworzenia Modelu z traitem `HasBedrijf`, dodaj Migrację z `ulid` oraz stwórz `Action` dla logiki biznesowej. To utrzyma kod czystym i skalowalnym.
