@extends('layouts.guest')

@section('content')

<!-- ===================== HERO ===================== -->
<section id="hero">
    <div class="hero-grid"></div>
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
        Ultrasnelle websites met een ingebouwd Smart Quote-systeem voor Vlaamse transporteurs. Uw dispatcher ontvangt enkel nog volledige, gestructureerde offerteaanvragen.
      </p>
      <div class="hero-btns" id="hbtns">
        <button class="btn-primary" onclick="document.getElementById('audit').scrollIntoView({behavior:'smooth'})">
          🔍 Gratis AI-audit aanvragen
        </button>
        <button class="btn-outline" onclick="document.getElementById('services').scrollIntoView({behavior:'smooth'})">
          Bekijk pakketten
        </button>
      </div>
      <div class="hero-stats" id="hstats">
        <div class="stat-item">
          <div class="stat-num" id="sc1">0<span>+</span></div>
          <div class="stat-label">actieve klanten</div>
        </div>
        <div class="stat-item">
          <div class="stat-num" id="sc2">0<span> dgn</span></div>
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
    <p class="section-sub">We zijn geen gewoon webbureau. We kennen het verschil tussen ADR, koeltransport en containerlogistiek — en dat zie je in onze software.</p>
    <div class="problems-grid">
      <div class="prob-card">
        <div class="prob-icon">📞</div>
        <div class="prob-title">Eindeloos bellen voor incomplete offertes</div>
        <p class="prob-text">Klanten bellen zonder afmetingen, gewicht of postcodes. Uw dispatcher verliest dagelijks uren aan nutteloze heen-en-weerberichten en incomplete mails.</p>
      </div>
      <div class="prob-card">
        <div class="prob-icon">📄</div>
        <div class="prob-title">E-facturering verplicht vanaf 2026</div>
        <p class="prob-text">De wetgeving klopt aan de deur. Grote ERP-systemen zoals SAP zijn onbetaalbaar voor KMO's. Wij bieden een schaalbaar alternatief dat meegroeit met uw bedrijf.</p>
      </div>
      <div class="prob-card">
        <div class="prob-icon">🌐</div>
        <div class="prob-title">Verouderde website, nul B2B-leads</div>
        <p class="prob-text">90% van de lokale transporteurs heeft een digitale visitekaart die geen enkel kwalitatief contact oplevert. Dat is gemiste omzet, elke dag opnieuw.</p>
      </div>
    </div>
  </div>
</section>

