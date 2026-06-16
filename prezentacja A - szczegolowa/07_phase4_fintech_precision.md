# Faza 4: Precyzja FinTech i Audyt Bezpieczeństwa (Phase 4)

**Co musisz wiedzieć z lotu ptaka:**
Faza 4 to moment, w którym aplikacja logistyczna staje się pełnoprawną maszyną finansową (FinTech). System musi być odporny na błędy matematyczne, złośliwe manipulacje formularzami w przeglądarce i zapewnić natychmiastowe (w czasie rzeczywistym) powiadomienia o nowych zleceniach bez zabijania serwera. Przeszliśmy na całkowitą precyzję ułamkową (BCMath), twarde podpisy kryptograficzne komponentów (Locked) oraz WebSockety (Reverb).

---

## 1. Absolutna Precyzja Finansowa: Architektura BCMath

### Zagrożenie: Błędy zaokrągleń (Floating-Point Drift)

W standardowym języku PHP, matematyka oparta na typie `float` (zmiennoprzecinkowym) jest podatna na odchylenia. Przykładowo, licząc 21% podatku z 3 sztuk po 14,90 € (`14.90 * 3 * 1.21`), procesor zamiast równego `54.09` wypluje `54.08699999...`.
Gdy faktura ma dziesiątki pozycji, te ułamki centów się kumulują. Skutek?
- **Odrzucenie przez bramkę płatniczą:** Stripe zgłasza błąd z powodu 1 centa różnicy między sumą pozycji a kwotą główną.
- **Odrzucenie przez urzędy skarbowe (Peppol):** Bramka europejskiego systemu e-faktur (UBL 2.1) restrykcyjnie przelicza podatki i odrzuca fakturę za najmniejszy błąd matematyczny.

### Zastosowane Rozwiązanie: BCMath (String-Based Math)

Usunęliśmy całkowicie zwykłą matematykę `float` z całego systemu finansowego:
1. Zamieniliśmy mnożenie (`*`) i dodawanie (`+`) na specjalistyczne funkcje **`bcmul()`** i **`bcadd()`**.
2. Operacje są teraz wykonywane na tekście (String), a ułamki ucinane do 2 miejsc po przecinku dopiero na samym końcu.

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `float` – Typ danych przechowujący ułamki (np. 14.90), który z powodów technologicznych w procesorze komputera nie jest w 100% precyzyjny.
> *   `BCMath` (np. `bcmul`) – Wbudowana w PHP biblioteka do matematyki o arbitralnej (absolutnej) precyzji, używająca tekstu zamiast liczb procesora.
>
> **Na chłopski rozum:** Liczenie ułamków na typie `float` to jak mierzenie cegieł tanią, miękką, gumową miarką. Kiedy budujesz dom, na 1000 cegieł pomylisz się o metr. `BCMath` to jak drogi laserowy miernik cyfrowy – traktuje liczby jak czysty tekst i dodaje je jak człowiek na kartce papieru, gwarantując precyzję co do grosza bez żadnych zakłóceń komputera.

---

## 2. Hardening e-Faktur Peppol (UBL 2.1 XML)

### Zagrożenie: Ciche awarie i łamanie standardów

Początkowy kod generowania e-Faktury w formacie XML miał problem: jeśli z jakiegoś powodu serwer nie mógł wygenerować tego ukrytego pliku urzędowego XML, cała funkcja "Wystaw Fakturę" pękała i klient nie otrzymywał nawet zwykłego, czytelnego pliku PDF.

### Zastosowane Rozwiązanie: Decoupling i Walidacja DOM

1. **Partial Success (Częściowy Sukces):** Oddzieliliśmy generowanie czytelnego pliku PDF od pliku XML (`try/catch`). Jeśli XML wybuchnie, klient i tak otrzyma poprawny PDF do rąk własnych, a błąd XML jest po cichu zapisywany w logach dla admina.
2. **Pre-Storage DOM Validation:** Wygenerowany XML zanim poleci na serwer urzędu skarbowego, jest najpierw walidowany wewnątrz serwera przez `DOMDocument`. 

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `try/catch` – Próba wykonania (omówiona wcześniej przy webhookach).
> *   `DOMDocument` – Narzędzie systemowe, które potrafi sprawdzić strukturę pliku XML/HTML przed jego wysłaniem.
>
> **Na chłopski rozum:** Oddzielenie PDF od XML (Decoupling) to danie priorytetu biznesowi. Jeśli "elektroniczna koperta" dla rządu (XML) zatnie się w niszczarce, nie blokujesz z tego powodu całego biznesu logistycznego – klient i tak dostaje wydruk do rąk (PDF). Z kolei walidacja `DOMDocument` to darmowy Nauczyciel Polskiego, który sprawdza gramatykę całego urzędowego pliku XML, zanim jeszcze wyślesz go do ministerstwa i narazisz się na wstyd z powodu braku przecinka (złego tagu HTML/XML).

