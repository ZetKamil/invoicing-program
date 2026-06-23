# 🛡️ KROK 0: Introductie, Scope & Afsluiting (Gegarandeerd 100 Punten)

*Uwaga: Ten dokument łączy teraz 2 kryteria oceny: "Scope en projectplanning /50" oraz "Presentatie eigen project /50". Zawiera wszystko, co musisz powiedzieć PRZED pokazaniem dema (Deel 1) oraz PO pokazaniu dema (Deel 3).*

---

## DEEL 1: INTRODUCTIE & SCOPE (Mówisz to ZANIM odpalisz kod/aplikację)

Beste jury, voordat ik de applicatie in actie laat zien, wil ik eerst de context schetsen en toelichten hoe ik dit project planmatig heb aangepakt.

### 1. Probleemstelling & Behoefte
Vandaag de dag verliezen kleine en middelgrote transportbedrijven (KMO's) dagelijks uren aan handmatige administratie. Ze typen offertes over, factureren handmatig, en bellen eindeloos achter betalingen aan. Traditionele systemen zijn vaak niet veilig afgeschermd voor meerdere transporteurs op één platform en missen moderne betaalmogelijkheden voor de eindklant. Op deze pijnpunten speelt mijn project in.

### 2. Doel van het project & Doelgroep
Mijn doel was het ontwikkelen van een modern, schaalbaar B2B SaaS-platform genaamd **Master-Digit (Logi-Web PRO)**.
De **doelgroep** is tweeledig: 
Enerzijds de dispatchers en transporteurs die het systeem beheren (backend), en anderzijds hun eindklanten die veilig online betalen via een publiek portaal (frontend).

### 3. Belangrijkste functionaliteiten
De applicatie focust zich op de kern van het bedrijfsproces:
- Een strikte, veilige **Multi-Tenant architectuur** (elke vervoerder ziet enkel zijn eigen data).
- Het naadloos omzetten van een Lead, naar een Offerte, en met één klik naar een Factuur.
- **Dual-Output Billing:** Genereren van zowel een PDF als een Peppol-compliant UBL XML bestand.
- Een responsieve, vlotte SPA-ervaring (Single Page Application) dankzij **Filament v3 en Livewire**.
- Een geïntegreerde **Stripe betaalpoort** met asynchrone webhooks.

### 4. Wat wel en NIET binnen de opdracht valt (Scope)
**In Scope:** De volledige financiële flow, 100% data-isolatie en betalingsverwerking stonden centraal.
**Out of Scope:** Ik heb bewust beslist om zaken zoals live GPS-tracking van vrachtwagens buiten scope te houden. Mijn doel was om de architectuur (financiële berekeningen en security) eerst 100% waterdicht te maken, in plaats van onstabiele extra features toe te voegen.

### 5. Realistische planning, Mijlpalen & Realisatie
Om dit complexe project succesvol op te leveren, heb ik gewerkt met 5 duidelijke mijlpalen:
1. **Fundament & Isolatie:** Opzetten van de database met ULIDs en Global Scopes voor veiligheid.
2. **Core CRUD & UI:** Bouwen van het Filament CMS beheergedeelte.
3. **Financiële Logica:** Implementeren van de factuurconversie met absolute precisie (`decimal 12,2`).
4. **Stripe & Webhooks:** Koppelen van online betalingen en asynchrone background queues.
5. **Testing & Hardening:** Schrijven van 45 Pest integratietesten om de stabiliteit te garanderen.

Terugkijkend kan ik met trots zeggen dat **100% van de vastgelegde MVP succesvol is gerealiseerd**.

---

## DEEL 2: DE DEMO (Główna część techniczna)

*(W tym momencie mówisz: "Laten we nu kijken hoe dit er in de praktijk uitziet" i przechodzisz płynnie do pliku `02_skrypt_obrony_uproszczony.md` realizując Kroki od 1 do 7)*

---

## DEEL 3: REFLECTIE & AFSLUITING (Mówisz to NA SAM KONIEC, po pokazaniu kodu)

### 6. Reflectie op Moeilijkheden en Oplossingen
Tijdens de ontwikkeling liep ik natuurlijk tegen enkele technische uitdagingen aan. De grootste moeilijkheid was het balanceren van extreme databeveiliging met asynchrone processen. 
Bijvoorbeeld de integratie van Stripe: Stripe staat los van de ingelogde gebruikerssessie. Het was een zware uitdaging om de inkomende betalingsbevestiging veilig te verwerken zonder een datalek te creëren. Ik heb dit opgelost door een **Webhook-First Provisioning** patroon te schrijven. De data wordt lokaal versleuteld gecachet met een anonieme UUID, en enkel de webhook triggert de decodering. Om de factuur dan als 'betaald' te markeren, moest ik kortstondig de Global Scopes omzeilen met de `withoutGlobalScopes()` methode. Dit vereiste een zeer diep inzicht in de Laravel architectuur.

### 7. Afsluiting
Kortom, ik hoop dat ik met deze applicatie en demo heb bewezen dat ik niet zomaar een basis CRUD-applicatie heb gebouwd. Ik heb een modern, performant en architecturaal veilig SaaS-platform neergezet dat klaar is voor de B2B-markt. 

Bedankt voor uw tijd en aandacht. Ik beantwoord nu met plezier uw eventuele vragen.

*(Koniec! Czekasz na oklaski i ewentualne pytania z Jury)*