<!-- ===================== SERVICES / PACKAGES ===================== -->
<section id="services">
  <div class="container">
    <p class="section-tag">Onze pakketten</p>
    <h2 class="section-title">Transparante prijzen.<br>Geen verrassingen.</h2>
    <p class="section-sub">Elk pakket bevat hosting, frontend én een Mini-CRM dashboard. Eenmalig + kleine maandelijkse abonnementskost.</p>
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
        <button class="pkg-btn" onclick="selectPackage('Start')">Kies Start</button>
      </div>
      <!-- PRO -->
      <div class="pkg-card popular">
        <div class="popular-label">Meest gekozen</div>
        <div class="pkg-tier">Pro Transport</div>
        <div class="pkg-price"><sup>€</sup>1.150</div>
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
        <button class="pkg-btn" onclick="selectPackage('Pro Transport')">Kies Pro Transport</button>
      </div>
      <!-- ENTERPRISE -->
      <div class="pkg-card">
        <div class="pkg-tier">Enterprise</div>
        <div class="pkg-price" style="font-size: 32px;">Prijs op maat</div>
        <div class="pkg-recur">afhankelijk van uw project</div>
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
        <button class="pkg-btn" onclick="selectPackage('Enterprise')">Vraag offerte aan</button>
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
        <p class="step-text">Onze AI analyseert uw huidige website op snelheid, UX en conversie. U ontvangt een concreet rapport binnen 24 uur.</p>
      </div>
      <div class="step">
        <div class="step-num">2</div>
        <div class="step-title">Intakegesprek</div>
        <p class="step-text">30 minuten. Geen verkoopspraatjes. We luisteren naar uw bedrijf en stellen het juiste pakket voor.</p>
      </div>
      <div class="step">
        <div class="step-num">3</div>
        <div class="step-title">Bouw & configuratie</div>
        <p class="step-text">Wij bouwen uw site en Mini-CRM. U levert de materialen aan op dag 1, wij doen de rest.</p>
      </div>
      <div class="step">
        <div class="step-num">4</div>
        <div class="step-title">Live & schaalbaar</div>
        <p class="step-text">Dag 7: uw site staat live. Leads stromen gestructureerd binnen. Upsell-modules groeien mee op uw tempo.</p>
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
        <p class="testi-quote">Onze dispatcher ontvangt nu alleen nog volledige offerteaanvragen. Het scheelt ons minstens 2 uur per dag aan telefoontjes. Eindelijk rust op de planning.</p>
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
        <p class="testi-quote">In 7 dagen een professionele site én een systeem waarmee mijn team leads opvolgt. Ik had niet verwacht dat dit zo snel en vlot kon verlopen. Sterk werk.</p>
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
        <p class="testi-quote">We verloren klanten aan concurrenten met een betere online aanwezigheid. Nu scoren we als eerste in Google voor koeltransport in de regio. Meer kan ik niet vragen.</p>
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
        <div class="sb-num">7<span> dgn</span></div>
        <div class="sb-label">Gemiddelde doorlooptijd</div>
      </div>
      <div class="sb-item">
        <div class="sb-num">€400<span>+/u</span></div>
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
            <p class="af-text">We meten laadtijden och Core Web Vitals. Een trage site verliest 40% van bezoekers al in de eerste 3 seconden.</p>
          </div>
        </div>
        <div class="af-item">
          <div class="af-icon">🎯</div>
          <div>
            <div class="af-title">Conversie-beoordeling</div>
            <p class="af-text">Zijn uw formulieren zo opgebouwd dat ze kwalitatieve leads binnenhalen? Wij checken elk contactpunt.</p>
          </div>
        </div>
        <div class="af-item">
          <div class="af-icon">🔍</div>
          <div>
            <div class="af-title">Lokale SEO-scan</div>
            <p class="af-text">Scoort u voor "transportbedrijf Roeselare" of "koeltransport West-Vlaanderen"? Wij analyseren uw zichtbaarheid.</p>
          </div>
        </div>
        <div class="af-item">
          <div class="af-icon">📊</div>
          <div>
            <div class="af-title">Concurrentieanalyse</div>
            <p class="af-text">Wie staat er boven u in Google, en waarom? U ontvangt concrete aanbevelingen om hen in te halen.</p>
          </div>
        </div>
      </div>
      <div class="audit-form-box">
        <h3>Start uw gratis audit</h3>
        <p>Vul uw gegevens in en ontvang binnen 24 uur een gepersonaliseerd rapport — volledig gratis, zonder verplichtingen.</p>
        <div class="aform" id="audit-form-container">
          <input type="text" placeholder="Bedrijfsnaam" />
          <input type="url" placeholder="URL huidige website (bv. www.uwbedrijf.be)" />
          <input type="email" placeholder="Uw e-mailadres" />
          <button class="btn-primary" style="justify-content:center;width:100%" onclick="submitAudit(event)">🔍 Analyseer mijn website</button>
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
    <p class="section-sub">Ons digitaal formulier dwingt klanten alle gegevens in te vullen vóór ze kunnen versturen. Uw dispatcher werkt direct met volledige dossiers.</p>
    <div class="quote-wrap">
      <div class="quote-info">
        <div class="qi-item">
          <div class="qi-icon">🚛</div>
          <div>
            <div class="qi-title">Transporttype selectie</div>
            <p class="qi-text">ADR, koeltransport, groupage, container — de klant kiest, het systeem past het formulier aan.</p>
          </div>
        </div>
        <div class="qi-item">
          <div class="qi-icon">📦</div>
          <div>
            <div class="qi-title">Verplichte vracht-info</div>
            <p class="qi-text">Afmetingen, gewicht, laad- en losadres worden verplicht ingevoerd. Geen vage aanvragen meer.</p>
          </div>
        </div>
        <div class="qi-item">
          <div class="qi-icon">📬</div>
          <div>
            <div class="qi-title">Automatisch naar CRM</div>
            <p class="qi-text">Elke aanvraag belandt gestructureerd in uw Mini-CRM dashboard. Opvolging in één oogopslag.</p>
          </div>
        </div>
        <div class="qi-item">
          <div class="qi-icon">🔔</div>
          <div>
            <div class="qi-title">Directe e-mailnotificatie</div>
            <p class="qi-text">Uw team ontvangt onmiddellijk een melding met alle gegevens. Reageer sneller dan de concurrent.</p>
          </div>
        </div>
      </div>
      <div class="quote-form-box">
        <h3>Demo: Offerteaanvraag</h3>
        <p>Zo ziet uw klant het formulier — elk veld verplicht, geen chaos meer.</p>
        <div class="qform" id="quote-form-container">
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
          <button class="btn-primary" style="justify-content:center;width:100%" onclick="submitQuote(event)">📤 Offerte aanvragen</button>
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
        <p class="ac-bio">We werken uitsluitend voor transport-, spedities en logistieke bedrijven in Flandria. Geen kappers, geen restaurants — enkel KMO's die vracht verplaatsen. Die focus vertaalt zich in software die de sector écht begrijpt.</p>
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
        <p>TransDigit Master is opgericht met één doel: de administratieve chaos in Vlaamse transportbedrijven elimineren. We combineren diepgaande sectorkennis met moderne webontwikkeling.</p>
        <p>Ons model is uniek: we stappen binnen als webbouwer, maar leveren een Mini-CRM dat uitgroeit tot een volwaardig ERP-systeem voor uw bedrijf — gefinancierd vanuit uw eigen groei, zonder grote investeringen vooraf.</p>
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
        <div class="blog-thumb" style="padding: 0;">
          <img src="{{ asset('images/blog/blog_einvoicing.png') }}" alt="E-facturering 2026" style="width:100%; height:100%; object-fit:cover;" />
        </div>
        <div class="blog-body">
          <div class="blog-cat">Regelgeving</div>
          <div class="blog-title">E-facturering verplicht in 2026: wat moet uw transportbedrijf nu al doen?</div>
          <p class="blog-excerpt">De federale overheid voert verplichte e-facturering in voor B2B. Wij leggen uit wat het betekent voor KMO-transporteurs och hoe u zich voorbereidt.</p>
          <span class="blog-read">Lees verder →</span>
        </div>
      </div>
      <div class="blog-card">
        <div class="blog-thumb" style="padding: 0;">
          <img src="{{ asset('images/blog/blog_dispatcher.png') }}" alt="Smart Quote Formulier" style="width:100%; height:100%; object-fit:cover;" />
        </div>
        <div class="blog-body">
          <div class="blog-cat">Digitalisering</div>
          <div class="blog-title">Hoe een Smart Quote-formulier uw dispatcher 2 uur per dag bespaart</div>
          <p class="blog-excerpt">Door klanten te verplichten alle gegevens in te vullen vóór het versturen, stoppen de incomplete aanvragen definitief. Zo werkt het in de praktijk.</p>
          <span class="blog-read">Lees verder →</span>
        </div>
      </div>
      <div class="blog-card">
        <div class="blog-thumb" style="padding: 0;">
          <img src="{{ asset('images/blog/blog_seo.png') }}" alt="Lokale SEO" style="width:100%; height:100%; object-fit:cover;" />
        </div>
        <div class="blog-body">
          <div class="blog-cat">SEO & Vindbaarheid</div>
          <div class="blog-title">Lokale SEO voor transporteurs: zo staat u boven uw concurrenten in Roeselare</div>
          <p class="blog-excerpt">Lokale zoektermen in de logistiek zijn goud waard. Ontdek welke concrete stappen u vandaag al kunt zetten voor betere Google-resultaten.</p>
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
    <p class="section-sub">Geen verkoopspraatjes. Een eerlijk gesprek over uw situatie — och of wij de juiste partner zijn voor uw bedrijf.</p>
    <div class="contact-grid">
      <div class="contact-items">
        <div class="ci">
          <div class="ci-icon">📍</div>
          <div>
            <div class="ci-label">Regio</div>
            <div class="ci-val">West-Vlaanderen<br><span style="font-size:13px;color:var(--muted);font-family:'DM Sans',sans-serif;font-weight:400">Roeselare · Kortrijk · Izegem</span></div>
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
      
      <livewire:public.contact-form />
      
    </div>
  </div>