---

## 3. Bezpieczeństwo Livewire: Blokada Podmiany Modeli

### Zagrożenie: Atak Hydration Tampering

Na publicznym portalu płatności dla Twoich klientów zmienna z numerem Faktury (Invoice) była całkowicie `publiczna`. Hacker mógł otworzyć Narzędzia Deweloperskie (F12) w przeglądarce, przechwycić kod JavaScript wysyłany do serwera Livewire, i **zmienić ID swojej faktury na ID faktury innej firmy**. W efekcie portal mógł wygenerować bramkę Stripe opłaconą dla kogoś innego!

### Zastosowane Rozwiązanie: Kryptograficzna kłódka `#[Locked]`

Zastąpiliśmy zwykłe przypisanie `public $invoice` super-nowoczesnym atrybutem bezpieczeństwa **`#[Locked] public string $invoiceId`**. 

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `public $invoice` – Właściwość komponentu dostępna i modyfikowalna zarówno przez serwer, jak i przeglądarkę (JavaScript).
> *   `#[Locked]` – Atrybut (magiczna naklejka) z Livewire 3/4. Podpisuje wartość właściwości niezłamywalnym certyfikatem kryptograficznym. 
>
> **Na chłopski rozum:** Zwykłe `public` na portalu to jak przyklejenie do komputera w sklepie ceny na zwykłej kartce papieru. Klient może skreślić "100 zł", napisać "1 zł" długopisem i zanieść do kasy. Dodanie `#[Locked]` to schowanie tej ceny do pancernej, szklanej gabloty nasączonej alarmem. Klient Ją widzi, ale jeśli tylko spróbuje dotknąć szkła (zmienić ID w przeglądarce), zamek krzyczy (Błąd Walidacji) i wyrzuca złodzieja ze sklepu.

---

## 4. Prawdziwy Czas Rzeczywisty: WebSockety i Laravel Reverb

### Zagrożenie: Polling Latency (Zamulenie serwera)

W branży logistycznej odpowiedź na zapytanie o transport z formularza na stronie musi być błyskawiczna (kto pierwszy da cenę, ten wygrywa). Stara technologia (`wire:poll`) działała tak, że komputery wszystkich dyspozytorów co 2 sekundy "pukały" do bazy danych pytając, czy jest nowa oferta. Przy 50 dyspozytorach zabija to serwer.

### Zastosowane Rozwiązanie: Architektura Event-Driven z Reverb

1. Wdrożyliśmy **Laravel Reverb** (własny serwer WebSocketów), rezygnując z płatnych pakietów jak Pusher.
2. Zastosowaliśmy **Prywatne Kanały** (`private-dispatcher.{bedrijfId}`). Informacja o nowym oknie transportowym w Holandii uderza tylko do dyspozytorów tej jednej firmy w ułamku sekundy, a nie wszystkich na serwerze.

> **Edukacja dla Ciebie (Pojęcia w kodzie):**
> *   `wire:poll` – Mechanizm Pętli Pytającej (HTTP Polling). Wymusza ciągłe wysyłanie żądań na serwer.
> *   `WebSocket` – Otwarte, stałe i bezpłatne "gniazdko" łączące przeglądarkę z serwerem, w którym nie trzeba odświeżać strony.
>
> **Na chłopski rozum:** `wire:poll` to jak dzieci na tylnym siedzeniu samochodu, które co 10 sekund krzyczą: "Daleko jeszcze? Daleko jeszcze? Jest nowa faktura?". Kierowca (Serwer) szybko dostaje szału. Architektura `WebSocket` (Reverb) to zmiana zasad: mówisz dzieciom "Cicho bądźcie, ja Was sam zawołam, jak dojedziemy na miejsce". Komputer milczy, odciąża bazę danych, a gdy na stronie pojawi się nowy formularz (Zdarzenie), serwer sam dzwoni do komputera pracownika i podrzuca mu baner powiadomienia w 1 milisekundę. Prawdziwa technologia Enterprise.
