<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TransDigit Master — Logi-Web PRO</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,400&display=swap" rel="stylesheet">

    <!-- External Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/css/frontend.css', 'resources/js/app.js'])
    @fluxStyles
</head>

<body>
    <livewire:public.package-inquiry-form />

    <div>
        <!-- ===================== NAVBAR ===================== -->
        <nav id="navbar">
            <div class="nav-inner">
                <div class="nav-logo">TransDigit<em>.</em></div>
                <ul class="nav-links">
                    <li><a href="#services">Diensten</a></li>
                    <li><a href="#hoewerkhet">Hoe het werkt</a></li>
                    <li><a href="#smartquote">Smart Quote</a></li>
                    <li><a href="#about">Over ons</a></li>
                    <li><a href="#blog">Blog</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
                <button class="nav-cta" onclick="document.getElementById('audit').scrollIntoView({behavior:'smooth'})">
                    Gratis audit →
                </button>
            </div>
        </nav>

        <!-- ===================== HERO ===================== -->
        <section id="hero">
            <div class="hero-grid"></div>
            <div class="hero-glow-1"></div>
            <div class="hero-glow-2"></div>
            <div class="container">
                <div class="hero-content">
                    <div class="hero-badge" id="hb">
                        <span class="badge-dot"></span>
                        Logi-Web PRO — West-Vlaanderen
                    </div>
                    <h1 class="hero-h1" id="hh">
                        Uw transport&shy;bedrijf.<br>
                        Digitaal.<br>
                        <span class="accent">Zonder chaos.</span>
                    </h1>
                    <p class="hero-sub" id="hs">
                        Ultrasnelle websites met een ingebouwd Smart Quote-systeem voor Vlaamse transporteurs. Uw dispatcher
                        ontvangt enkel nog volledige, gestructureerde offerteaanvragen.
                    </p>
                    <div class="hero-btns" id="hbtns">
                        <button class="btn-primary"
                            onclick="document.getElementById('audit').scrollIntoView({behavior:'smooth'})">
                            🔍 Gratis AI-audit aanvragen
                        </button>
                        <button class="btn-outline"
                            onclick="document.getElementById('services').scrollIntoView({behavior:'smooth'})">
                            Bekijk pakketten
                        </button>
                    </div>
                    <div class="hero-stats" id="hstats">
                        <div class="stat-item">
                            <div class="stat-num" id="sc1">0<span>+</span></div>
                            <div class="stat-label">actieve klanten</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-num" id="sc2">0<span> dagnen</span></div>
                            <div class="stat-label">gemiddeld live</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-num" id="sc3">0<span>%</span></div>
                            <div class="stat-label">meer kwalitatieve leads</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===================== PROBLEMS ===================== -->
        <section id="problems">
            <div class="container">
                <p class="section-tag">Het probleem</p>
                <h2 class="section-title">Drie pijnpunten die elke<br>Vlaamse transporteur kent</h2>
                <p class="section-sub">We zijn geen gewoon webbureau. We kennen het difference tussen ADR, koeltransport en
                    containerlogistiek — en dat zie je in onze software.</p>
                <div class="problems-grid">
                    <div class="prob-card">
                        <div class="prob-icon">📞</div>
                        <div class="prob-title">Eindeloos bellen voor incomplete offertes</div>
                        <p class="prob-text">Klanten bellen zonder afmetingen, gewicht of postcodes. Uw dispatcher verliest
                            dagelijks uren aan nutteloze heen-en-weerberichten en incomplete mails.</p>
                    </div>
                    <div class="prob-card">
                        <div class="prob-icon">📄</div>
                        <div class="prob-title">E-facturering verplicht vanaf 2026</div>
                        <p class="prob-text">De wetgeving klopt aan de deur. Grote ERP-systemen zoals SAP zijn onbetaalbaar
                            voor KMO's. Wij bieden een schaalbaar alternatief dat meegroeit met uw bedrijf.</p>
                    </div>
                    <div class="prob-card">
                        <div class="prob-icon">🌐</div>
                        <div class="prob-title">Verouderde website, nul B2B-leads</div>
                        <p class="prob-text">90% van de lokale transporteurs heeft een digitale visitekaart die geen enkel
                            kwalitatief contact oplevert. Dat is gemiste omzet, elke dag opnieuw.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===================== SERVICES / PACKAGES ===================== -->
        <section id="services">
            <div class="container">
                <p class="section-tag">Onze pakketten</p>
                <h2 class="section-title">Transparante prijzen.<br>Geen verrassingen.</h2>
                <p class="section-sub">Elk pakket bevat hosting, frontend én een Mini-CRM dashboard. Eenmalig + kleine
                    maandelijkse abonnementskost.</p>
                <div class="packages-grid">
                    <!-- START -->
                    <div class="pkg-card">
                        <div class="pkg-tier">Start</div>
                        <div class="pkg-price"><sup>€</sup>850</div>
                        <div class="pkg-recur">eenmalig + €49/maand</div>
                        <div class="pkg-divider"></div>
                        <ul class="pkg-features">
                            <li><span class="feat-check">✓</span> Professionele one-pager</li>
                            <li><span class="feat-check">✓</span> Basis offerteformulier</li>
                            <li><span class="feat-check">✓</span> SEO-optimalisatie</li>
                            <li><span class="feat-check">✓</span> Google My Business setup</li>
                            <li><span class="feat-check">✓</span> Hosting inbegrepen</li>
                            <li><span class="feat-check">✓</span> SSL-certificaat</li>
                        </ul>
                        <button class="pkg-btn"
                            onclick="window.Livewire.find(document.querySelector('[wire\\:id]').id).call('open', 'Start')">Kies
                            Start</button>
                    </div>
                    <!-- PRO -->
                    <div class="pkg-card popular">
                        <div class="popular-label">Meest gekozen</div>
                        <div class="pkg-tier">Pro Transport</div>
                        <div class="pkg-price"><sup>€</sup>2.450</div>
                        <div class="pkg-recur">eenmalig + €79/maand</div>
                        <div class="pkg-divider"></div>
                        <ul class="pkg-features">
                            <li><span class="feat-check">✓</span> 5 pagina's op maat</li>
                            <li><span class="feat-check">✓</span> Smart Quote System</li>
                            <li><span class="feat-check">✓</span> Mini-CRM dashboard</li>
                            <li><span class="feat-check">✓</span> Google My Business + reviews</li>
                            <li><span class="feat-check">✓</span> Live in 7 werkdagen</li>
                            <li><span class="feat-check">✓</span> Kwartaalrapportage</li>
                            <li><span class="feat-check">✓</span> E-mail notificaties</li>
                        </ul>
                        <button class="pkg-btn"
                            onclick="window.Livewire.find(document.querySelector('[wire\\:id]').id).call('open', 'Pro Transport')">Kies Pro
                            Transport</button>
                    </div>
                    <!-- ENTERPRISE -->
                    <div class="pkg-card">
                        <div class="pkg-tier">Enterprise</div>
                        <div class="pkg-price" style="font-size:32px">Prijs op aanvraag</div>
                        <div class="pkg-recur">Op maat gemaakt traject</div>
                        <div class="pkg-divider"></div>
                        <ul class="pkg-features">
                            <li><span class="feat-check">✓</span> Volledige procesautomatisering</li>
                            <li><span class="feat-check">✓</span> OCR voor opdrachtenlezing</li>
                            <li><span class="feat-check">✓</span> E-facturatiemodule (2026-klaar)</li>
                            <li><span class="feat-check">✓</span> Chauffeursbeheer</li>
                            <li><span class="feat-check">✓</span> Meewerken aan roadmap</li>
                            <li><span class="feat-check">✓</span> Prioriteits-support</li>
                            <li><span class="feat-check">✓</span> TMS-uitbreiding mogelijk</li>
                        </ul>
                        <button class="pkg-btn"
                            onclick="window.Livewire.find(document.querySelector('[wire\\:id]').id).call('open', 'Enterprise')">Vraag offerte
                            aan</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===================== HOW IT WORKS ===================== -->
        <section id="hoewerkhet">
            <div class="container">
                <p class="section-tag">Hoe het werkt</p>
                <h2 class="section-title">Van audit tot live —<br>in 7 werkdagen.</h2>
                <div class="steps-grid">
                    <div class="step">
                        <div class="step-num">1</div>
                        <div class="step-title">Gratis AI-audit</div>
                        <p class="step-text">Onze AI analyseert uw huidige website op snelheid, UX en conversie. U ontvangt
                            een concreet rapport binnen 24 uur.</p>
                    </div>
                    <div class="step">
                        <div class="step-num">2</div>
                        <div class="step-title">Intakegesprek</div>
                        <p class="step-text">30 minuten. Geen verkoopspraatjes. We luisteren naar uw bedrijf en stellen het
                            juiste pakket voor.</p>
                    </div>
                    <div class="step">
                        <div class="step-num">3</div>
                        <div class="step-title">Bouw & configuratie</div>
                        <p class="step-text">Wij bouwen uw site en Mini-CRM. U levert de materialen aan op dag 1, wij doen
                            de rest.</p>
                    </div>
                    <div class="step">
                        <div class="step-num">4</div>
                        <div class="step-title">Live & schaalbaar</div>
                        <p class="step-text">Dag 7: uw site staat live. Leads stromen gestructureerd binnen. Upsell-modules
                            groeien mee op uw tempo.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===================== TESTIMONIALS ===================== -->
        <section id="testimonials">
            <div class="container">
                <p class="section-tag">Klanten aan het woord</p>
                <h2 class="section-title">Resultaten die spreken.</h2>
                <div class="testi-grid">
                    <div class="testi-card">
                        <div class="stars">★★★★★</div>
                        <p class="testi-quote">Onze dispatcher ontvangt nu alleen nog volledige offerteaanvragen. Het
                            scheelt ons minstens 2 uur per dag aan telefoontjes. Eindelijk rust op de planning.</p>
                        <div class="testi-author">
                            <div class="author-av">WD</div>
                            <div>
                                <div class="author-name">Wouter Desmet</div>
                                <div class="author-role">Zaakvoerder — Desmet Transport, Roeselare</div>
                            </div>
                        </div>
                    </div>
                    <div class="testi-card">
                        <div class="stars">★★★★★</div>
                        <p class="testi-quote">In 7 dagen een professionele site én een systeem waarmee mijn team leads
                            opvolgt. Ik had niet verwacht dat dit zo snel en vlot kon verlopen. Sterk werk.</p>
                        <div class="testi-author">
                            <div class="author-av">SM</div>
                            <div>
                                <div class="author-name">Sarah Maes</div>
                                <div class="author-role">Operations Manager — Maes Logistics, Kortrijk</div>
                            </div>
                        </div>
                    </div>
                    <div class="testi-card">
                        <div class="stars">★★★★★</div>
                        <p class="testi-quote">We verloren klanten aan concurrenten met een betere online aanwezigheid. Nu
                            scoren we als eerste in Google voor koeltransport in de regio. Meer kan ik niet vragen.</p>
                        <div class="testi-author">
                            <div class="author-av">JV</div>
                            <div>
                                <div class="author-name">Jan Vandevelde</div>
                                <div class="author-role">Directeur — Vandevelde Koeltransport, Izegem</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===================== STATS BAR ===================== -->
        <div id="statsbar">
            <div class="container">
                <div class="stats-row">
                    <div class="sb-item">
                        <div class="sb-num">7<span> dagen</span></div>
                        <div class="sb-label">Gemiddelde doorlooptijd</div>
                    </div>
                    <div class="sb-item">
                        <div class="sb-num">€100<span>+/u</span></div>
                        <div class="sb-label">Effectief uurloon</div>
                    </div>
                    <div class="sb-item">
                        <div class="sb-num">90<span>%</span></div>
                        <div class="sb-label">Vlaamse KMO's zonder goede site</div>
                    </div>
                    <div class="sb-item">
                        <div class="sb-num">100<span>%</span></div>
                        <div class="sb-label">In-house ontwikkeling</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===================== AUDIT ===================== -->
        <section id="audit">
            <div class="container">
                <p class="section-tag">AI-audit</p>
                <h2 class="section-title">Weet in 24 uur wat uw<br>website u kost.</h2>
                <div class="audit-wrap">
                    <div class="audit-features">
                        <div class="af-item">
                            <div class="af-icon">⚡</div>
                            <div>
                                <div class="af-title">Snelheidsanalyse</div>
                                <p class="af-text">We meten laadtijden en Core Web Vitals. Een trage site verliest 40% van
                                    bezoekers al in de eerste 3 seconden.</p>
                            </div>
                        </div>
                        <div class="af-item">
                            <div class="af-icon">🎯</div>
                            <div>
                                <div class="af-title">Conversie-beoordeling</div>
                                <p class="af-text">Zijn uw formulieren zo opgebouwd dat ze kwalitatieve leads binnenhalen?
                                    Wij checken elk contactpunt.</p>
                            </div>
                        </div>
                        <div class="af-item">
                            <div class="af-icon">🔍</div>
                            <div>
                                <div class="af-title">Lokale SEO-scan</div>
                                <p class="af-text">Scoort u voor "transportbedrijf Roeselare" of "koeltransport
                                    West-Vlaanderen"? Wij analyseren uw zichtbaarheid.</p>
                            </div>
                        </div>
                        <div class="af-item">
                            <div class="af-icon">📊</div>
                            <div>
                                <div class="af-title">Concurrentieanalyse</div>
                                <p class="af-text">Wie staat er boven u in Google, en waarom? U ontvangt concrete
                                    aanbevelingen om hen in te halen.</p>
                            </div>
                        </div>
                    </div>
                    <div class="audit-form-box">
                        <h3>Start uw gratis audit</h3>
                        <p>Vul uw gegevens in en ontvang binnen 24 uur een gepersonaliseerd rapport — volledig gratis,
                            zonder verplichtingen.</p>
                        <div class="aform">
                            <input type="text" placeholder="Bedrijfsnaam" />
                            <input type="url" placeholder="URL huidige website (bv. www.uwbedrijf.be)" />
                            <input type="email" placeholder="Uw e-mailadres" />
                            <button class="btn-primary" style="justify-content:center;width:100%">🔍 Analyseer mijn
                                website</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===================== SMART QUOTE ===================== -->
        <section id="smartquote">
            <div class="container">
                <p class="section-tag">Smart Quote System</p>
                <h2 class="section-title">Nooit meer incomplete<br>offerteaanvragen.</h2>
                <p class="section-sub">Ons digitaal formulier dwingt klanten alle gegevens in te vullen vóór ze kunnen
                    versturen. Uw dispatcher werkt direct met volledige dossiers.</p>
                <div class="quote-wrap">
                    <div class="quote-info">
                        <div class="qi-item">
                            <div class="qi-icon">🚛</div>
                            <div>
                                <div class="qi-title">Transporttype selectie</div>
                                <p class="qi-text">ADR, koeltransport, groupage, container — de klant kiest, het systeem
                                    past het formulier aan.</p>
                            </div>
                        </div>
                        <div class="qi-item">
                            <div class="qi-icon">📦</div>
                            <div>
                                <div class="qi-title">Verplichte vracht-info</div>
                                <p class="qi-text">Afmetingen, gewicht, laad- en losadres worden verplicht ingevoerd. Geen
                                    vage aanvragen meer.</p>
                            </div>
                        </div>
                        <div class="qi-item">
                            <div class="qi-icon">📬</div>
                            <div>
                                <div class="qi-title">Automatisch naar CRM</div>
                                <p class="qi-text">Elke aanvraag belandt gestructureerd in uw Mini-CRM dashboard. Opvolging
                                    in één oogopslag.</p>
                            </div>
                        </div>
                        <div class="qi-item">
                            <div class="qi-icon">🔔</div>
                            <div>
                                <div class="qi-title">Directe e-mailnotificatie</div>
                                <p class="qi-text">Uw team ontvangt onmiddellijk een melding met alle gegevens. Reageer
                                    sneller dan de concurrent.</p>
                            </div>
                        </div>
                    </div>
                    <div class="quote-form-box">
                        <h3>Demo: Offerteaanvraag</h3>
                        <p>Zo ziet uw klant het formulier — elk veld verplicht, geen chaos meer.</p>
                        <div class="qform">
                            <div class="qform-row">
                                <div class="fgroup">
                                    <label>Bedrijfsnaam</label>
                                    <input type="text" placeholder="NV Uw Bedrijf" />
                                </div>
                                <div class="fgroup">
                                    <label>Transporttype</label>
                                    <select>
                                        <option value="">Selecteer...</option>
                                        <option>Standaard wegtransport</option>
                                        <option>Koeltransport (ATP)</option>
                                        <option>ADR gevaarlijke stoffen</option>
                                        <option>Groupage</option>
                                        <option>Container / zee</option>
                                    </select>
                                </div>
                            </div>
                            <div class="qform-row">
                                <div class="fgroup">
                                    <label>Laadadres + postcode</label>
                                    <input type="text" placeholder="Industrielaan 5, 8800 Roeselare" />
                                </div>
                                <div class="fgroup">
                                    <label>Losadres + postcode</label>
                                    <input type="text" placeholder="Havenstraat 12, 2000 Antwerpen" />
                                </div>
                            </div>
                            <div class="qform-row">
                                <div class="fgroup">
                                    <label>Gewicht (kg)</label>
                                    <input type="number" placeholder="bv. 5000" />
                                </div>
                                <div class="fgroup">
                                    <label>Afmetingen (L×B×H cm)</label>
                                    <input type="text" placeholder="bv. 240 × 120 × 180" />
                                </div>
                            </div>
                            <div class="qform-row">
                                <div class="fgroup">
                                    <label>Gewenste laaddatum</label>
                                    <input type="date" />
                                </div>
                                <div class="fgroup">
                                    <label>E-mailadres</label>
                                    <input type="email" placeholder="contact@uwbedrijf.be" />
                                </div>
                            </div>
                            <div class="fgroup">
                                <label>Extra opmerkingen</label>
                                <textarea rows="3" placeholder="Laadplatform aanwezig? Specifieke vereisten?"></textarea>
                            </div>
                            <button class="btn-primary" style="justify-content:center;width:100%">📤 Offerte
                                aanvragen</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===================== ABOUT ===================== -->
        <section id="about">
            <div class="container">
                <p class="section-tag">Over ons</p>
                <h2 class="section-title">Geen generalistisch bureau.<br>Wij spreken logistiek.</h2>
                <div class="about-grid">
                    <div class="about-card">
                        <div class="ac-tag">Founder & Lead Developer</div>
                        <div class="ac-name">TransDigit Master</div>
                        <div class="ac-role">Specialist digitale transformatie — TSL-sector</div>
                        <p class="ac-bio">We werken uitsluitend voor transport-, spedities en logistieke bedrijven in
                            Flandria. Geen kappers, geen restaurants — enkel KMO's die vracht verplaatsen. Die focus
                            vertaalt zich in software die de sector écht begrijpt.</p>
                        <div class="tech-tags">
                            <span class="t-tag">Vue.js</span>
                            <span class="t-tag">Laravel</span>
                            <span class="t-tag">Livewire</span>
                            <span class="t-tag">Tailwind CSS</span>
                            <span class="t-tag">Multi-tenant SaaS</span>
                            <span class="t-tag">AI-integratie</span>
                        </div>
                    </div>
                    <div class="about-body">
                        <p>TransDigit Master is opgericht met één doel: de administratieve chaos in Vlaamse
                            transportbedrijven elimineren. We combineren diepgaande sectorkennis met moderne
                            webontwikkeling.</p>
                        <p>Ons model is uniek: we stappen binnen als webbouwer, maar leveren een Mini-CRM dat uitgroeit tot
                            een volwaardig ERP-systeem voor uw bedrijf — gefinancierd vanuit uw eigen groei, zonder grote
                            investeringen vooraf.</p>
                        <ul class="value-list">
                            <li><span class="vdot"></span> 100% in-house ontwikkeling, geen uitbesteding</li>
                            <li><span class="vdot"></span> Gesloten ecosysteem: hosting, frontend én backend bij ons</li>
                            <li><span class="vdot"></span> Live in 7 dagen of uw geld terug</li>
                            <li><span class="vdot"></span> Schaalbaar van one-pager tot volwaardig TMS</li>
                            <li><span class="vdot"></span> Actief in West-Vlaanderen: Roeselare, Kortrijk, Izegem</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===================== BLOG ===================== -->
        <section id="blog">
            <div class="container">
                <p class="section-tag">Kennisbank</p>
                <h2 class="section-title">Inzichten voor Vlaamse<br>transporteurs.</h2>
                <div class="blog-grid">
                    <div class="blog-card">
                        <div class="blog-thumb" style="background:linear-gradient(135deg,#0D1E35,#142845)">📄</div>
                        <div class="blog-body">
                            <div class="blog-cat">Regelgeving</div>
                            <div class="blog-title">E-facturering verplicht in 2026: wat moet uw transportbedrijf nu al
                                doen?</div>
                            <p class="blog-excerpt">De federale overheid voert verplichte e-facturering in voor B2B. Wij
                                leggen uit wat het betekent voor KMO-transporteurs en hoe u zich voorbereidt.</p>
                            <span class="blog-read">Lees verder →</span>
                        </div>
                    </div>
                    <div class="blog-card">
                        <div class="blog-thumb" style="background:linear-gradient(135deg,#142845,#1A3460)">🎯</div>
                        <div class="blog-body">
                            <div class="blog-cat">Digitalisering</div>
                            <div class="blog-title">Hoe een Smart Quote-formulier uw dispatcher 2 uur per dag bespaart</div>
                            <p class="blog-excerpt">Door klanten te verplichten alle gegevens in te vullen vóór het
                                versturen, stoppen de incomplete aanvragen definitief. Zo werkt het in de praktijk.</p>
                            <span class="blog-read">Lees verder →</span>
                        </div>
                    </div>
                    <div class="blog-card">
                        <div class="blog-thumb" style="background:linear-gradient(135deg,#08111F,#0D1E35)">🔍</div>
                        <div class="blog-body">
                            <div class="blog-cat">SEO & Vindbaarheid</div>
                            <div class="blog-title">Lokale SEO voor transporteurs: zo staat u boven uw concurrenten in
                                Roeselare</div>
                            <p class="blog-excerpt">Lokale zoektermen in de logistiek zijn goud waard. Ontdek welke concrete
                                stappen u vandaag al kunt zetten voor betere Google-resultaten.</p>
                            <span class="blog-read">Lees verder →</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===================== CONTACT ===================== -->
        <section id="contact">
            <div class="container">
                <p class="section-tag">Contact</p>
                <h2 class="section-title">Klaar voor het gesprek?</h2>
                <p class="section-sub">Geen verkoopspraatjes. Een eerlijk gesprek over uw situatie — en of wij de juiste
                    partner zijn voor uw bedrijf.</p>
                <div class="contact-grid">
                    <div class="contact-items">
                        <div class="ci">
                            <div class="ci-icon">📍</div>
                            <div>
                                <div class="ci-label">Regio</div>
                                <div class="ci-val">West-Vlaanderen<br><span
                                        style="font-size:13px;color:var(--muted);font-family:'DM Sans',sans-serif;font-weight:400">Roeselare
                                        · Kortrijk · Izegem</span></div>
                            </div>
                        </div>
                        <div class="ci">
                            <div class="ci-icon">📧</div>
                            <div>
                                <div class="ci-label">E-mail</div>
                                <div class="ci-val">info@transdigitmaster.be</div>
                            </div>
                        </div>
                        <div class="ci">
                            <div class="ci-icon">💼</div>
                            <div>
                                <div class="ci-label">LinkedIn</div>
                                <div class="ci-val">TransDigit Master</div>
                            </div>
                        </div>
                        <div class="ci">
                            <div class="ci-icon">⏱️</div>
                            <div>
                                <div class="ci-label">Responstijd</div>
                                <div class="ci-val">Binnen 24 uur op werkdagen</div>
                            </div>
                        </div>
                    </div>
                    <div class="contact-form">
                        <div class="cf-row">
                            <div class="cf-group">
                                <label>Voornaam</label>
                                <input type="text" placeholder="Jan" />
                            </div>
                            <div class="cf-group">
                                <label>Achternaam</label>
                                <input type="text" placeholder="Desmet" />
                            </div>
                        </div>
                        <div class="cf-group">
                            <label>Bedrijfsnaam</label>
                            <input type="text" placeholder="Desmet Transport NV" />
                        </div>
                        <div class="cf-group">
                            <label>E-mailadres</label>
                            <input type="email" placeholder="jan@desmettransport.be" />
                        </div>
                        <div class="cf-group">
                            <label>Pakket interesse</label>
                            <select>
                                <option value="">Selecteer een pakket...</option>
                                <option>Start — €850</option>
                                <option>Pro Transport — €1.150</option>
                                <option>Enterprise — €1.950+</option>
                                <option>Eerst gratis audit aanvragen</option>
                            </select>
                        </div>
                        <div class="cf-group">
                            <label>Bericht</label>
                            <textarea rows="4"
                                placeholder="Vertel ons kort over uw huidige situatie en wat u zoekt..."></textarea>
                        </div>
                        <button class="btn-primary" style="justify-content:center">Verstuur bericht →</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===================== FOOTER ===================== -->
        <footer>
            <div class="container">
                <div class="footer-inner">
                    <div class="footer-brand">
                        <div class="nav-logo">TransDigit<em style="color:var(--orange);font-style:normal">.</em></div>
                        <p>Digitale transformatie voor Vlaamse transport- en logistiekbedrijven. Van website tot ERP — in uw
                            eigen tempo.</p>
                    </div>
                    <div class="footer-col">
                        <h4>Navigatie</h4>
                        <ul>
                            <li><a href="#services">Diensten & Pakketten</a></li>
                            <li><a href="#hoewerkhet">Hoe het werkt</a></li>
                            <li><a href="#smartquote">Smart Quote</a></li>
                            <li><a href="#testimonials">Referenties</a></li>
                            <li><a href="#blog">Blog</a></li>
                        </ul>
                    </div>
                    <div class="footer-col">
                        <h4>Juridisch</h4>
                        <ul>
                            <li><a href="#">Privacybeleid</a></li>
                            <li><a href="#">Algemene voorwaarden</a></li>
                            <li><a href="#">Cookie-instellingen</a></li>
                            <li><a href="#">BTW: BE 0XXX.XXX.XXX</a></li>
                        </ul>
                    </div>
                    <div class="footer-col">
                        <h4>Contact</h4>
                        <ul>
                            <li><a href="#">info@transdigitmaster.be</a></li>
                            <li><a href="#">West-Vlaanderen, België</a></li>
                            <li><a href="#">LinkedIn</a></li>
                            <li><a href="#audit">Gratis audit</a></li>
                        </ul>
                    </div>
                </div>
                <div class="footer-bottom">
                    <p>© 2026 TransDigit Master — Logi-Web PRO. Alle rechten voorbehouden.</p>
                    <span class="footer-badge">🇧🇪 Made in West-Vlaanderen</span>
                </div>
            </div>
        </footer>
    </div>

    @fluxScripts
    <script>
        gsap.registerPlugin(ScrollTrigger);

        // Hero entrance
        gsap.fromTo("#hb", { opacity: 0, y: 28 }, { opacity: 1, y: 0, duration: 0.7, ease: "power3.out", delay: 0.35 });
        gsap.fromTo("#hh", { opacity: 0, y: 55 }, { opacity: 1, y: 0, duration: 0.9, ease: "power3.out", delay: 0.55 });
        gsap.fromTo("#hs", { opacity: 0, y: 30 }, { opacity: 1, y: 0, duration: 0.7, ease: "power3.out", delay: 0.78 });
        gsap.fromTo("#hbtns", { opacity: 0, y: 22 }, { opacity: 1, y: 0, duration: 0.6, ease: "power3.out", delay: 0.96 });
        gsap.fromTo("#hstats", { opacity: 0, y: 18 }, { opacity: 1, y: 0, duration: 0.6, ease: "power3.out", delay: 1.12 });

        // Counter animation
        function counter(el, target, suffix, duration) {
            let start = 0;
            const increment = target / (duration / 16);
            const timer = setInterval(() => {
                start = Math.min(start + increment, target);
                el.innerHTML = Math.round(start) + '<span>' + suffix + '</span>';
                if (start >= target) clearInterval(timer);
            }, 16);
        }
        ScrollTrigger.create({
            trigger: "#hstats", start: "top 90%",
            onEnter: () => {
                counter(document.getElementById("sc1"), 12, "+", 1100);
                counter(document.getElementById("sc2"), 7, " dagen", 900);
                counter(document.getElementById("sc3"), 67, "%", 1300);
            }
        });

        // Scroll fade-in for all sections
        const fadeEls = document.querySelectorAll(
            '.prob-card, .pkg-card, .step, .testi-card, .af-item, .qi-item, .blog-card, .ci, ' +
            '.section-tag, .section-title, .section-sub, .audit-form-box, .audit-features, ' +
            '.about-card, .about-body, .quote-info, .quote-form-box, .contact-items, .contact-form'
        );
        fadeEls.forEach((el, i) => {
            gsap.fromTo(el,
                { opacity: 0, y: 36 },
                {
                    opacity: 1, y: 0, duration: 0.7, ease: "power3.out",
                    scrollTrigger: { trigger: el, start: "top 90%", toggleActions: "play none none none" }
                }
            );
        });

        // Navbar on scroll
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('navbar');
            if (nav) {
                nav.style.background = window.scrollY > 60
                    ? 'rgba(8,17,31,0.98)'
                    : 'rgba(8,17,31,0.88)';
            }
        });
    </script>
</body>

</html>