</section>


<script>
gsap.registerPlugin(ScrollTrigger);

// Hero entrance
gsap.fromTo("#hb",  {opacity:0,y:28},{opacity:1,y:0,duration:0.7,ease:"power3.out",delay:0.35});
gsap.fromTo("#hh",  {opacity:0,y:55},{opacity:1,y:0,duration:0.9,ease:"power3.out",delay:0.55});
gsap.fromTo("#hs",  {opacity:0,y:30},{opacity:1,y:0,duration:0.7,ease:"power3.out",delay:0.78});
gsap.fromTo("#hbtns",{opacity:0,y:22},{opacity:1,y:0,duration:0.6,ease:"power3.out",delay:0.96});
gsap.fromTo("#hstats",{opacity:0,y:18},{opacity:1,y:0,duration:0.6,ease:"power3.out",delay:1.12});

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
    counter(document.getElementById("sc2"), 7, " dgn", 900);
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
  nav.style.background = window.scrollY > 60
    ? 'rgba(9,9,11,0.85)'
    : 'rgba(9,9,11,0.95)';
});

// Form logic
function submitAudit(e) {
  const btn = e.target;
  btn.innerHTML = "⏳ Analyseren...";
  btn.style.opacity = "0.7";
  btn.style.pointerEvents = "none";
  setTimeout(() => {
    document.getElementById('audit-form-container').innerHTML = `
      <div style="text-align:center; padding: 24px; background: rgba(212,255,0,0.05); border: 1px solid var(--orange); border-radius: 12px;">
        <h4 style="color: #fff; font-size: 18px; margin-bottom: 8px;">Audit aangevraagd! ✅</h4>
        <p style="color: var(--muted); font-size: 14px; line-height: 1.6;">De AI heeft uw website succesvol gescand. U ontvangt het rapport binnen 24 uur in uw mailbox.</p>
      </div>
    `;
  }, 1500);
}

