@extends('layouts.guest')
@section('title', 'Over Ons | TransDigit Master')

@section('content')
<!-- ===================== ABOUT ===================== -->
<section id="about" style="padding-top: 150px;">
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
          <span class="t-tag">Multi-bedrijf SaaS</span>
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

<!-- ===================== STATS BAR ===================== -->
<div id="statsbar" style="margin-top: 100px;">
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

<script>
gsap.registerPlugin(ScrollTrigger);
const fadeEls = document.querySelectorAll('.about-card, .about-body');
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
