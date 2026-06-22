EINDOPDRACHT
1. Algemene situering
Tijdens deze eindopdracht ontwikkel je individueel een volledige webapplicatie in Laravel.
Afhankelijk van je gekozen traject en je eerdere oefeningen werk je deze applicatie uit met
Laravel blade components, Livewire of met Filament. De eindopdracht vormt het sluitstuk
van de opleiding en moet aantonen dat je in staat bent om een realistische webtoepassing
zelfstandig te analyseren, op te bouwen en toe te lichten.
Je kiest een van de vier opgegeven opdrachten en werkt deze uit volgens de verwachtingen
van jouw traject.
De vier mogelijke domeinen zijn:
• E-commerce platform
• Ticketing platform
• Facturatieprogramma
• Warehouse Management System (WMS)
Hoewel de onderwerpen verschillen, worden alle opdrachten als gelijkwaardig beschouwd.
Elke opdracht vraagt een combinatie van datamodellering, backendlogica,
gebruikersinterface, validatie, beheerfunctionaliteiten en technische onderbouwing

3. Wat voor alle opdrachten verplicht is
Elke eindopdracht moet aan onderstaande algemene voorwaarden voldoen.
3.1 Werkende applicatie
Je applicatie moet effectief bruikbaar zijn. Dat betekent dat de belangrijkste flows volledig
doorlopen kunnen worden en dat de kernfunctionaliteiten werken zoals bedoeld.
3.2 Logische database-structuur
Je database moet correct ontworpen zijn. Tabellen, relaties en sleutels moeten logisch
gekozen zijn en passen bij de werking van de applicatie.
3.3 Correcte validatie
Formulieren moeten gevalideerd worden. Verkeerde of onvolledige invoer moet op een
gecontroleerde manier afgehandeld worden.
3.4 Verzorgde gebruikersinterface
De applicatie moet duidelijk en bruikbaar zijn. De nadruk ligt niet op grafische perfectie,
maar wel op overzicht, logica en gebruiksvriendelijkheid.
3.5 Beheergedeelte
Elke opdracht moet een duidelijke beheerkant bevatten. Het project mag dus niet enkel uit
publieke pagina’s bestaan.
3.6 Presentatie en verdediging
Je moet tijdens de presentatie niet alleen tonen dat de applicatie werkt, maar ook uitleg
kunnen geven over je keuzes, je databankstructuur, je logica en je technische aanpak

FACTURATIEPROGRAMMA
1. Doel van de opdracht
In deze opdracht ontwikkel je een zakelijke applicatie voor het beheren van klanten,
offertes en facturen. De gebruiker moet documenten kunnen opmaken, exporteren,
versturen en opvolgen.

3. Traject B, zonder stage
3.1 Multi-bedrijf of tenant-structuur
• Meerdere bedrijven op hetzelfde platform
• Data per bedrijf gescheiden
• Centrale admin
Je werkt een platform uit waarop meerdere bedrijven hun eigen facturatieomgeving
hebben.
3.2 Factuur locking en data-integriteit
• Verzonden facturen niet vrij wijzigbaar
• Veilige statusovergangen
• Bewust omgaan met definitieve documenten
Een factuur is geen vrijblijvend record. In traject B verwachten we dat je nadenkt over de
integriteit van zakelijke data.
3.3 Online betaling van facturen
• Integratie met Stripe of Mollie
• Factuurstatus automatisch aanpassen
Je breidt de applicatie uit zodat klanten facturen online kunnen betalen.
3.4 Webhooks en synchronisatie
• Externe betalingsupdates verwerken
• Logging
• Correct omgaan met fouten
Je zorgt voor een robuustere koppeling met de betaalprovider.
3.5 Automatische herinneringen
• Herinneringen voor openstaande facturen
• Scheduler of cron gebruiken
Je werkt ook tijdsgebonden logica uit zodat openstaande facturen automatisch opgevolgd
kunnen worden.
3.6 Uitgebreide rapportering
• Omzet per maand
• Omzet per klant
• Openstaande bedragen
De gebruiker moet diepgaander inzicht krijgen in de financiele situatie.
3.7 Queue en mailarchitectuur
• Mails via queue
• Herinneringsmails automatiseren
Hiermee toon je dat je professionele e-mailverwerking begrijpt.
3.8 Professionele architectuur
• Services
• Logging
• Onderhoudbare structuur
Complexe documentlogica en betaalverwerking moeten op een schaalbare manier
opgebouwd zijn.