function submitQuote(e) {
  const btn = e.target;
  btn.innerHTML = "⏳ Verzenden...";
  btn.style.opacity = "0.7";
  btn.style.pointerEvents = "none";
  setTimeout(() => {
    document.getElementById('quote-form-container').innerHTML = `
      <div style="text-align:center; padding: 34px; background: rgba(212,255,0,0.05); border: 1px solid var(--orange); border-radius: 12px; height: 100%; display:flex; flex-direction:column; justify-content:center;">
        <div style="font-size: 40px; margin-bottom: 15px;">📦</div>
        <h4 style="color: #fff; font-size: 20px; margin-bottom: 8px;">Dossier succesvol verzonden!</h4>
        <p style="color: var(--muted); font-size: 15px; line-height: 1.6;">Dit is hoe uw klant het ervaart. De aanvraag zit nu veilig in uw Mini-CRM. Uw dispatcher kan direct aan de slag met een volledig dossier.</p>
      </div>
    `;
  }, 1200);
}

function selectPackage(pkgName) {
  document.getElementById('contact').scrollIntoView({behavior:'smooth'});
  setTimeout(() => {
    // Try to find the livewire textarea
    const textarea = document.querySelector('textarea[wire\\\\:model="message"], textarea');
    if (textarea) {
      textarea.value = "Beste, ik ben geïnteresseerd in het " + pkgName + " pakket. Graag plannen we een vrijblijvend intakegesprek in.";
      textarea.dispatchEvent(new Event('input', { bubbles: true }));
      textarea.focus();
    }
  }, 800);
}
</script>
@endsection
