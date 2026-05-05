<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'TransDigit Master — Logi-Web PRO | Digitale oplossingen voor transport')</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,400&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxStyles
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>

    <style>
    /* ===== RESET & BASE ===== */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --navy:       #08111F;
      --navy-mid:   #0D1E35;
      --navy-light: #142845;
      --navy-card:  #0F1E32;
      --orange:     #F97316;
      --orange-d:   #DC6A0E;
      --orange-glow:rgba(249,115,22,0.15);
      --gold:       #FBBF24;
      --white:      #FFFFFF;
      --text:       #D9E6F5;
      --muted:      #7B9EC4;
      --dim:        #3F5A7A;
      --border:     rgba(139,163,199,0.1);
      --border-o:   rgba(249,115,22,0.25);
    }
    html { scroll-behavior: smooth; }
    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--navy);
      color: var(--text);
      overflow-x: hidden;
      line-height: 1.65;
      font-size: 16px;
    }
    h1, h2, h3, h4, h5 { font-family: 'Outfit', sans-serif; line-height: 1.12; }
    a { text-decoration: none; color: inherit; }
    img { display: block; max-width: 100%; }
    button { cursor: pointer; font-family: 'DM Sans', sans-serif; }

    /* ===== UTILITY ===== */
    .container { max-width: 1160px; margin: 0 auto; padding: 0 32px; }
    .section-tag {
      display: inline-block;
      font-size: 11px; font-weight: 700; letter-spacing: 2.5px;
      text-transform: uppercase; color: var(--orange);
      margin-bottom: 14px;
    }
    .section-title {
      font-size: clamp(30px, 4vw, 50px);
      font-weight: 800; color: #fff;
      letter-spacing: -1.5px; margin-bottom: 16px;
    }
    .section-sub {
      font-size: 16px; color: var(--muted);
      max-width: 540px; line-height: 1.7;
    }
    .btn-primary {
      display: inline-flex; align-items: center; gap: 8px;
      background: var(--orange); color: #fff;
      border: none; padding: 14px 28px;
      border-radius: 8px;
      font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 14px;
      letter-spacing: 0.3px;
      transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
    }
    .btn-primary:hover {
      background: var(--orange-d);
      transform: translateY(-2px);
      box-shadow: 0 10px 36px rgba(249,115,22,0.35);
    }
    .btn-outline {
      display: inline-flex; align-items: center; gap: 8px;
      background: transparent; color: var(--text);
      border: 1px solid var(--dim); padding: 14px 28px;
      border-radius: 8px;
      font-family: 'Outfit', sans-serif; font-weight: 600; font-size: 14px;
      transition: border-color 0.2s, color 0.2s;
    }
    .btn-outline:hover { border-color: var(--orange); color: var(--orange); }

    /* ===== NAVBAR ===== */
    #navbar {
      position: fixed; top: 0; left: 0; right: 0; z-index: 999;
      height: 70px;
      background: rgba(8,17,31,0.88);
      backdrop-filter: blur(20px);
      border-bottom: 1px solid var(--border);
      transition: background 0.3s;
    }
    .nav-inner {
      max-width: 1160px; margin: 0 auto; padding: 0 32px;
      height: 100%; display: flex; align-items: center; justify-content: space-between;
    }
    .nav-logo {
      font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 19px;
      color: #fff; letter-spacing: -0.5px;
    }
    .nav-logo em { color: var(--orange); font-style: normal; }
    .nav-links { display: flex; gap: 30px; list-style: none; }
    .nav-links a {
      font-size: 13.5px; font-weight: 500; color: var(--muted);
      transition: color 0.2s; letter-spacing: 0.2px;
    }
    .nav-links a:hover { color: var(--orange); }
    .nav-cta {
      background: var(--orange); color: #fff;
      border: none; padding: 9px 20px;
      border-radius: 7px;
      font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 13px;
      letter-spacing: 0.3px;
      transition: background 0.2s, transform 0.15s;
    }
    .nav-cta:hover { background: var(--orange-d); transform: translateY(-1px); }

    /* ===== HERO ===== */
    #hero {
      min-height: 100vh;
      display: flex; align-items: center;
      padding: 130px 0 90px;
      position: relative; overflow: hidden;
    }
    .hero-grid {
      position: absolute; inset: 0; pointer-events: none;
      background-image:
        linear-gradient(rgba(249,115,22,0.035) 1px, transparent 1px),
        linear-gradient(90deg, rgba(249,115,22,0.035) 1px, transparent 1px);
      background-size: 64px 64px;
    }
    .hero-glow-1 {
      position: absolute; top: -180px; right: -120px;
      width: 700px; height: 700px; pointer-events: none;
      background: radial-gradient(circle, rgba(249,115,22,0.11) 0%, transparent 65%);
    }
    .hero-glow-2 {
      position: absolute; bottom: -100px; left: -100px;
      width: 500px; height: 500px; pointer-events: none;
      background: radial-gradient(circle, rgba(20,40,100,0.5) 0%, transparent 70%);
    }
    .hero-content { position: relative; z-index: 1; max-width: 780px; }
    .hero-badge {
      display: inline-flex; align-items: center; gap: 9px;
      background: rgba(249,115,22,0.1);
      border: 1px solid rgba(249,115,22,0.28);
      color: var(--orange); padding: 6px 16px;
      border-radius: 100px; font-size: 12px; font-weight: 700;
      letter-spacing: 1.4px; text-transform: uppercase; margin-bottom: 28px;
    }
    .badge-dot {
      width: 7px; height: 7px; border-radius: 50%;
      background: var(--orange);
      animation: blink 1.9s ease-in-out infinite;
    }
    @keyframes blink {
      0%, 100% { opacity: 1; transform: scale(1); }
      50% { opacity: 0.45; transform: scale(1.5); }
    }
    .hero-h1 {
      font-size: clamp(42px, 6vw, 78px);
      font-weight: 800; color: #fff;
      letter-spacing: -3px; margin-bottom: 22px;
      line-height: 1.05;
    }
    .hero-h1 .accent { color: var(--orange); }
    .hero-sub {
      font-size: clamp(16px, 1.8vw, 20px); color: var(--muted);
      max-width: 580px; margin-bottom: 42px; line-height: 1.6;
    }
    .hero-btns { display: flex; gap: 14px; flex-wrap: wrap; margin-bottom: 64px; }
    .hero-stats {
      display: flex; gap: 48px; flex-wrap: wrap;
      padding-top: 40px;
      border-top: 1px solid var(--border);
    }
    .stat-num {
      font-family: 'Outfit', sans-serif; font-size: 38px;
      font-weight: 800; color: #fff; line-height: 1;
    }
    .stat-num span { color: var(--orange); }
    .stat-label {
      font-size: 11.5px; color: var(--muted);
      text-transform: uppercase; letter-spacing: 0.9px; margin-top: 5px;
    }

    /* ===== PROBLEMS ===== */
    #problems { padding: 100px 0; background: var(--navy-mid); }
    .problems-grid {
      display: grid; grid-template-columns: repeat(3, 1fr);
      gap: 24px; margin-top: 56px;
    }
    .prob-card {
      background: rgba(255,255,255,0.025);
      border: 1px solid var(--border);
      border-radius: 14px; padding: 30px;
      position: relative; overflow: hidden;
      transition: border-color 0.3s, transform 0.3s;
    }
    .prob-card::before {
      content: '';
      position: absolute; top: 0; left: 0; right: 0; height: 3px;
      background: var(--orange);
      transform: scaleX(0); transform-origin: left;
      transition: transform 0.35s;
    }
    .prob-card:hover { border-color: var(--border-o); transform: translateY(-5px); }
    .prob-card:hover::before { transform: scaleX(1); }
    .prob-icon {
      width: 48px; height: 48px;
      background: rgba(249,115,22,0.1);
      border: 1px solid rgba(249,115,22,0.2);
      border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      font-size: 22px; margin-bottom: 20px;
    }
    .prob-title {
      font-family: 'Outfit', sans-serif; font-weight: 700;
      font-size: 17px; color: #fff; margin-bottom: 10px;
    }
    .prob-text { font-size: 14px; color: var(--muted); line-height: 1.7; }

    /* ===== SERVICES / PACKAGES ===== */
    #services { padding: 100px 0; background: var(--navy); }
    .packages-grid {
      display: grid; grid-template-columns: repeat(3, 1fr);
      gap: 24px; margin-top: 56px;
    }
    .pkg-card {
      background: var(--navy-card);
      border: 1px solid var(--border);
      border-radius: 16px; padding: 38px;
      display: flex; flex-direction: column;
      position: relative; overflow: hidden;
      transition: transform 0.3s, border-color 0.3s;
    }
    .pkg-card:hover { transform: translateY(-6px); }
    .pkg-card.popular {
      border-color: var(--orange);
      background: rgba(249,115,22,0.05);
    }
    .popular-label {
      position: absolute; top: 20px; right: 20px;
      background: var(--orange); color: #fff;
      font-size: 11px; font-weight: 700;
      padding: 4px 12px; border-radius: 100px;
      letter-spacing: 0.6px; text-transform: uppercase;
    }
    .pkg-tier {
      font-size: 11px; font-weight: 700; letter-spacing: 2.5px;
      text-transform: uppercase; color: var(--muted); margin-bottom: 10px;
    }
    .pkg-price {
      font-family: 'Outfit', sans-serif; font-size: 52px;
      font-weight: 800; color: #fff; line-height: 1;
    }
    .pkg-price sup { font-size: 22px; color: var(--orange); vertical-align: super; }
    .pkg-recur {
      font-size: 13px; color: var(--dim); margin-bottom: 28px; margin-top: 4px;
    }
    .pkg-divider { height: 1px; background: var(--border); margin-bottom: 26px; }
    .pkg-features { list-style: none; display: flex; flex-direction: column; gap: 13px; flex: 1; }
    .pkg-features li {
      font-size: 14px; color: var(--muted);
      display: flex; align-items: flex-start; gap: 10px;
    }
    .feat-check {
      width: 20px; height: 20px; min-width: 20px;
      border-radius: 50%;
      background: rgba(249,115,22,0.12);
      border: 1px solid rgba(249,115,22,0.35);
      display: flex; align-items: center; justify-content: center;
      font-size: 10px; color: var(--orange); font-weight: 700;
      margin-top: 1px;
    }
    .pkg-btn {
      margin-top: 30px; width: 100%;
      background: rgba(249,115,22,0.1);
      color: var(--orange);
      border: 1px solid var(--border-o);
      padding: 13px; border-radius: 9px;
      font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 13.5px;
      letter-spacing: 0.3px;
      transition: background 0.2s, color 0.2s;
    }
    .pkg-card.popular .pkg-btn,
    .pkg-btn:hover {
      background: var(--orange); color: #fff; border-color: var(--orange);
    }

    /* ===== HOW IT WORKS ===== */
    #hoewerkhet { padding: 100px 0; background: var(--navy-mid); }
    .steps-grid {
      display: grid; grid-template-columns: repeat(4, 1fr);
      gap: 8px; margin-top: 60px; position: relative;
    }
    .steps-grid::after {
      content: '';
      position: absolute; top: 32px; left: 12.5%; right: 12.5%; height: 1px;
      background: linear-gradient(90deg, transparent, var(--border-o), transparent);
    }
    .step { text-align: center; padding: 10px 20px; }
    .step-num {
      width: 64px; height: 64px; border-radius: 50%;
      background: var(--navy-card);
      border: 2px solid var(--border-o);
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 22px;
      font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 20px; color: var(--orange);
      position: relative; z-index: 1;
      transition: background 0.3s, border-color 0.3s;
    }
    .step:hover .step-num { background: rgba(249,115,22,0.12); border-color: var(--orange); }
    .step-title {
      font-family: 'Outfit', sans-serif; font-weight: 700;
      font-size: 16px; color: #fff; margin-bottom: 9px;
    }
    .step-text { font-size: 13.5px; color: var(--muted); line-height: 1.65; }

    /* ===== TESTIMONIALS ===== */
    #testimonials { padding: 100px 0; background: var(--navy); }
    .testi-grid {
      display: grid; grid-template-columns: repeat(3, 1fr);
      gap: 24px; margin-top: 56px;
    }
    .testi-card {
      background: var(--navy-card);
      border: 1px solid var(--border);
      border-radius: 16px; padding: 34px;
      transition: border-color 0.3s, transform 0.3s;
    }
    .testi-card:hover { border-color: var(--border-o); transform: translateY(-4px); }
    .stars { color: var(--gold); font-size: 15px; letter-spacing: 2px; margin-bottom: 18px; }
    .testi-quote {
      font-size: 15px; color: var(--text);
      line-height: 1.75; margin-bottom: 26px;
      font-style: italic; position: relative; padding-left: 22px;
    }
    .testi-quote::before {
      content: '"';
      position: absolute; left: 0; top: -8px;
      font-family: 'Outfit', sans-serif; font-size: 52px;
      color: rgba(249,115,22,0.28); line-height: 1; font-style: normal;
    }
    .testi-author { display: flex; align-items: center; gap: 14px; }
    .author-av {
      width: 44px; height: 44px; border-radius: 50%;
      background: rgba(249,115,22,0.12);
      border: 2px solid rgba(249,115,22,0.3);
      display: flex; align-items: center; justify-content: center;
      font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 13px; color: var(--orange);
      flex-shrink: 0;
    }
    .author-name {
      font-family: 'Outfit', sans-serif; font-weight: 700;
      font-size: 14px; color: #fff;
    }
    .author-role { font-size: 12px; color: var(--dim); margin-top: 2px; }

    /* ===== STATS BAR ===== */
    #statsbar {
      background: var(--orange); padding: 52px 0;
    }
    .stats-row {
      display: flex; justify-content: center; align-items: center;
      gap: 72px; flex-wrap: wrap;
    }
    .sb-item { text-align: center; }
    .sb-num {
      font-family: 'Outfit', sans-serif; font-size: 46px;
      font-weight: 800; color: #fff; line-height: 1;
    }
    .sb-num span { color: rgba(8,17,31,0.7); }
    .sb-label { font-size: 13px; color: rgba(255,255,255,0.78); margin-top: 5px; }

    /* ===== AUDIT ===== */
    #audit { padding: 100px 0; background: var(--navy-mid); }
    .audit-wrap {
      display: grid; grid-template-columns: 1fr 1fr;
      gap: 70px; margin-top: 60px; align-items: start;
    }
    .audit-features { display: flex; flex-direction: column; gap: 28px; }
    .af-item { display: flex; gap: 18px; align-items: flex-start; }
    .af-icon {
      width: 44px; height: 44px; min-width: 44px;
      border-radius: 10px;
      background: rgba(249,115,22,0.1);
      border: 1px solid rgba(249,115,22,0.22);
      display: flex; align-items: center; justify-content: center;
      font-size: 18px;
    }
    .af-title {
      font-family: 'Outfit', sans-serif; font-weight: 700;
      font-size: 15.5px; color: #fff; margin-bottom: 5px;
    }
    .af-text { font-size: 13.5px; color: var(--muted); line-height: 1.65; }
    .audit-form-box {
      background: var(--navy-card);
      border: 1px solid var(--border-o);
      border-radius: 18px; padding: 42px; text-align: center;
    }
    .audit-form-box h3 {
      font-size: 24px; font-weight: 800; color: #fff; margin-bottom: 10px;
    }
    .audit-form-box p { font-size: 14px; color: var(--muted); margin-bottom: 30px; line-height: 1.65; }
    .aform { display: flex; flex-direction: column; gap: 13px; text-align: left; }
    .aform input {
      background: rgba(255,255,255,0.05);
      border: 1px solid var(--border);
      border-radius: 8px; padding: 13px 16px;
      font-size: 14px; color: #fff;
      font-family: 'DM Sans', sans-serif;
      outline: none; width: 100%;
      transition: border-color 0.2s;
    }
    .aform input:focus { border-color: var(--orange); }
    .aform input::placeholder { color: var(--dim); }

    /* ===== ABOUT ===== */
    #about { padding: 100px 0; background: var(--navy); }
    .about-grid {
      display: grid; grid-template-columns: 5fr 7fr;
      gap: 72px; margin-top: 56px; align-items: start;
    }
    .about-card {
      background: var(--navy-card);
      border: 1px solid var(--border-o);
      border-radius: 18px; padding: 42px;
      position: relative; overflow: hidden;
    }
    .about-card::after {
      content: '';
      position: absolute; bottom: -70px; right: -70px;
      width: 220px; height: 220px;
      background: radial-gradient(circle, rgba(249,115,22,0.08) 0%, transparent 70%);
    }
    .ac-tag {
      font-size: 11px; font-weight: 700; letter-spacing: 2px;
      text-transform: uppercase; color: var(--orange); margin-bottom: 10px;
    }
    .ac-name {
      font-family: 'Outfit', sans-serif; font-size: 22px;
      font-weight: 800; color: #fff; margin-bottom: 3px;
    }
    .ac-role { font-size: 13px; color: var(--dim); margin-bottom: 20px; }
    .ac-bio {
      font-size: 14px; color: var(--muted); line-height: 1.75; margin-bottom: 26px;
    }
    .tech-tags { display: flex; flex-wrap: wrap; gap: 8px; }
    .t-tag {
      background: rgba(249,115,22,0.1);
      border: 1px solid rgba(249,115,22,0.22);
      color: var(--orange); font-size: 12px; font-weight: 600;
      padding: 4px 13px; border-radius: 100px; letter-spacing: 0.3px;
    }
    .about-body p {
      font-size: 15.5px; color: var(--muted);
      line-height: 1.8; margin-bottom: 22px;
    }
    .value-list { list-style: none; display: flex; flex-direction: column; gap: 14px; margin-top: 28px; }
    .value-list li { display: flex; align-items: center; gap: 13px; font-size: 14.5px; color: var(--text); }
    .vdot { width: 9px; height: 9px; border-radius: 50%; background: var(--orange); min-width: 9px; }

    /* ===== BLOG ===== */
    #blog { padding: 100px 0; background: var(--navy-mid); }
    .blog-grid {
      display: grid; grid-template-columns: repeat(3, 1fr);
      gap: 24px; margin-top: 56px;
    }
    .blog-card {
      background: var(--navy-card);
      border: 1px solid var(--border);
      border-radius: 14px; overflow: hidden;
      transition: border-color 0.3s, transform 0.3s; cursor: pointer;
    }
    .blog-card:hover { border-color: var(--border-o); transform: translateY(-5px); }
    .blog-thumb {
      height: 160px;
      display: flex; align-items: center; justify-content: center;
      font-size: 44px; position: relative; overflow: hidden;
    }
    .blog-body { padding: 26px; }
    .blog-cat {
      font-size: 11px; font-weight: 700; letter-spacing: 1.8px;
      text-transform: uppercase; color: var(--orange); margin-bottom: 10px;
    }
    .blog-title {
      font-family: 'Outfit', sans-serif; font-weight: 700;
      font-size: 16px; color: #fff; margin-bottom: 10px; line-height: 1.35;
    }
    .blog-excerpt { font-size: 13.5px; color: var(--muted); line-height: 1.65; margin-bottom: 18px; }
    .blog-read {
      font-size: 12.5px; color: var(--orange); font-weight: 600;
      display: flex; align-items: center; gap: 5px;
    }

    /* ===== SMART QUOTE ===== */
    #smartquote { padding: 100px 0; background: var(--navy); }
    .quote-wrap {
      display: grid; grid-template-columns: 5fr 7fr;
      gap: 72px; margin-top: 56px; align-items: start;
    }
    .quote-info { display: flex; flex-direction: column; gap: 20px; }
    .qi-item { display: flex; gap: 16px; align-items: flex-start; }
    .qi-icon {
      width: 44px; height: 44px; min-width: 44px; border-radius: 10px;
      background: rgba(249,115,22,0.1); border: 1px solid rgba(249,115,22,0.22);
      display: flex; align-items: center; justify-content: center; font-size: 18px;
    }
    .qi-title {
      font-family: 'Outfit', sans-serif; font-weight: 700;
      font-size: 15px; color: #fff; margin-bottom: 4px;
    }
    .qi-text { font-size: 13.5px; color: var(--muted); line-height: 1.6; }
    .quote-form-box {
      background: var(--navy-card);
      border: 1px solid var(--border);
      border-radius: 18px; padding: 42px;
    }
    .quote-form-box h3 {
      font-size: 22px; font-weight: 800; color: #fff; margin-bottom: 6px;
    }
    .quote-form-box > p { font-size: 14px; color: var(--muted); margin-bottom: 28px; }
    .qform { display: flex; flex-direction: column; gap: 14px; }
    .qform-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .fgroup { display: flex; flex-direction: column; gap: 6px; }
    .fgroup label {
      font-size: 11.5px; font-weight: 700; letter-spacing: 0.7px;
      text-transform: uppercase; color: var(--muted);
    }
    .fgroup input, .fgroup select, .fgroup textarea {
      background: rgba(255,255,255,0.04);
      border: 1px solid var(--border);
      border-radius: 8px; padding: 12px 15px;
      font-size: 14px; color: #fff;
      font-family: 'DM Sans', sans-serif; outline: none; resize: none;
      transition: border-color 0.2s;
    }
    .fgroup input:focus, .fgroup select:focus, .fgroup textarea:focus {
      border-color: var(--orange);
    }
    .fgroup input::placeholder, .fgroup textarea::placeholder { color: var(--dim); }
    .fgroup select { color: var(--muted); }
    .fgroup select option { background: var(--navy-mid); color: var(--text); }

    /* ===== CONTACT ===== */
    #contact { padding: 100px 0; background: var(--navy-mid); }
    .contact-grid {
      display: grid; grid-template-columns: 5fr 7fr;
      gap: 72px; margin-top: 56px; align-items: start;
    }
    .contact-items { display: flex; flex-direction: column; gap: 28px; }
    .ci { display: flex; gap: 16px; align-items: flex-start; }
    .ci-icon {
      width: 48px; height: 48px; min-width: 48px; border-radius: 12px;
      background: rgba(249,115,22,0.1); border: 1px solid rgba(249,115,22,0.22);
      display: flex; align-items: center; justify-content: center; font-size: 20px;
    }
    .ci-label {
      font-size: 11px; font-weight: 700; letter-spacing: 1px;
      text-transform: uppercase; color: var(--dim); margin-bottom: 5px;
    }
    .ci-val {
      font-family: 'Outfit', sans-serif; font-weight: 700;
      font-size: 15.5px; color: #fff;
    }
    
    /* ===== FOOTER ===== */
    footer {
      background: #050D18; padding: 60px 0 32px;
      border-top: 1px solid var(--border);
    }
    .footer-inner {
      display: flex; justify-content: space-between;
      align-items: flex-start; gap: 48px; flex-wrap: wrap;
      margin-bottom: 48px;
    }
    .footer-brand .nav-logo { margin-bottom: 12px; }
    .footer-brand p { font-size: 13.5px; color: var(--dim); max-width: 280px; line-height: 1.65; }
    .footer-col h4 {
      font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 12px;
      text-transform: uppercase; letter-spacing: 1.2px; color: var(--muted); margin-bottom: 18px;
    }
    .footer-col ul { list-style: none; display: flex; flex-direction: column; gap: 10px; }
    .footer-col a { font-size: 13.5px; color: var(--dim); transition: color 0.2s; }
    .footer-col a:hover { color: var(--orange); }
    .footer-bottom {
      border-top: 1px solid var(--border); padding-top: 26px;
      display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;
    }
    .footer-bottom p { font-size: 12.5px; color: var(--dim); }
    .footer-badge {
      background: rgba(249,115,22,0.1); border: 1px solid rgba(249,115,22,0.22);
      color: var(--orange); font-size: 11.5px; font-weight: 700;
      padding: 5px 14px; border-radius: 100px; letter-spacing: 0.4px;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1024px) {
      .problems-grid, .packages-grid, .testi-grid, .blog-grid { grid-template-columns: repeat(2, 1fr); }
      .steps-grid { grid-template-columns: repeat(2, 1fr); }
      .steps-grid::after { display: none; }
      .audit-wrap, .about-grid, .quote-wrap, .contact-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 700px) {
      .nav-links { display: none; }
      .problems-grid, .packages-grid, .testi-grid, .blog-grid, .steps-grid { grid-template-columns: 1fr; }
      .hero-stats { gap: 28px; }
      .stats-row { gap: 40px; }
      .qform-row, .cf-row { grid-template-columns: 1fr; }
      .footer-inner { flex-direction: column; }
      .footer-bottom { flex-direction: column; text-align: center; }
    }
    </style>
</head>
<body>
    @yield('content')
    
    @fluxScripts
</body>
</html>
