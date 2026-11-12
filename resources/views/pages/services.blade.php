@extends('layouts.guest')
@section('title', 'Diensten & Pakketten | TransDigit Master')

@section('content')
<!-- ===================== SERVICES / PACKAGES ===================== -->
<section id="services" style="padding-top: 150px;">
  <div class="container">
    <p class="section-tag">Onze pakketten</p>
    <h2 class="section-title">Transparante prijzen.<br>Geen verrassingen.</h2>
    <p class="section-sub">Elk pakket bevat hosting, frontend én een Mini-CRM dashboard. Eenmalig + kleine maandelijkse abonnementskost.</p>
    <div class="packages-grid">
      <!-- START -->
      <div class="pkg-card">
        <div class="pkg-tier">Basic Setup</div>
        <div class="pkg-price"><sup>€</sup>850</div>
        <div class="pkg-recur">eenmalig (excl. hosting/licentie)</div>
        <div class="pkg-divider"></div>
        <ul class="pkg-features">
          <li><span class="feat-check">✓</span> Professionele one-pager</li>
          <li><span class="feat-check">✓</span> Basis offerteformulier</li>
          <li><span class="feat-check">✓</span> SEO-optimalisatie</li>
          <li><span class="feat-check">✓</span> Google My Business setup</li>
          <li><span class="feat-check">✓</span> Hosting basis inbegrepen</li>
          <li><span class="feat-check">✓</span> SSL-certificaat</li>
        </ul>
        <a href="{{ url('/#contact') }}" class="pkg-btn" style="display:block;text-align:center">Kies Basic Setup</a>
      </div>
      <!-- PRO -->
      <div class="pkg-card popular">
        <div class="popular-label">Meest gekozen</div>
        <div class="pkg-tier">Pro Transport Edition</div>
        <div class="pkg-price"><sup>€</sup>2.450</div>
        <div class="pkg-recur">eenmalig + €600/jaar (SaaS Licentie & Support)</div>
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
        <a href="{{ url('/#contact') }}" class="pkg-btn" style="display:block;text-align:center">Kies Pro Transport</a>
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
          <li><span class="feat-check">✓</span> E-facturatiemodule (Peppol)</li>
          <li><span class="feat-check">✓</span> Chauffeursbeheer</li>
          <li><span class="feat-check">✓</span> Meewerken aan roadmap</li>
          <li><span class="feat-check">✓</span> Prioriteits-support</li>
          <li><span class="feat-check">✓</span> TMS-uitbreiding mogelijk</li>
        </ul>
        <a href="{{ url('/#contact') }}" class="pkg-btn" style="display:block;text-align:center">Vraag offerte aan</a>
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

<script>
gsap.registerPlugin(ScrollTrigger);
const fadeEls = document.querySelectorAll('.pkg-card, .step, .section-tag, .section-title, .section-sub');
fadeEls.forEach((el, i) => {
  gsap.fromTo(el,
    { opacity: 0, y: 36 },
    {
      opacity: 1, y: 0, duration: 0.7, ease: "power3.out",
      scrollTrigger: { trigger: el, start: "top 90%", toggleActions: "play none none none" }
    }
  );
});
</script>
@endsection