4. In te dienen onderdelen
Elke cursist dient minstens volgende onderdelen in:
• De volledige broncode van het project
• Een export van de databank of de nodige migraties en seeders
• Een korte README met uitleg over installatie en gebruik
• Een korte toelichting van de gekozen opdracht en het uitgewerkte traject
• Eventuele testaccounts indien nodig voor demo of jury
De bedoeling is dat het project door de jury op een vlotte manier opgestart en getest kan
worden.
5. Presentatieverwachtingen
Tijdens de presentatie moet je minstens kunnen tonen:
• Wat het doel is van jouw applicatie
• Welke kernfunctionaliteiten je gebouwd hebt
• Hoe je databank is opgebouwd
• Welke keuzes je gemaakt hebt in je code
• Wat het verschil is tussen jouw basisfunctionaliteiten en eventuele verdiepingen
• Welke moeilijkheden je bent tegengekomen en hoe je die hebt opgelost
Een presentatie is dus niet enkel een klikdemo. We verwachten ook technische toelichting
en inzicht.
6. Algemene evaluatielogica
Alle opdrachten worden beoordeeld volgens dezelfde hoofdlijnen, zodat de opdrachten
gelijkwaardig blijven ondanks hun verschillend domein.
6.1 Functionele uitwerking
Werken de kernfunctionaliteiten correct en volledig?
6.2 Datamodel en logica
Zijn tabellen, relaties en processen logisch opgebouwd?
6.3 Beheer en gebruiksvriendelijkheid
Is de applicatie bruikbaar voor de eindgebruiker en de beheerder?
6.4 Technische kwaliteit
Is de code gestructureerd, gevalideerd en onderhoudbaar?


Verwachtingen eindopdracht Frontend en Backend voor jullie mapje die je dient in te geven bij de verdediging:

Voor de eindopdracht bouwen de cursisten een eigen gekozen project uit waarin ze aantonen dat ze de aangeleerde technieken zelfstandig, professioneel en doordacht kunnen toepassen. Het project moet niet alleen technisch werken, maar ook logisch opgebouwd, verzorgd gepresenteerd en verdedigbaar zijn tegenover een vakjury.
Dezelfde beoordelingsstructuur geldt voor zowel Frontend als Backend, telkens met een totaal van 400 punten.

1. Scope en projectplanning /50
De cursist moet aantonen dat het project vooraf duidelijk werd afgebakend en dat de uitvoering planmatig gebeurde.
Verwachtingen
De cursist levert een duidelijke projectscope aan met daarin:

het doel van het project;
de doelgroep of gebruiker van de applicatie;
de belangrijkste functionaliteiten;
wat wel en niet binnen de opdracht valt;
een realistische planning;
tussentijdse mijlpalen of oplevermomenten;
een overzicht van wat effectief gerealiseerd werd.
Professionele minimumverwachting
Een goede eindopdracht bevat geen vaag idee, maar een afgebakend project met duidelijke keuzes. De cursist moet kunnen uitleggen waarom bepaalde functionaliteiten gekozen zijn en waarom andere zaken eventueel buiten scope vielen.
Beoordeling
Er wordt gekeken naar:

volledigheid van de scope;
haalbaarheid van de planning;
mate waarin tussentijdse opleveringen behaald werden;
professionele opvolging van het project;
realistische inschatting van tijd en complexiteit;
duidelijke reflectie op wat wel of niet gelukt is.


2. Presentatie eigen project /50
De cursist presenteert het eigen project op een professionele manier aan de jury.
Verwachtingen
De presentatie bevat minimaal:

