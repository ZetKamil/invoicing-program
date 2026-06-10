@extends('layouts.guest')
@section('title', 'Kennisbank & Blog | TransDigit Master')

@section('content')
<!-- ===================== BLOG ===================== -->
<section id="blog" style="padding-top: 150px;">
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

<script>
gsap.registerPlugin(ScrollTrigger);
const fadeEls = document.querySelectorAll('.blog-card');
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
