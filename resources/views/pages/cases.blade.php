@extends('layouts.guest')
@section('title', 'Realisaties | TransDigit Master')

@section('content')
<!-- ===================== CASES HERO ===================== -->
<section id="cases-hero" style="padding: 150px 0 80px;">
  <div class="container">
    <p class="section-tag">Realisaties</p>
    <h2 class="section-title">Case Studies & Succesverhalen</h2>
    <p class="section-sub">Bekijk hoe wij Vlaamse transporteurs helpen digitaliseren, tijd besparen en meer leads genereren. Echte resultaten in cijfers.</p>
  </div>
</section>

<!-- ===================== TESTIMONIALS ===================== -->
<section id="testimonials" style="padding-top: 20px;">
  <div class="container">
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

<!-- ===================== NEW CASE STUDY PLACEHOLDER ===================== -->
<section id="case-studies" style="padding: 100px 0; background: var(--navy-mid);">
  <div class="container">
    <div class="about-grid" style="grid-template-columns: 1fr;">
      <div class="about-card" style="border-color: var(--orange);">
        <div class="ac-tag">Featured Case Study</div>
        <div class="ac-name">Desmet Transport: Van chaos naar gestructureerde opdrachten</div>
        <p class="ac-bio">Voorheen ontving Desmet Transport wekelijks 40+ telefoontjes met incomplete prijsaanvragen. Door de implementatie van ons Smart Quote systeem en een vernieuwde landingspagina, hebben we het proces 100% gedigitaliseerd.</p>
        <div class="tech-tags">
          <span class="t-tag">Tijdbesparing: 10u/week</span>
          <span class="t-tag">Conversie: +35%</span>
          <span class="t-tag">Lead Kwaliteit: 100% Compleet</span>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
gsap.registerPlugin(ScrollTrigger);
const fadeEls = document.querySelectorAll('.testi-card, .about-card');
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