een korte introductie van het project;
het probleem of de behoefte waarop het project inspeelt;
een overzicht van de belangrijkste functionaliteiten;
visuele ondersteuning via slides, demo of screenshots;
uitleg over de technische keuzes;
een korte reflectie op moeilijkheden en oplossingen;
een duidelijke afsluiting.
Voor Frontend
Bij Frontend ligt de nadruk op:

visuele kwaliteit;
layout;
gebruiksvriendelijkheid;
responsive design;
interactie;
consistentie in stijl;
toegankelijkheid en leesbaarheid.
Voor Backend
Bij Backend ligt de nadruk op:

logische datastructuur;
correcte werking van functionaliteiten;
validatie;
security;
performantie;
beheerbaarheid;
correcte koppeling met de frontend of API.
Beoordeling
Er wordt gekeken naar:

professionele uitstraling;
duidelijke uitleg;
structuur van de presentatie;
kwaliteit van de demo;
technische onderbouwing;
verzorgd taalgebruik;
vermogen om het project overtuigend voor te stellen.


3. Neerslag eigen project /100
De cursist levert een schriftelijke neerslag of projectdocumentatie in waarin het project professioneel wordt toegelicht.
Verwachtingen
De neerslag bevat minimaal:
1. Projectomschrijving
titel van het project;
korte samenvatting;
doelstelling;
doelgroep;
probleemstelling of behoefte.
2. Scope
wat werd gebouwd;
wat bewust niet werd gebouwd;
belangrijkste functionaliteiten;
uitbreidingsmogelijkheden.
3. Analyse
functionele vereisten;
niet-functionele vereisten;
gebruikersrollen indien van toepassing;
user stories of use cases.
4. Technische uitwerking
gebruikte technologieën;
projectstructuur;
belangrijke bestanden of componenten;
databankstructuur indien van toepassing;
API’s of externe koppelingen indien gebruikt.
5. Ontwerp en UX
wireframes, screenshots of designkeuzes;
motivatie van kleuren, typografie en layout;
responsive gedrag;
toegankelijkheid.
6. Testing en kwaliteitscontrole
wat werd getest;
gekende fouten of beperkingen;
hoe bugs werden opgelost;
eventuele screenshots van tests.
7. Reflectie
wat goed ging;
wat moeilijk was;
wat de cursist geleerd heeft;
wat bij een volgende versie beter zou kunnen.
8. Bronnen en hulpmiddelen
gebruikte documentatie;
gebruikte AI-tools indien van toepassing;
gebruikte libraries, packages of frameworks;
correcte vermelding van externe code of templates.
Professionele minimumverwachting
De neerslag moet duidelijk genoeg zijn zodat een externe beoordelaar begrijpt wat het project doet, hoe het technisch is opgebouwd en welke keuzes de cursist gemaakt heeft.
De neerslag mag geen losse verzameling screenshots zijn. Het moet een gestructureerd document zijn met uitleg, onderbouwing en reflectie.

4. Verdediging eigen project /100
De cursist verdedigt het project mondeling voor een vakjury.
Verwachtingen
Tijdens de verdediging moet de cursist kunnen aantonen dat hij of zij het project zelf begrijpt en zelfstandig kan uitleggen.
De cursist moet kunnen antwoorden op vragen over:

projectkeuzes;
technische implementatie;
gebruikte code;
structuur van het project;
databank of componenten;
validatie en foutafhandeling;
beveiliging;
UX-keuzes;
beperkingen van het project;
mogelijke uitbreidingen.
Voor Frontend
De cursist moet onder andere kunnen uitleggen:

hoe de pagina’s zijn opgebouwd;
hoe responsive design werd toegepast;
waarom bepaalde layoutkeuzes gemaakt zijn;
hoe interacties werken;
hoe componenten of scripts georganiseerd zijn;
hoe de frontend gekoppeld is aan data of backendlogica indien van toepassing.
Voor Backend
De cursist moet onder andere kunnen uitleggen:

hoe de databank is opgebouwd;
welke relaties er bestaan;
hoe validatie werkt;
hoe CRUD-functionaliteit is opgebouwd;
hoe routes, controllers, Livewire-componenten of API’s werken;
hoe authenticatie of autorisatie is toegepast;
hoe fouten worden afgehandeld;
hoe data veilig wordt verwerkt.
Beoordeling
Er wordt gekeken naar:

