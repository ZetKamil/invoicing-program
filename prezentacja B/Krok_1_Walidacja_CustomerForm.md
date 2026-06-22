# 🧠 KROK 1: Tworzenie Klienta (Walidacja i Bezpieczeństwo)

**Plik:** `app/Filament/Resources/Customers/Schemas/CustomerForm.php`

## O czym jest ten krok?
Kiedy pokazujesz Jury dodawanie nowego klienta (Klant aanmaken), najważniejsze co musisz im udowodnić, to że Twój system jest **odporny na błędne dane** (Data Integrity). 

## Co musisz wytłumaczyć z kodu?

### 1. Metoda `->required()`
Gdy użytkownik spróbuje wysłać pusty formularz, system tego nie przepuści.
- **Jak to działa pod spodem:** Laravel zatrzymuje żądanie HTTP na poziomie kontrolera, zanim jeszcze spróbuje zapisać cokolwiek do bazy danych. Chroni to bazę przed błędami typu SQL `NOT NULL Constraint Violation`.

### 2. Metoda `->email()`
Używasz jej przy polu adresu e-mail.
- **Jak to działa pod spodem:** Ta funkcja używa silnika wyrażeń regularnych (Regex). Analizuje string pod kątem wzorca (np. czy zawiera `@` oraz domenę `.com` / `.be`). Zapobiega to sytuacjom, gdzie dyspozytor wpisałby numer telefonu w pole e-mail, co później wywaliłoby błąd systemu przy próbie wysyłki oferty pocztą.

### 3. Zabezpieczenie CSRF
Formularz jest chroniony przed atakami Cross-Site Request Forgery.
- **Wyjaśnienie dla Jury:** "Za każdym razem, gdy użytkownik ładuje formularz, serwer generuje ukryty token kryptograficzny. Przy wysyłaniu formularza, token ten wraca do serwera. Jeśli haker spróbowałby spreparować ten formularz na innej stronie internetowej, nie miałby dostępu do tego tokena, a serwer odrzuciłby takie żądanie kodem 419 (Page Expired)."