technische kennis;
zelfstandigheid;
inzicht in de eigen code;
correct gebruik van vakterminologie;
vermogen om keuzes te verantwoorden;
probleemoplossend denken;
eerlijkheid over beperkingen;
professionele houding tegenover feedback.


5. Parate kennis /100
Naast het eigen project wordt ook de algemene kennis van de aangeleerde topics bevraagd via open vragen.
Verwachtingen
De cursist moet kunnen aantonen dat hij of zij de geziene leerstof begrijpt en niet enkel het eigen project van buiten kent.
Mogelijke topics Frontend
De cursist moet vragen kunnen beantwoorden over onder andere:

HTML-structuur;
semantische HTML;
CSS en layout;
responsive design;
Flexbox en Grid;
JavaScript-basisprincipes;
DOM-manipulatie;
componentgericht werken;
formulieren;
validatie aan frontendzijde;
UX/UI-principes;
toegankelijkheid;
performance;
SEO-basisprincipes;
gebruik van libraries of frameworks indien gezien.
Mogelijke topics Backend
De cursist moet vragen kunnen beantwoorden over onder andere:

routes;
controllers;
views;
models;
migrations;
seeders;
factories;
Eloquent-relaties;
CRUD;
validatie;
authenticatie;
autorisatie;
middleware;
databanknormalisatie;
security;
foutafhandeling;
API’s;
file uploads;
mailing;
deploymentbasis;
gebruik van packages of frameworks indien gezien.
Beoordeling
Er wordt gekeken naar:

correctheid van de antwoorden;
inzicht in de leerstof;
vermogen om voorbeelden te geven;
gebruik van juiste terminologie;
verbanden leggen tussen theorie en praktijk;
toepassen van kennis buiten het eigen project.


Algemene professionele vereisten
Elke eindopdracht moet voldoen aan een aantal basisverwachtingen.
Technische kwaliteit
Het project moet:

functioneel werken;
geen kritieke fouten bevatten;
logisch gestructureerd zijn;
leesbare code bevatten;
duidelijke naamgeving gebruiken;
geen overbodige of dode code bevatten;
foutmeldingen correct afhandelen;
data correct verwerken;
veilig omgaan met input van gebruikers.
Codekwaliteit
De code moet professioneel genoeg zijn om door iemand anders gelezen en begrepen te worden.
Er wordt verwacht dat:

bestanden logisch benoemd zijn;
functies, componenten of classes een duidelijke verantwoordelijkheid hebben;
herhaling zoveel mogelijk vermeden wordt;
comments alleen gebruikt worden waar ze nuttig zijn;
formatting consequent is;
de projectstructuur overzichtelijk blijft.
Design en gebruikservaring
Vooral bij Frontend, maar ook bij volledige projecten, wordt gekeken naar:

duidelijke navigatie;
consistente layout;
leesbare typografie;
correcte spacing;
responsive werking op mobiel, tablet en desktop;
duidelijke knoppen en acties;
begrijpelijke foutmeldingen;
professionele afwerking.
Documentatie
De cursist moet documentatie voorzien waarmee het project kan worden begrepen, geïnstalleerd en beoordeeld.
Minimaal verwacht:

korte installatie-instructies;
gebruikte technologieën;
login-gegevens indien nodig;
overzicht van functionaliteiten;
gekende beperkingen;
screenshots of demo-instructies;
bronvermelding.
AI-gebruik
AI-tools mogen gebruikt worden, maar de cursist blijft verantwoordelijk voor de inhoud, code en uitleg.
De cursist moet kunnen aantonen:

welke onderdelen eventueel met AI ondersteund werden;
dat hij of zij de gegenereerde code begrijpt;
dat de code aangepast werd aan het eigen project;
dat fouten of onlogische output gecontroleerd werden;
dat er geen volledige opdracht blind werd overgenomen zonder begrip.
Een project dat technisch werkt, maar niet verdedigd kan worden, wordt onvoldoende beschouwd.